<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use DateTime;
use App\Models\Admin_user;
use App\Models\OfficeLunch;

class OfficeLunchController extends Controller
{
   public function officeLunchToday()
{
    $com = auth()->user()->au_company_id;
    $branchId = auth()->user()->au_branch_id;  // Assuming the branch ID is available on the user model

    $comDetails = Admin_user::where('au_company_id', $com)->first();

    $shiftTime = explode(',', $comDetails->au_work_shift);
    $shiftStart = Carbon::parse($shiftTime[0]);
    $lunchCancel = $shiftStart->addMinutes(61);

    $currentTime = Carbon::now();

    $cancelTimeMatch = $currentTime->gte($lunchCancel);

    // Filtering OfficeLunch by company_id and branch_id
    $todayLunchUsers = OfficeLunch::where('company_id', $com)
        ->where('branch_id', $branchId)  // Adding the branch_id filter
        ->whereDate('lunch_in', now()->toDateString())
        ->get();

    $lunchCount = $todayLunchUsers->sum('total_meal');

    $userIds = $todayLunchUsers->pluck('user_id');

    // Filtering Admin_User by company_id, branch_id, and excluding the users who already have lunch
    $userDetails = Admin_user::whereIn('au_id', $userIds)
                    ->where('au_company_id', $com)
                    ->where('au_branch_id', $branchId)  // Adding the branch_id filter
                    ->orderBy('au_id')
                    ->get();

    $users = Admin_User::where('au_company_id', $com)
                ->where('au_branch_id', $branchId)  // Adding the branch_id filter
                ->whereNotIn('au_id', $userIds)
                ->get();

    return view('page.oms.lunch.today-lunch', compact('todayLunchUsers', 'users', 'userDetails', 'lunchCount', 'cancelTimeMatch'));
}



public function officeLunchUser()
{
    $com = auth()->user()->au_company_id;
    $branchId = auth()->user()->au_branch_id;  // Assuming the branch ID is available on the user model

    $comUsers = Admin_user::where('au_company_id', $com)
        ->where('au_branch_id', $branchId)  // Adding the branch_id filter
        ->where('au_permission_status', 94)
        ->get();

    return view('page.oms.lunch.lunch-user', compact('comUsers'));
}


public function lunchMark(Request $request) {

    $empId = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;  // Assuming branch_id is stored in the user model

    // Check if the user has already marked lunch for today
    $todayLunch = OfficeLunch::where('user_id', $empId)
        ->where('company_id', $com)
        ->where('branch_id', $branchId)  // Make sure lunch record matches the branch_id
        ->whereDate('lunch_in', Carbon::today())
        ->first();

    if ($todayLunch) {
        return redirect()->back()->with(['msg' => 'You have already marked lunch for today.', 'type' => 'info']);
    }

    // Get current time for lunch_in
    $lunchIn = Carbon::now()->format('Y-m-d H:i:s');
    
    // Get company and user details
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $au_user = Admin_user::where('au_id', $empId)->first();

    // Default notes
    $defaultNotes = '';

    // Message for success
    $message = "You are entered for office lunch today.";
    $type = "success";

    // Create new OfficeLunch record
    $lunch = new OfficeLunch();

    $lunch->company_id = $com;
    $lunch->user_id = $empId;
    $lunch->branch_id = $branchId;  // Store the branch ID in the lunch record
    $lunch->total_meal = "1";  // Assuming 1 meal is default
    $lunch->lunch_in = $lunchIn;
    $lunch->user_ip = $request->ip();
    $lunch->notes = $request->input('notes', $defaultNotes);
    
    // Save the lunch record
    $lunch->save();

    return redirect()->back()->with(['msg' => $message, 'type' => $type]);
}


