<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFine;
use App\Models\Admin_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Ensure this line is present
use Illuminate\Support\Facades\Auth;
use App\Models\Fine;
use Carbon\Carbon;

class EmployeeFineController extends Controller
{
    /**
     * Display a listing of the employee fines.
     *
     * @return \Illuminate\Http\Response
     */
public function index()
{
    $user = Auth::user();
    $com_id = $user->au_company_id;
    $branch_id = $user->au_branch_id;

    // Get employee fines for the same company and branch
    $employeeFines = EmployeeFine::with(['user', 'fine'])
        ->where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->get();

    // Get users of the same company and branch
    $users = Admin_User::where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
        ->get(['au_user_id', 'au_name']);

    // Get all fine types (not branch-specific)
    $fines = Fine::all(['id', 'title']);

    // Return the view with data
    return view('inventory.accounts.employee_fines.index', compact('employeeFines', 'users', 'fines'));
}



    /**
     * Show the form for creating a new employee fine.
     *
     * @return \Illuminate\Http\Response
     */
public function create()
{
    // Get the authenticated user's company and branch ID
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    // Get all users from the same company and branch
    $users = Admin_User::where('au_company_id', $companyId)
                       ->where('au_branch_id', $branchId)
                       ->get(['au_user_id', 'au_name']);

    // Get all fines
    $fines = Fine::all(['id', 'title']);

    // Return view with filtered users and fines
    return view('inventory.accounts.employee_fines.create', compact('users', 'fines'));
}

    /**
     * Store a newly created employee fine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
{
    // Validate the request data
    $request->validate([
        'au_user_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'amount' => 'required|numeric',
        'date' => 'required|date_format:d/m/Y',
    ]);

    $authUser = Auth::user();
    $companyId = $authUser->au_company_id;
    $branchId = $authUser->au_branch_id;

    // Check if the selected user is from the same branch
    $targetUser = Admin_User::where('au_user_id', $request->au_user_id)
                            ->where('au_company_id', $companyId)
                            ->where('au_branch_id', $branchId)
                            ->first();

    if (!$targetUser) {
        return redirect()->back()->with('error', 'You are not allowed to assign fines to this user.');
    }

    // Create the employee fine
    $employeeFine = new EmployeeFine([
        'au_user_id' => $request->input('au_user_id'),
        'au_company_id' => $companyId,
        'title' => $request->input('title'),
        'amount' => $request->input('amount'),
        'date' => Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
    ]);
    $employeeFine->save();

    return redirect()->route('employee_fines.index')->with('success', 'Employee fine created successfully.');
}


    /**
     * Show the form for editing the specified employee fine.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function edit($id)
{
    $employeeFine = EmployeeFine::findOrFail($id);

    $authUser = Auth::user();
    $companyId = $authUser->au_company_id;
    $branchId = $authUser->au_branch_id;

    // Limit users to same branch
    $users = Admin_User::where('au_company_id', $companyId)
                       ->where('au_branch_id', $branchId)
                       ->get(['au_user_id', 'au_name']);

    $fines = Fine::all(['id', 'title']);

    return view('inventory.accounts.employee_fines.edit', compact('employeeFine', 'users', 'fines'));
}


    /**
     * Update the specified employee fine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
  public function update(Request $request, $id)
{
    $request->validate([
        'au_user_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'amount' => 'required|numeric',
        'date' => 'required|date',
    ]);

    $authUser = Auth::user();
    $companyId = $authUser->au_company_id;
    $branchId = $authUser->au_branch_id;

    // Check that the target user belongs to the same branch
    $targetUser = Admin_User::where('au_user_id', $request->au_user_id)
                            ->where('au_company_id', $companyId)
                            ->where('au_branch_id', $branchId)
                            ->first();

    if (!$targetUser) {
        return redirect()->back()->with('error', 'You are not authorized to update fines for this user.');
    }

    $employeeFine = EmployeeFine::findOrFail($id);
    $employeeFine->au_user_id = $request->au_user_id;
    $employeeFine->title = $request->title;
    $employeeFine->amount = $request->amount;
    $employeeFine->date = $request->date;
    $employeeFine->save();

    return redirect()->back()->with('success', 'Fine updated successfully.');
}


    /**
     * Remove the specified employee fine from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $employeeFine = EmployeeFine::findOrFail($id);
            $employeeFine->delete();
            return response()->json(['message' => 'Employee fine deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error deleting employee fine.'], 500);
        }
    }
}
