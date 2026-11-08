<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Visit;
use App\Models\Client_feedback;
use App\Models\Sds_query_book;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Admin_user;
use App\Models\SalesVisit;
use App\Models\Sds_reason;


class SalesVisitController extends Controller
{
public function salesVisit()
{
    $user = Auth::user();
    $comId = $user->au_company_id;
    $branchId = $user->au_branch_id; // Get the branch ID of the authenticated user
    $currentDate = Carbon::now();
    $reasonList = Sds_reason::all();

    $dateFilter = $currentDate->subDays(14)->toDateString();
    
    // Filter users by both company_id and branch_id
    $users = Admin_user::where('au_company_id', $comId)
        ->where('au_branch_id', $branchId) // Add branch filter
        ->get();

    // Filter assigned visits by both company_id and branch_id
    $assignedVisits = SalesVisit::where('company_id', $comId)
        ->where('branch_id', $branchId) // Add branch filter
        ->where('created_at', '>=', $dateFilter)
        ->orderBy('created_at', 'desc')
        ->get();

    $alrAsgnd = $assignedVisits->pluck('cf_id')->toArray();

    // Filter client feedback by both company_id and branch_id
    $clientFeedPending = Client_feedback::where('cf_company_id', $comId)
        ->where('cf_branch_id', $branchId) // Add branch filter
        ->whereDate('cf_date', '>=', $dateFilter)
        ->where('cf_client_feedback', 4)
        ->whereNotIn('cf_id', $alrAsgnd)
        ->get();

    $clientFeedAssigned = Client_feedback::where('cf_company_id', $comId)
        ->where('cf_branch_id', $branchId) // Add branch filter
        ->whereDate('cf_date', '>=', $dateFilter)
        ->where('cf_client_feedback', 4)
        ->whereIn('cf_id', $alrAsgnd)
        ->get();

    $cfQbIdsPend = $clientFeedPending->pluck('cf_qb_id')->toArray();

    $cfQbIdsAsgnd = $clientFeedAssigned->pluck('cf_qb_id')->toArray();

    // Filter client lists by both company_id and qb_ids
$clientListPend = Sds_query_book::where('qb_company_id', $comId)
    ->where('qb_branch_id', $branchId) // Add branch filter
    ->whereIn('qb_id', $cfQbIdsPend)
    ->get();

$clientListAsgn = Sds_query_book::where('qb_company_id', $comId)
    ->where('qb_branch_id', $branchId) // Add branch filter
    ->whereIn('qb_id', $cfQbIdsAsgnd)
    ->get();

    return view('page.crm.sales-visit', compact('users', 'reasonList', 'clientFeedPending', 'clientFeedAssigned', 'clientListPend', 'clientListAsgn', 'assignedVisits'));
}


   public function store(Request $request)
{
    $validateData = Validator::make($request->all(), [
        'cf_id.*' => 'required',
        'emp_id.*' => 'required',
        'client_id.*' => 'required',
        'client_phone.*' => 'required',
        'entry_by.*' => 'required',
        'visit_date.*' => 'required',
    ]);

    if ($validateData->fails()) {
        return redirect()->back()->withErrors($validateData)->withInput();
    }

    $comId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id; // Get branch_id from the authenticated user

    $feedbackIds = $request->input('cf_id');
    $assignedUsers = $request->input('emp_id');
    $clientIds = $request->input('client_id');
    $clientPhones = $request->input('client_phone');
    $entryBys = $request->input('entry_by');
    $visitDates = $request->input('visit_date');
    $reasons = $request->has('reason') ? $request->input('reason') : [];

    $scheduledEmployees = [];

    foreach ($feedbackIds as $key => $feedbackId) {
        if (isset($scheduledEmployees[$visitDates[$key]]) && in_array($assignedUsers[$key], $scheduledEmployees[$visitDates[$key]])) {
            continue;
        }

        $scheduledEmployees[$visitDates[$key]][] = $assignedUsers[$key];

        $salesVisit = new SalesVisit();
        $salesVisit->company_id = $comId;
        $salesVisit->branch_id = $branchId; // Store the branch_id
        $salesVisit->cf_id = $feedbackId;
        $salesVisit->emp_id = $assignedUsers[$key];
        $salesVisit->client_id = $clientIds[$key];
        $salesVisit->client_phone = $clientPhones[$key];
        $salesVisit->entry_by = $entryBys[$key];
        $salesVisit->visit_date = $visitDates[$key];
        $salesVisit->reason = isset($reasons[$key]) ? $reasons[$key] : null;
        $salesVisit->save();
    }

    return redirect()->back()->with('success', 'Sales visits have been successfully stored.');
}


    public function destroy($id)
    {
        $salesVisit = SalesVisit::find($id);

        if ($salesVisit) {
            $salesVisit->delete();

            return redirect()->back()->with('success', 'Sales visit has been successfully deleted.');
        } else {
            return redirect()->back()->with('error', 'Sales visit not found.');
        }
    }


 public function monthReport()
{
    $comId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id; // Get branch_id from the authenticated user
    $reasonList = Sds_reason::all();
    
    $users = Admin_user::where('au_company_id', $comId)
        ->where('au_branch_id', $branchId)  // Filter by branch_id for users
        ->get();

    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $visitMonth = SalesVisit::where('company_id', $comId)
        ->where('branch_id', $branchId)  // Filter by branch_id for SalesVisit
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->orderBy('created_at', 'desc')
        ->get();

    $cfQbIds = $visitMonth->pluck('client_id')->toArray();

    $clientList = Sds_query_book::where('qb_company_id', $comId)
        ->where('qb_branch_id', $branchId)  // Filter by branch_id for client list
        ->whereIn('qb_id', $cfQbIds)
        ->get();
        
    return view('page.crm.sales-visit-report', compact('users', 'reasonList', 'visitMonth', 'clientList'));
}


   public function mySalesVisit()
{
    $comId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id; // Get branch_id from the authenticated user
    $reasonList = Sds_reason::all();
    
    $users = Admin_user::where('au_company_id', $comId)
        ->where('au_branch_id', $branchId)  // Filter by branch_id for users
        ->get();

    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $visitMonth = SalesVisit::where('company_id', $comId)
        ->where('branch_id', $branchId)  // Filter by branch_id for SalesVisit
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->orderBy('created_at', 'desc')
        ->get();

    $cfQbIds = $visitMonth->pluck('client_id')->toArray();

    $clientList = Sds_query_book::where('qb_company_id', $comId)
        ->where('qb_branch_id', $branchId)  // Filter by branch_id for client list
        ->whereIn('qb_id', $cfQbIds)
        ->get();
        
    return view('page.crm.my-visit', compact('users', 'reasonList', 'visitMonth', 'clientList'));
}


  public function mySalesVisitReport()
{
    $comId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id; // Get branch_id from the authenticated user
    $reasonList = Sds_reason::all();
    
    $users = Admin_user::where('au_company_id', $comId)
        ->where('au_branch_id', $branchId)  // Filter by branch_id for users
        ->get();

    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $visitMonth = SalesVisit::where('company_id', $comId)
        ->where('branch_id', $branchId)  // Filter by branch_id for SalesVisit
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->orderBy('created_at', 'desc')
        ->get();

    $cfQbIds = $visitMonth->pluck('client_id')->toArray();

    $clientList = Sds_query_book::where('qb_company_id', $comId)
        ->where('qb_branch_id', $branchId)  // Filter by branch_id for client list
        ->whereIn('qb_id', $cfQbIds)
        ->get();
        
    return view('page.crm.my-visit-report', compact('users', 'reasonList', 'visitMonth', 'clientList'));
}


}
