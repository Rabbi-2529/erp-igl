<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Admin_user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchRegistrationController extends Controller
{
    // List branches for current user's company
    public function index()
    {
        $companyId = Auth::user()->au_company_id;

        $branches = Branch::where('company_id', $companyId)->get();

        return view('page.branch.index', compact('branches'));
    }

    // Show create branch form
    public function create()
    {
        return view('page.branch.create');
    }

    // Store new branch with auto-increment branch_id per company
    public function store(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',

            'branch_address' => 'nullable|string|max:255',
            'branch_city' => 'nullable|string|max:100',
            'branch_state' => 'nullable|string|max:100',
            'branch_zip' => 'nullable|string|max:20',
            'branch_hotline' => 'nullable|string|max:50',
            'branch_email' => 'nullable|email|max:100',
             'status' => 'nullable|in:0,1',

        ]);

        DB::beginTransaction();

        try {
            $companyId = Auth::user()->au_company_id;

            // Get last branch_id for this company
            $lastBranch = Branch::where('company_id', $companyId)
                ->orderByDesc('branch_id')
                ->first();

            $nextBranchId = $lastBranch ? ((int)$lastBranch->branch_id + 1) : 1001;

            $branch = new Branch();
            $branch->branch_id = $nextBranchId;
            $branch->branch_name = $request->branch_name;
       
            $branch->branch_address = $request->branch_address;
            $branch->branch_city = $request->branch_city;
            $branch->branch_state = $request->branch_state;
            $branch->branch_zip = $request->branch_zip;
            $branch->branch_hotline = $request->branch_hotline;
            $branch->branch_email = $request->branch_email;
            $branch->company_id = $companyId;
            $branch->status = $request->status ?? 'active';

            $branch->save();

            DB::commit();

            return redirect()->route('branch.index')->with('success', 'Branch created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error creating branch: ' . $e->getMessage()]);
        }
    }

    // Show edit form for a branch
    public function edit($id)
    {
        $companyId = Auth::user()->au_company_id;

        $branch = Branch::where('company_id', $companyId)
            ->where('branch_id', $id)
            ->first();

        if (!$branch) {
            return redirect()->route('branch.index')->with('error', 'Branch not found.');
        }

        return view('page.branch.edit', compact('branch'));
    }

    // Update branch data
    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
      
            'branch_address' => 'nullable|string|max:255',
            'branch_city' => 'nullable|string|max:100',
            'branch_state' => 'nullable|string|max:100',
            'branch_zip' => 'nullable|string|max:20',
            'branch_hotline' => 'nullable|string|max:50',
            'branch_email' => 'nullable|email|max:100',
       'status' => 'nullable|in:0,1',
        ]);

        DB::beginTransaction();

        try {
            $companyId = Auth::user()->au_company_id;

            $branch = Branch::where('company_id', $companyId)
                ->where('branch_id', $id)
                ->first();

            if (!$branch) {
                return redirect()->route('branch.index')->with('error', 'Branch not found.');
            }

            $branch->branch_name = $request->branch_name;
 
            $branch->branch_address = $request->branch_address;
            $branch->branch_city = $request->branch_city;
            $branch->branch_state = $request->branch_state;
            $branch->branch_zip = $request->branch_zip;
            $branch->branch_hotline = $request->branch_hotline;
            $branch->branch_email = $request->branch_email;
            $branch->status = $request->status ?? $branch->status;

            $branch->save();

            DB::commit();

            return redirect()->route('branch.index')->with('success', 'Branch updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error updating branch: ' . $e->getMessage()]);
        }
    }

    // Show form to assign branch to a user
    public function assignForm()
    {
        $companyId = Auth::user()->au_company_id;

        $users = Admin_user::where('au_company_id', $companyId)
            ->where('au_user_type', '!=', 4)  // exclude branch users
            ->get();

        $branches = Branch::where('company_id', $companyId)->get();

        return view('page.branch.assign', compact('users', 'branches'));
    }

    // Assign branch to user
   public function assign(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:admin_user,au_id',
        'branch_id' => 'required|exists:auth_branch,branch_id', // fix table name here
    ]);

    $user = Admin_user::find($request->user_id);
    $branch = Branch::where('branch_id', $request->branch_id)->first();

    if (!$user || !$branch) {
        return back()->withErrors(['error' => 'Invalid user or branch selected.']);
    }

    if ($branch->status != 1) {
        return back()->withErrors(['error' => 'The selected branch is not active yet.']);
    }

    $user->au_branch_id = $branch->branch_id;
    $user->au_branch_name = $branch->branch_name;
    $user->au_user_type = 4; // branch user
    $user->au_permission_status = '1-2-3-4-6-7-8-46'; // example permissions
    $user->save();

   return redirect()->route('branch.assignForm')->with('success', 'Branch assigned to user successfully.');

}
public function assignedUsers()
{
    $companyId = Auth::user()->au_company_id;

    $assignedUsers = Admin_user::where('au_company_id', $companyId)
        ->where('au_user_type', 4)
        ->with('branch')
        ->get();

    // Auto-sync user status with their branch status
    foreach ($assignedUsers as $user) {
        if ($user->branch) {
            $desiredStatus = $user->branch->status;
            if ($user->au_status !== $desiredStatus) {
                $user->au_status = $desiredStatus;
                $user->save();
            }
        }
    }

    return view('page.branch.assigned_users', compact('assignedUsers'));
}


public function toggleUserStatus(Admin_user $user)
{
    // Check if user has a branch and its status
    if ($user->branch) {
        if ($user->branch->status == 0 && $user->au_status == 0) {
            // Branch inactive and user already inactive, nothing to do
            return redirect()->back()->with('error', 'User and branch are both inactive.');
        }

        if ($user->branch->status == 0 && $user->au_status == 1) {
            // Trying to activate user but branch is inactive
            return redirect()->back()->with('error', 'Cannot activate user. Branch is inactive.');
        }
    }

    // If branch active or no branch, toggle status
    $user->au_status = $user->au_status == 1 ? 0 : 1;
    $user->save();

    return redirect()->back()->with('success', 'User status updated successfully.');
}

public function toggleBranchStatus($branchId)
{
    $branch = Branch::findOrFail($branchId);

    // Toggle branch status
    $branch->status = !$branch->status;
    $branch->save();

    // Update all users assigned to this branch
    Admin_user::where('au_branch_id', $branchId)
        ->update(['au_status' => $branch->status]);

    return redirect()->back()->with('success', 'Branch and related users status updated successfully.');
}


}
