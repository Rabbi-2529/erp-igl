<?php

namespace App\Http\Controllers;

use App\Models\SalaryRecord;
use App\Models\Admin_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryRecordController extends Controller
{
    // Display a listing of the salary records
 public function salary_advanced_create()
{
    $user = Auth::user();

    // Fetch salary records filtered by company and branch
    $records = SalaryRecord::where('com_id', $user->au_company_id)
        ->where('branch_id', $user->au_branch_id)
        ->get();

    // Fetch employees filtered by company and branch
    $employees = Admin_user::where('au_company_id', $user->au_company_id)
        ->where('au_branch_id', $user->au_branch_id)
        ->get();

    return view('page.salary_records.salary_advanced_create', compact('records', 'employees'));
}


    // Show the form for creating a new salary record
  public function create()
{
    // Fetch all employees associated with the same company and branch as the authenticated user
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $employees = Admin_user::where('au_company_id', $companyId)
        ->where('au_branch_id', $branchId)
        ->get();

    return view('page.salary_records.create', compact('employees'));
}


    // Store a newly created salary record in storage
   public function salary_advanced_store(Request $request)
{
    $user = Auth::user(); // Get the authenticated user
    $company_id = $user->au_company_id; // Fix: use au_company_id
    $branch_id = $user->au_branch_id; // Get the branch ID

    // Validate input data
    $validated = $request->validate([
        'employee_id' => 'required|exists:admin_user,au_id',
        'salary_month' => 'required|date_format:Y-m-d', // Ensure date format is correct
        'amount' => 'required|numeric',
    ]);

    // Add company and branch ID to the validated data
    $validated['com_id'] = $company_id;
    $validated['branch_id'] = $branch_id;

    // Create the record
    SalaryRecord::create($validated);

    // Redirect with success message
    return redirect()->route('salary-records.salary_advanced_create')->with('success', 'Salary record created successfully.');
}

    // Display the specified salary record
  public function salary_advanced_show($id)
{
    $record = SalaryRecord::where('id', $id)
                ->where('paid', 0)
                ->firstOrFail();

    $employee = Admin_user::find($record->employee_id);

    return view('page.salary_records.salary_advanced_show', compact('record', 'employee'));
}

    // Show the form for editing the specified salary record
public function salary_advanced_edit($id)
{
    // Find the salary record by ID
    $record = SalaryRecord::findOrFail($id);

    // Get the company and branch ID from the authenticated user
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    // Fetch all employees associated with the company
    $employees = Admin_user::where('au_company_id', $companyId)->get();

    // Return the edit view with the record, employees, company and branch information
    return view('page.salary_records.salary_advanced_edit', compact('record', 'employees', 'branchId'));
}

    // Update the specified salary record in storage
    public function salary_advanced_update(Request $request, $id)
{
    $user = Auth::user(); // Get the authenticated user
    $company_id = $user->au_company_id; // Get the company ID from the authenticated user
    $branch_id = $user->au_branch_id; // Get the branch ID from the authenticated user

    $validated = $request->validate([
        'employee_id' => 'required|exists:admin_user,au_id',
        'salary_month' => 'required|date_format:Y-m-d', // Ensure date format is correct
        'amount' => 'required|numeric',
    ]);

    $record = SalaryRecord::findOrFail($id);

    // Ensure the company_id and branch_id are set correctly
    $validated['com_id'] = $company_id;
    $validated['branch_id'] = $branch_id; 

    // Update the record with validated data including company_id and branch_id
    $record->update($validated);

    return redirect()->route('salary-records.salary_advanced_create')->with('success', 'Salary record updated successfully.');
}


    // Remove the specified salary record from storage
    public function destroy($id)
    {
        $record = SalaryRecord::findOrFail($id);
        $record->delete();
        return redirect()->route('salary-records.salary_advanced_create')->with('success', 'Record deleted successfully');
    }
}
