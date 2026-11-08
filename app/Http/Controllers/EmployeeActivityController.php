<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeActivity;
use Carbon\Carbon;

class EmployeeActivityController extends Controller
{
public function index()
{
    $com_id = Auth::user()->au_company_id;
    $user = Auth::user();
    $branch_id = Auth::user()->au_branch_id; // Get the branch ID of the logged-in user

    // If the user is an HR Admin (au_user_type = 4), show all activities in the company, filtered by branch
    if ($user->au_user_type == 4) {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id) // Filter by branch ID
                                      ->oldest()
                                      ->get();
    }
    // If the user is neither HR Admin nor Team Leader, show only their own activities, filtered by branch
    else {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('user_id', $user->au_id)
                                      ->where('branch_id', $branch_id) // Filter by branch ID
                                      ->oldest()
                                      ->get();
    }

    return view('page.hrm.employee_activity.index', compact('activities'));
}

public function daily_activity()
{
    $user = Auth::user();
    $com_id = $user->au_company_id;
    $branch_id = $user->au_branch_id;

    // If the user is an HR Admin (au_user_type == 4)
    if ($user->au_user_type == 4) {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->whereDate('activity_date', today())
                                      ->oldest()
                                      ->get();
    }
    // If the user is a Team Leader (au_user_type == 5), show team activities in their branch
    elseif ($user->au_user_type == 5) {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->whereDate('activity_date', today())
                                      ->whereHas('user', function ($query) use ($user) {
                                          $query->where('au_team_id', $user->au_team_id);
                                      })
                                      ->oldest()
                                      ->get();
    }
    // For all other users, only their own activities in their branch
    else {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->where('user_id', $user->au_id)
                                      ->whereDate('activity_date', today())
                                      ->oldest()
                                      ->get();
    }

    return view('page.hrm.employee_activity.today', compact('activities'));
}

 public function total_activity()
{
    $user = Auth::user();
    $com_id = $user->au_company_id;
    $branch_id = $user->au_branch_id;

    // HR Admin (User Type = 4): See all activities in the company & branch
    if ($user->au_user_type == 4) {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->latest()
                                      ->get();
    }
    // Team Leader (User Type = 5): See team members' activities in their branch
    elseif ($user->au_user_type == 5) {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->whereHas('user', function ($query) use ($user) {
                                          $query->where('au_team_id', $user->au_team_id);
                                      })
                                      ->oldest()
                                      ->get();
    }
    // Other Users: See only their own activities in their branch
    else {
        $activities = EmployeeActivity::where('company_id', $com_id)
                                      ->where('branch_id', $branch_id)
                                      ->where('user_id', $user->au_id)
                                      ->oldest()
                                      ->get();
    }

    return view('page.hrm.employee_activity.total_activity', compact('activities'));
}

public function create()
{
    return view('page.hrm.employee_activity.create');
}

public function store(Request $request)
{
    // Validate the form input
    $request->validate([
        'w_title' => 'required|string|max:255',
        'w_d' => 'nullable|string',
        'activity_date' => 'required|date',
        'sell_amount' => 'nullable|numeric|min:0',
    ]);

    $user = Auth::user();

    // Check if the user has already submitted an activity for this date
    $exists = EmployeeActivity::where('user_id', $user->au_id)
        ->whereDate('activity_date', $request->activity_date)
        ->exists();

    if ($exists) {
        return redirect()->back()->with('error', 'You have already submitted an activity for this date.');
    }

    // Process the content to convert numbers to links
    $processedContent = $this->convertNumbersToLinks($request->w_d);

    // Save the new activity
    $activity = new EmployeeActivity();
    $activity->company_id = $user->au_company_id;
    $activity->branch_id = $user->au_branch_id; // ✅ Add this line
    $activity->user_id = $user->au_id;
    $activity->w_title = $request->w_title;
    $activity->w_d = $processedContent;
    $activity->activity_date = $request->activity_date;
    $activity->sell_amount = $request->sell_amount;
    $activity->status = 0;
    $activity->save();

    return redirect()->route('employee-activity.index')->with('success', 'Activity created successfully.');
}
public function show($id)
{
    $activity = EmployeeActivity::findOrFail($id);
    return view('page.hrm.employee_activity.show', compact('activity'));
}

public function edit($id)
{
    $activity = EmployeeActivity::findOrFail($id);
    return view('page.hrm.employee_activity.edit', compact('activity'));
}

public function update(Request $request, $id)
{
    // Validate the form input including the new sell_amount and activity_date fields
    $request->validate([
        'w_title' => 'required|string|max:255',
        'w_d' => 'nullable|string',
        'activity_date' => 'required|date',
        'sell_amount' => 'nullable|numeric|min:0',
    ]);

    $user = Auth::user();

    // Process the content to convert numbers to links
    $processedContent = $this->convertNumbersToLinks($request->w_d);

    // Update the activity with the sell_amount and activity_date
    $activity = EmployeeActivity::findOrFail($id);
    $activity->company_id = $user->au_company_id;
    $activity->branch_id = $user->au_branch_id; // ✅ Set branch ID
    $activity->w_title = $request->w_title;
    $activity->w_d = $processedContent;
    $activity->activity_date = $request->activity_date;
    $activity->sell_amount = $request->sell_amount;
    $activity->status = 0;
    $activity->save();

    return redirect()->back()->with('success', 'Activity updated successfully.');
}


/**
 * Convert standalone numbers in text to client feedback links
 */
private function convertNumbersToLinks($content)
{
    if (empty($content)) {
        return $content;
    }

    // Pattern to match standalone numbers (not part of other words)
    $pattern = '/(?<!\w)(\d+)(?!\w)/';

    return preg_replace_callback($pattern, function($matches) {
        $id = $matches[1];
        $url = route('pages.client_feedback', ['id' => $id]);
        return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.$id.'</a>';
    }, $content);
}

    public function destroy($id)
    {
        $activity = EmployeeActivity::findOrFail($id);
        $activity->delete();

        return redirect()->back()->with('success', 'Successfully  Deleted.');
    }

public function accept($id)
{
    $activity = EmployeeActivity::findOrFail($id);
    $activity->status = 1; // or whatever your accepted status is
    $activity->save();

    return redirect()->back()->with('success', 'Activity accepted.');
}

public function reject($id)
{
    $activity = EmployeeActivity::findOrFail($id);
    $activity->status = 2; // or whatever your rejected status is
    $activity->save();

    return redirect()->back()->with('success', 'Activity rejected.');
}

}