  public function lunchMarkAdmin(Request $request) {

    $empId = $request->input('user_id');
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;  // Get the branch_id of the admin who is marking the lunch

    // Check if the employee has already marked lunch for today
    $todayLunch = OfficeLunch::where('user_id', $empId)
        ->where('company_id', $com)
        ->where('branch_id', $branchId)  // Filter by the branch_id of the employee
        ->whereDate('lunch_in', Carbon::today())
        ->first();

    if ($todayLunch) {
        return redirect()->back()->with(['msg' => 'User already marked lunch for today.', 'type' => 'info']);
    }

    // Get current time for lunch_in
    $lunchIn = Carbon::now()->format('Y-m-d H:i:s');
    
    // Get company and user details
    $comDetails = Admin_user::where('au_company_id', $com)->first();
    $au_user = Admin_user::where('au_id', $empId)->first();

    // Default notes
    $defaultNotes = 'Created By Authorized Person';

    // Message for success
    $message = "Employee is entered for office lunch today.";
    $type = "success";

    // Create new OfficeLunch record
    $lunch = new OfficeLunch();

    $lunch->company_id = $com;
    $lunch->user_id = $empId;
    $lunch->branch_id = $branchId;  // Store the branch ID for this lunch record
    $lunch->total_meal = "1";  // Assuming 1 meal is default
    $lunch->lunch_in = $lunchIn;
    $lunch->user_ip = $request->ip();
    $lunch->notes = $defaultNotes;

    // Save the lunch record
    $lunch->save();

    return redirect()->back()->with(['msg' => $message, 'type' => $type]);
}



    public function lunchCancel(Request $request) {
        $empId = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
            $branchId = Auth::user()->au_branch_id;  // Get the branch_id of the admin who is canceling the lunch


        $todayLunch = OfficeLunch::where('user_id', $empId)
            ->where('company_id', $com)
            ->where('branch_id', $branchId)  // Include branch_id in the query
            ->whereDate('lunch_in', Carbon::today())
            ->first();

        $lunchCancel = Carbon::now()->format('Y-m-d H:i:s');
        $comDetails = Admin_user::where('au_company_id', $com)->first();

        $au_user = Admin_user::where('au_id', $empId)->first();

        if (!$todayLunch) {
            return redirect()->back()->with(['msg' => 'No lunch data found for today.', 'type' => 'error']);
        }

        $message = "You are cancel office lunch today.";
        $type = "success";

        $todayLunch->lunch_cancel = Carbon::now()->format('Y-m-d H:i:s');
        $todayLunch->total_meal = "0";
        $todayLunch->cancel_reason = $request->input('cancel_reason');

        $todayLunch->save();

        $lunchCancel = Carbon::now()->format('Y-m-d H:i:s');
        $comDetails = Admin_user::where('au_company_id', $com)->first();


        return redirect()->back()->with(['msg' => $message, 'type' => $type]);

    }

   public function lunchCancelAdmin(Request $request)
{
    $id = $request->input('user_id');
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;  // Get the branch_id of the logged-in admin

    // Find the lunch record by user_id and ensure it matches the company and branch
    $todayLunch = OfficeLunch::find($id);

    if (!$todayLunch) {
        return redirect()->back()->with(['msg' => 'No lunch data found for the selected user.', 'type' => 'error']);
    }

    if ($todayLunch->company_id != $com || $todayLunch->branch_id != $branchId) {
        return redirect()->back()->with(['msg' => 'Invalid company or branch for the selected user.', 'type' => 'error']);
    }

    $todayLunch->lunch_cancel = now();
    $todayLunch->total_meal = "0";
    $todayLunch->cancel_reason = $request->input('cancel_reason');
    $todayLunch->save();

    $message = "You have canceled office lunch for the selected user.";
    $type = "success";

    return redirect()->back()->with(['msg' => $message, 'type' => $type]);
}



    

    public function manualLunchEntry(Request $request)
    {
        $empId = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;  // Get the branch_id of the logged-in user

        $lunchIn = Carbon::now()->format('Y-m-d H:i:s');

        $au_user = Admin_user::where('au_id', $empId)->first();

        $message = "You are entered guest for office lunch today.";
        $type = "success";

        $lunch = new OfficeLunch();
        $lunch->company_id = $com;
        $lunch->branch_id = $branchId;  // Add the branch_id here
        $lunch->user_id = $empId;
        $lunch->lunch_in = $lunchIn;
        $lunch->total_meal = $request->input('total_meal');
        $lunch->notes = $request->input('notes');
        $lunch->status = '2';  // Assuming '2' is for guest status
        $lunch->save();

        return redirect()->back()->with(['msg' => 'Guest added successfully!', 'type' => 'success']);
    }

}
