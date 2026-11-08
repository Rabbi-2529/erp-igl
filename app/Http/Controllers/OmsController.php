<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use App\Models\Oms_inventory;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_acc_bank_statement;
use Illuminate\Validation\ValidationException;
use App\Models\Sds_query_book;
use App\Models\Admin_user;
use App\Models\Client_feedback;
use App\Models\Sds_sms;
use Illuminate\Support\Facades\Log;
class OmsController extends Controller
{
    //
    public function oms_convence_users(){
    $com_id = Auth::user()->au_company_id;
    $user = Auth::user()->au_id;
    $team_id = Auth::user()->au_team_id;
    $branch_id = Auth::user()->au_branch_id;  // Added branch_id here
    $team_m = array();
    $user_type = Auth::user()->au_user_type;

    foreach(Admin_user::team_member($team_id, $com_id) as $member) {
        $team_m[] = $member->au_id;
    }

    $users = Admin_user::where('au_company_id',$com_id)
    ->where('au_branch_id',$branch_id)
        ->where('au_user_type', '!=',  4)
        ->when($user_type == 5, function ($query) use ($team_m) {
            return $query->whereIn('au_id', $team_m);
        })
        ->get();

    $confirm_bills = Oms_inventory::whereIn('oms_status',[4, 7])
        ->when($user_type != 4, function ($query) use ($team_m, $user) {
            return $query->whereIn('oms_staff_id', $team_m)->orWhere('oms_staff_id', $user);
        })
        ->get();

    $confirms = $confirm_bills->where('oms_com_id',$com_id)->groupBy('oms_staff_id');

    return view('page.oms.conveyance.user', compact('confirms','users', 'branch_id'));  // Passing branch_id to view
}


    public function oms_pay_convence(){
        $table = Input::all();
            $date_pay_convence = Carbon::now()->format('Y-m-d');
            $table = new Oms_inventory;
            $com_convence = Auth::user()->au_company_id;
            $branch_convence = Auth::user()->au_branch_id;
            $table->oms_payment = Input::get('oms_payment');
            $table->oms_reason = Input::get('oms_reason');
            $table->oms_credit = Input::get('oms_credit');
            $table->oms_com_id = $com_convence;
              $table->oms_branch_id = $branch_convence;
            $table->oms_staff_id = Input::get('oms_staff_id');
            $table->oms_status = 7;
            $table->oms_transaction_date = $date_pay_convence;
            $table->save();
        return redirect('/oms_convence_users');
    }

    public function oms_convence_details($id){
//        $details = Oms_inventory::whereIn('oms_status',[4,7])->where('oms_staff_id', $id)->get();
        $details = Oms_inventory::whereIn('oms_status',[4,7])->where('oms_staff_id', $id)->with('projects')->get();
        return view('page.oms.conveyance.user_details',compact('details'));
    }

public function convence_add_new_bill() {
    // Get the company ID for the authenticated user
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;


    // Get the department list for the authenticated user
    $departments = explode('-', Auth::user()->au_department);

    // Get users based on company ID, active status, and user type
    $users = Admin_user::where('au_company_id', $com)
        ->where('au_branch_id', $branch_id)
        ->where('au_status', 1)
        ->where('au_user_type', '!=', 4)
        ->select('au_user_id', 'au_name', 'au_branch_id') // Added au_branch_id to the select statement
        ->get();

    // Fetch reasons and modes from respective tables
    $reasons = DB::table('reasons')->orderBy('name', 'asc')->get();
    $modes = DB::table('conveyance_modes')->orderBy('name', 'asc')->get();

    // Return view with data
    return view('page.oms.conveyance.add-bill', compact('users', 'reasons', 'modes'));
}

public function convence_get_customer(Request $request) {
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    // Fetch customers based on company ID and search term
    $customers = Sds_query_book::where('qb_company_id', $com)
         ->where('qb_branch_id', $branch_id)
        ->select('qb_id', 'qb_company_name', 'qb_mobile') // Added qb_branch_id
        ->where('qb_company_name', 'LIKE', '%' . $request->term . '%')
        ->orWhere('qb_mobile', 'LIKE', '%' . $request->term . '%')
        ->get();

    // Return the customers if found
    if (isset($customers)) {
        return response($customers);
    }
}

    public function convence_add_new_bill_submit(Request $request){

        DB::beginTransaction();
      try {
        $table = Input::all();
        $message =
        [
            'oms_what_for.required' => 'This field must be Required',
            'oms_bill_date.required' => 'This Field must be Required',
            'oms_dfrom.required' => 'This Field Must be Required',
            'oms_mode.required' => 'This Field Must be Required',
            'oms_dto.required' => 'This Field Must be Required',
            'oms_debit.required' => 'This Field Must be Required',
            'oms_reason.required' => 'This Field Must be Required',
        ];
        $request->validate([
            'oms_what_for' => 'required',
            'oms_bill_date' => 'required',
            'oms_dfrom' => 'required',
            'oms_mode' => 'required',
            'oms_dto' => 'required',
            'oms_debit' => 'required',
            'oms_reason' => 'required',
        ],$message);


          if (isset($request->engineer)){
              $staffs = implode(',',$request->engineer); //add this table
          }else{
              $staffs = Auth::user()->au_user_id;

          }
            $date_convence = Carbon::now()->format('Y-m-d');
            $table = new Oms_inventory;
            $table->oms_what_for = Input::get('oms_what_for');
            $table->oms_reason = Input::get('oms_reason');
            $table->oms_bill_date = Input::get('oms_bill_date');
            $table->oms_dfrom = Input::get('oms_dfrom');
            $table->oms_mode = Input::get('oms_mode');
            $table->oms_dto = Input::get('oms_dto');
            $table->oms_debit = Input::get('oms_debit');
            $table->oms_com_id = Auth::user()->au_company_id;
             $table->oms_branch_id = Auth::user()->au_branch_id;
            $table->oms_staff_id = Auth::user()->au_id;
            $table->oms_status = 1;
            $table->oms_insert_at = $date_convence;
            $table->oms_staffs_user_id = $staffs;

            $table-> save();
      } catch (\Exception $e) {
        DB::rollback();
        return redirect('/convence_add_new_bill')->with(['convence_error' => 'Conveyance Bill Added Error!'.$e->getMessage()]);
      }
      DB::commit();
      return redirect('/convence_add_new_bill')->with(['msg4' => 'Conveyance Bill Added!']);
    }
//     public function convence_pending_bill() {
//       $user = Auth::user()->au_id;
//       $com = Auth::user()->au_company_id;
//       $team_id = Auth::user()->au_team_id;
//       $team_m = array();
//       $feedbacks = [];
      
//       $hasPermission = explode('-', Auth::user()->au_permission_status);
  
//       foreach(Admin_user::team_member($team_id, $com) as $member) {
//           $team_m[] = $member->au_id;
//       }
  
//       if (Auth::user()->au_user_type == 4) {
//           $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
//                                   ->where('oms_com_id', $com)
//                                   ->with('mode')
//                                   ->get();
  
//           $notify = Sds_sms::where('sms_status', 2)
//                           ->pluck('sms_cf_id')->toArray();
  
//       } 
//     //   elseif (Auth::user()->au_user_type == 5 && in_array(89, $hasPermission)) {
//     elseif ( in_array(28, $hasPermission)) {
//           $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
//                                   ->where('oms_com_id', $com)
//                                   ->with('mode')
//                                   ->get();
//       } elseif (Auth::user()->au_user_type == 5) {
//           $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
//                                   ->where('oms_com_id', $com)
//                                   ->whereIn('oms_staff_id', $team_m)
//                                   ->with('mode')
//                                   ->get();
//       } else {
//           $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
//                                   ->where('oms_com_id', $com)
//                                   ->where('oms_staff_id', $user)
//                                   ->with('mode')
//                                   ->get();
//       }
      
//       return view('page.oms.conveyance.pending', compact('pendings', 'feedbacks'));
//   }

public function convence_pending_bill()
{
    $user = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id; // ✅ branch id
    $team_id = Auth::user()->au_team_id;
    $team_m = [];
    $feedbacks = [];

    $hasPermission = explode('-', Auth::user()->au_permission_status);

    foreach (Admin_user::team_member($team_id, $com) as $member) {
        $team_m[] = $member->au_id;
    }

    if (Auth::user()->au_user_type == 4) {

        $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->with('mode')
            ->orderBy('oms_bill_date', 'desc')
            ->orderBy('sl_id', 'desc')
            ->get();

        $notify = Sds_sms::where('sms_status', 2)
            ->pluck('sms_cf_id')
            ->toArray();

    } elseif (Auth::user()->au_user_type == 6 && in_array(92, $hasPermission)) {

        $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->with('mode')
            ->orderBy('oms_bill_date', 'desc')
            ->orderBy('sl_id', 'desc')
            ->get();

    } elseif (Auth::user()->au_user_type == 5 && in_array(92, $hasPermission)) {

        $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->with('mode')
            ->orderBy('oms_bill_date', 'desc')
            ->orderBy('sl_id', 'desc')
            ->get();

    } elseif (Auth::user()->au_user_type == 5) {

        $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->whereIn('oms_staff_id', $team_m)
            ->with('mode')
            ->orderBy('oms_bill_date', 'desc')
            ->orderBy('sl_id', 'desc')
            ->get();

    } else {

        $pendings = Oms_inventory::whereIn('oms_status', [1, 9])
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->where('oms_staff_id', $user)
            ->with('mode')
            ->orderBy('oms_bill_date', 'desc')
            ->orderBy('sl_id', 'desc')
            ->get();
    }

    return view('page.oms.conveyance.pending', compact('pendings', 'feedbacks'));
}



public function convence_verification_accept($id) {
    try {
        $user = auth()->user();

        // Check if the user has the required permission and user type


        $pending = Oms_inventory::findOrFail($id);
        $pending->oms_status = 9; // Set status to 9 for verification accept
        $pending->save();

        return response()->json([
            'success' => true,
            'message' => 'Verification accepted successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}

public function convence_verification_reject(Request $request, $id) {
    try {
        $user = auth()->user();

        // Check if the user has the required permission and user type


        $pending = Oms_inventory::findOrFail($id);

        // Check if the rejection reason is provided
        $rejectionReason = $request->input('rejection_reason', '');

        $pending->oms_status = 10; // Set status to 10 for verification reject
        $pending->oms_rejection_reason = $rejectionReason; // Save rejection reason
        $pending->save();

        return response()->json([
            'success' => true,
            'message' => 'Verification rejected successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ], 500);
    }
}
    public function my_pending_bill(){
      $com = Auth::user()->au_company_id;
         $branchid = Auth::user()->au_branch_id;
      
      $leader = Auth::user()->au_id;
      $my_pendings = Oms_inventory::where('oms_com_id',$com)
                   ->where('oms_branch_id', $branchid)
                                  ->where('oms_staff_id',$leader)
                                  ->where('oms_status',1)
                                  ->with('mode')
                                  ->get();
      return view('page.oms.conveyance.my-pending-bill',compact('my_pendings'));
    }

        public function convence_confirm_update(Request $request, $id)
    {
        $pending = Oms_inventory::find($id);

        if (!$pending) {
            return $this->confirmResponse($request, false, 'Bill not found.', 404);
        }

        if ($pending->oms_status != 9) {
            return $this->confirmResponse($request, false, 'Bill not verified.', 422);
        }

        $pending->oms_status = 4; // Confirmed
        $pending->save();

        return $this->confirmResponse($request, true, 'Bill confirmed successfully.');
    }

    // JSON for AJAX calls, redirect for normal clicks
    private function confirmResponse(Request $request, $success, $message, $status = 200)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $status);
        }

        return redirect('/convence_pending_bill')->with($success ? 'success' : 'error', $message);
    }
    
    public function convence_reject_update(Request $request, $id)
    {
        $pending = Oms_inventory::find($id);
    
        // Check if the record exists
        if (!$pending) {
            return redirect('/convence_pending_bill')->with('error', 'Bill not found.');
        }
    
        if ($pending->oms_status != 9) {
            return redirect('/convence_pending_bill')->with('error', 'Bill not verified.');
        }
    
        // Update the status and save the rejection reason
        $pending->oms_status = 3; // Update status to 3
        $pending->oms_rejection_reason = $request->input('rejection_reason'); // Save rejection reason
        $pending->save();
    
        // Return a JSON response for AJAX
        return redirect('/convence_pending_bill')->with('success', 'Bill Rejected successfully.');
    }
    public function convence_reject_bill_update(Request $request,$id){
        $reject =Oms_inventory::find($id);
        $reject ->oms_what_for = $request->input('reason');
        $reject ->oms_dfrom = $request->input('from');
        $reject ->oms_mode = $request->input('via');
        $reject ->oms_dto = $request->input('to');
        $reject ->oms_debit =$request->input('oms_debit');
        $reject ->oms_status= 1;
        $reject ->save();
        return redirect('/convence_reject_bill');
    }
public function oms_update_bill($id){
    $bill = Oms_inventory::find($id);
      $qb = Sds_query_book::where('qb_id',$bill->oms_what_for)->get(['qb_id','qb_branch_id', 'qb_company_name', 'qb_mobile']);
    return view('page.oms.conveyance.oms_update_bill', compact('bill','qb'));
}


    public function fetchQbData(Request $request)
    {
        $searchTerm = $request->get('term'); // For search functionality
        $qb = Sds_query_book::where('qb_company_name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('qb_mobile', 'LIKE', "%{$searchTerm}%")
            ->take(1000)
            ->get(['qb_id', 'qb_company_name', 'qb_mobile','qb_branch_id']);
        return response()->json($qb);
    }
    public function convence_reject_bill() {
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $user = Auth::user()->au_id;
        $hasPermission = explode('-', Auth::user()->au_permission_status);
    
        if (Auth::user()->au_user_type == 4 || in_array(28, $hasPermission)) {
            $rejects = Oms_inventory::whereIn('oms_status', [3, 10])  // Use whereIn to filter multiple statuses
                                    ->where('oms_com_id', $com_id)
                                           ->where('oms_branch_id', $branch_id)
                                    ->with('mode')
                                    ->get();
            $reasons = DB::table('reasons')->groupBy('name')->get();
        } else {
            $rejects = Oms_inventory::whereIn('oms_status', [3, 10])  // Use whereIn to filter multiple statuses
                                    ->where('oms_com_id', $com_id)
                                      ->where('oms_branch_id', $branch_id)
                                    ->where('oms_staff_id', $user)
                                    ->with('mode')
                                    ->get();
            $reasons = DB::table('reasons')->groupBy('name')->get();
        }
    
        return view('page.oms.conveyance.reject', compact('rejects', 'reasons'));
    }
    public function convence_paid_bill(){
        $com_id = Auth::user()->au_company_id;
             $branch_id = Auth::user()->au_branch_id;
        $user = Auth::user()->au_id;
        $paid = Oms_inventory::whereIn('oms_status',[7,1])
                             ->where('oms_com_id',$com_id)
                               ->where('oms_branch_id', $branch_id)
                             ->where('oms_staff_id',$user)
                             ->get();
    	return view('page.oms.conveyance.final-bill',compact('paid'));
    }
    
    public function oms_lunch_users(){
    $company = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    $team_id = Auth::user()->au_team_id;
    $user = Auth::user()->au_id;
    $user_type = Auth::user()->au_user_type;
    $team_m = Admin_user::team_member($team_id, $company)->pluck('au_id')->toArray();

    // Filter users by company, branch_id, and user type
    $users = Admin_user::where('au_company_id', $company)
        ->where('au_branch_id', $branch_id)
        ->where('au_user_type', '!=', 4)
        ->when($user_type == 5, function ($query) use ($team_m) {
            return $query->whereIn('au_id', $team_m);
        })->get();

    // Filter confirm lunch records by company, branch_id, and user type
    $confirm_lunch = Oms_inventory::whereIn('oms_status', [6, 8])
        ->where('oms_com_id', $company)
        ->where('oms_branch_id', $branch_id)
        ->when($user_type == 5, function ($query) use ($team_m) {
            return $query->whereIn('oms_staff_id', $team_m);
        })
        ->when($user_type == 6, function ($query) use ($user) {
            return $query->where('oms_staff_id', $user);
        })
        ->get();

    // Group lunch records by staff, company, and branch
    $lunch = $confirm_lunch->groupBy(function($item) {
        return $item->oms_staff_id . '-' . $item->oms_com_id . '-' . $item->oms_branch_id;
    });

    return view('page.oms.meal.user', compact('users', 'lunch'));
}

    public function oms_lunch_details($id){
        $lunch_details = Oms_inventory::whereIn('oms_status',[6,8])
                                      ->where('oms_staff_id',$id)
                                      ->get();
        return view('page.oms.meal.user_details',compact('lunch_details'));
    }
   public function oms_pay_lunch(){
    $date_pay_lunch = Carbon::now()->format('Y-m-d');
    $com_lunch = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id; // Get the branch ID of the authenticated user
    $table = Input::all();
    
    $table = new Oms_inventory;
    $table->oms_staff_id = Input::get('oms_staff_id');
    $table->oms_payment = Input::get('oms_payment');
    $table->oms_reason = Input::get('oms_reason');
    $table->oms_credit = Input::get('oms_credit');
    $table->oms_transaction_date = $date_pay_lunch;
    $table->oms_com_id = $com_lunch;
    $table->oms_branch_id = $branch_id; // Set the branch ID for the record
    $table->oms_status = 8;
    $table->save();
    
    return redirect('/oms_lunch_users');
}

    public function lunch_add_new_bill(){
         $com = Auth::user()->au_company_id;
              $branch_id = Auth::user()->au_branch_id;

        $users = Admin_user::where('au_company_id',$com)
          ->where('au_branch_id', $branch_id)
            ->where('au_status',1)
            ->where('au_user_type','!=',4)
            ->select('au_user_id','au_name')
            ->get();

        $reasons =  DB::table('reasons')->groupBy('name')->get();


//         $q = Sds_query_book::where('qb_company_id',$com)->get();
    	return view('page.oms.meal.add-bill',compact('users','reasons'));
    }
    public function lunch_add_new_bill_submit(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validate request data
            $message = [
                'oms_bill_date.required' => 'Fill The Bill Date',
                'oms_bill_date.date_format' => 'Invalid Bill Date Format',
                'oms_debit.required' => 'Amount Must be Included',
                'oms_debit.numeric' => 'Amount Must be a Number',
            ];
    
            $request->validate([
                'oms_bill_date' => 'required|date_format:Y-m-d',
                'oms_debit' => 'required|numeric',
            ], $message);
    
            $guest = $request->guest ?? 0;
            $staffs = isset($request->engineer) ? implode(',', $request->engineer) : Auth::user()->au_user_id;
    
            $date = Carbon::now()->format('Y-m-d');
    
            $table = new Oms_inventory;
            $table->oms_staffs_user_id = $staffs;
            $table->menu = $request->input('menu');
            $table->oms_what_for = $request->input('reason');
            $table->oms_reason = $request->input('oms_reason');
            $table->oms_bill_date = $request->input('oms_bill_date');
            $table->oms_person = $guest;
            $table->oms_debit = $request->input('oms_debit');
            $table->oms_com_id = Auth::user()->au_company_id;
              $table->oms_branch_id = Auth::user()->au_branch_id;
            $table->oms_staff_id = Auth::user()->au_id;
            $table->oms_insert_at = $date;
            $table->oms_status = 2;
            $table->save();
    
            DB::commit();
            return redirect('/lunch_add_new_bill')->with(['msg3' => 'Meal Bill Added!']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Meal Bill Added Error: ' . $e->getMessage());
            return redirect('/lunch_add_new_bill')->with(['luck_error' => 'Meal Bill Added Error!']);
        }
    }
   public function lunch_pending_bill()
{
    $u = Auth::user()->au_id;
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id; // ✅ added
    $team_id = Auth::user()->au_team_id;
    $team_m = array();

    $hasPermission = in_array('90', explode('-', Auth::user()->au_permission_status));

    foreach (Admin_user::team_member($team_id, $com) as $member) {
        $team_m[] = $member->au_id;
    }

    if (Auth::user()->au_user_type == 4) {

        $lunch_bills = Oms_inventory::with('what_info_oms')
            ->where('oms_status', '2')
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->get();

    } elseif (Auth::user()->au_user_type == 5 && $hasPermission) {

        $lunch_bills = Oms_inventory::with('what_info_oms')
            ->where('oms_status', '2')
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->get();

    } elseif (Auth::user()->au_user_type == 5) {

        $lunch_bills = Oms_inventory::with('what_info_oms')
            ->where('oms_status', '2')
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->whereIn('oms_staff_id', $team_m)
            ->get();

    } else {

        $lunch_bills = Oms_inventory::with('what_info_oms')
            ->where('oms_status', '2')
            ->where('oms_com_id', $com)
            ->where('oms_branch_id', $branch_id) // ✅ added
            ->where('oms_staff_id', $u)
            ->get();
    }

    return view('page.oms.meal.pending', compact('lunch_bills'));
}


    public function lunch_my_pending_bill(){
      $com = Auth::user()->au_company_id;
           $branch_id = Auth::user()->au_branch_id;
      $leader = Auth::user()->au_id;
      $my_pendings = Oms_inventory::where('oms_com_id',$com)
                                   ->where('oms_branch_id',$branch_id)
                                  ->where('oms_staff_id',$leader)
                                  ->where('oms_status',2)
                                  ->get();
      return view('page.oms.meal.my-pending-bill',compact('my_pendings'));
    }

    public function lunch_pending_confirm(Request $request,$id){
            $lunch_bills = Oms_inventory::find($id);
            $lunch_bills->oms_status = 6;
            $lunch_bills->save();
            return redirect('/lunch_pending_bill');
    }
    public function lunch_pending_reject(Request $request,$id){
            $lunch_bills = Oms_inventory::find($id);
            $lunch_bills->oms_status = 5;
            $lunch_bills->save();
            return redirect('lunch_pending_bill');
    }
    public function lunch_reject_bill(){
        $com = Auth::user()->au_company_id;
             $branch_id = Auth::user()->au_branch_id;
        $u = Auth::user()->au_id;

        if (Auth::user()->au_user_type == 4) {
          $lunch_rejects = Oms_inventory::where('oms_status','5')
                                        ->where('oms_com_id',$com)
                                           ->where('oms_branch_id',$branch_id)
                                        ->get();
        }else {
          $lunch_rejects = Oms_inventory::where('oms_status','5')
                                        ->where('oms_staff_id',$u)
                                        ->get();
        }
        // dd($lunch_rejects);
    	return view('page.oms.meal.reject',compact('lunch_rejects'));
    }
    public function lunch_reject_update(Request $request,$id){
            $lunch_rejects = Oms_inventory::find($id);
            $lunch_rejects->oms_what_for = $request->input('lunch_customer');
            $lunch_rejects->oms_reason = $request->input('lunch_reason');
//            $lunch_rejects->oms_person = $request->input('lunch_person');
            $lunch_rejects->menu = $request->input('menu');
            $lunch_rejects->oms_debit = $request->input('lunch_cost');
            $lunch_rejects->oms_status = 2;
            $lunch_rejects->save();
            return redirect('/lunch_reject_bill');
    }
    public function oms_lunch_update_bill($id){
        $lunch_up_bills = Oms_inventory::find($id);
        $com = Auth::user()->au_company_id;
       $branch_id = Auth::user()->au_branch_id; // Get the branch ID of the authenticated user

    $lunch_c = Sds_query_book::where('qb_company_id', $com)
        ->where('qb_branch_id', $branch_id) // Add branch ID condition
        ->get();
        return view('pages.oms_lunch_update_bill',compact('lunch_up_bills','lunch_c'));
    }
    public function lunch_paid_bill(){
      $com_id = Auth::user()->au_company_id;
            $branch_id = Auth::user()->au_branch_id;
     
      $staff = Auth::user()->au_id;
//      $lunch_pays = Oms_inventory::whereIn('oms_status',[2,8])
      $lunch_pays = Oms_inventory::whereIn('oms_status',[2,8])
                                 ->where('oms_com_id',$com_id)
                                    ->where('oms_branch_id',$branch_id)
                                 ->where('oms_staff_id',$staff)
                                 ->get();
    	return view('page.oms.meal.final-bill',compact('lunch_pays'));
    }

    public function oms_convence_bill_users(Request $request){
      $id = $request->id;
       $user_debit = Oms_inventory::where('oms_staff_id',$id)
                                ->where('oms_status',4)
                                ->sum('oms_debit');

        $user_credit = Oms_inventory::where('oms_staff_id',$id)
                                 ->where('oms_status',7)
                                 ->sum('oms_credit');

        $amount = $user_credit - $user_debit;
        return $amount;
    }

    public function oms_lunch_bill_users(Request $request){
      $id = $request->id;
       $user_debit = Oms_inventory::where('oms_staff_id',$id)
                                ->where('oms_status',6)
                                ->sum('oms_debit');

        $user_credit = Oms_inventory::where('oms_staff_id',$id)
                                 ->where('oms_status',8)
                                 ->sum('oms_credit');

        $amount = $user_credit - $user_debit;
        return $amount;
    }


    public function accounts()
{
    $u = Auth::user();
    $com_id = $u->au_company_id;
    $branch_id = $u->au_branch_id;
    $user = $u->au_id;
    $team_id = $u->au_team_id;
    $team_m = [];

    foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
        $team_m[] = $member->au_id;
    }

    // Users
    if ($u->au_user_type == 4) {
        $users = Admin_user::where('au_company_id', $com_id)
            ->where('au_branch_id', $branch_id)
            ->where('au_user_type', '!=', 4)
            ->get();

        $confirm_bills = Oms_inventory::whereIn('oms_status', [4,6,7,8])
            ->where('oms_com_id', $com_id)
            ->where('oms_branch_id', $branch_id)
            ->get();
    } elseif ($u->au_user_type == 5) {
        $users = Admin_user::where('au_company_id', $com_id)
            ->where('au_branch_id', $branch_id)
            ->where('au_user_type', '!=', 4)
            // ->whereIn('au_id', $team_m) // uncomment if needed
            ->get();

        $confirm_bills = Oms_inventory::whereIn('oms_status', [4,6,7,8])
            ->where('oms_com_id', $com_id)
            ->where('oms_branch_id', $branch_id)
            // ->whereIn('oms_staff_id', $team_m) // uncomment if needed
            ->get();
    } elseif ($u->au_user_type == 6) {
        $users = Admin_user::where('au_company_id', $com_id)
            ->where('au_branch_id', $branch_id)
            ->where('au_user_type', '!=', 4)
            ->get();

        $confirm_bills = Oms_inventory::whereIn('oms_status', [4,6,7,8])
            ->where('oms_com_id', $com_id)
            ->where('oms_branch_id', $branch_id)
            ->where('oms_staff_id', $user)
            ->get();
    }

    // Group by staff_id properly
    $confirms = $confirm_bills->groupBy('oms_staff_id');

    // Bank accounts the bill can be paid from
    $banks = Inv_acc_bank_info::where('inv_abi_company_id', $com_id)
        ->where('inv_abi_branch_id', $branch_id)
        ->where('inv_abi_status', 1)
        ->with('bank_info')
        ->get();

    return view('page.oms.accounts', compact('confirms', 'users', 'banks'));
}

public function accounts_bank_balance(Request $request)
{
    return Inv_acc_bank_statement::getAvailableBalanceByBankId($request->bank_id);
}


public function accounts_details($id)
{
    // Eager load the 'mode' relationship to get the 'name' directly
    $details = Oms_inventory::whereIn('oms_status', [4, 6, 7, 8])
                             ->where('oms_staff_id', $id)
                             ->with('mode') // Eager load the mode relationship
                             ->get();

    return view('pages.accounts_details', compact('details'));
}
    public function accounts_bill_users(Request $request){
      $id = $request->id;
       $user_debit = Oms_inventory::where('oms_staff_id',$id)
                                ->whereIn('oms_status',[4,6])
                                ->sum('oms_debit');

        $user_credit = Oms_inventory::where('oms_staff_id',$id)
                                 ->whereIn('oms_status',[7,8])
                                 ->sum('oms_credit');

        $amount = $user_credit - $user_debit;
        return $amount;
    }

public function pay_bills(Request $request)
{
    $request->validate([
        'oms_payment' => 'required',
        'oms_reason' => 'required|string',
        'oms_credit' => 'required|numeric|min:0',
        'oms_staff_id' => 'required',
        'paid_for' => 'required|in:7,8',
        'oms_transaction_date' => 'required|date_format:Y-m-d',
        'bank_id' => 'required|integer',
        'service_charge' => 'nullable|numeric|min:0',
    ]);

    $bank = Inv_acc_bank_info::where('inv_abi_id', $request->input('bank_id'))
        ->where('inv_abi_company_id', Auth::user()->au_company_id)
        ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
        ->where('inv_abi_status', 1)
        ->first();

    if (!$bank) {
        throw ValidationException::withMessages([
            'bank_id' => 'The selected bank does not belong to your company.',
        ]);
    }

    DB::transaction(function () use ($request, $bank) {
        $table = new Oms_inventory;

        $table->oms_payment = $request->input('oms_payment');
        $table->oms_reason = $request->input('oms_reason');
        $table->oms_credit = $request->input('oms_credit');
        $table->oms_com_id = Auth::user()->au_company_id;
        $table->oms_branch_id = Auth::user()->au_branch_id;
        $table->oms_staff_id = $request->input('oms_staff_id');
        $table->oms_status = $request->input('paid_for');
        $table->oms_transaction_date = $request->input('oms_transaction_date');

        $table->save();

        $voucherNo = Carbon::now()->format('YmdHis');
        $billFor = $request->input('paid_for') == 7 ? 'Conveyance' : 'Lunch';
        $description = $billFor.' bill paid: '.$request->input('oms_reason');

        // Bill payment is cut from the selected bank
        $payment = new Inv_acc_bank_statement();
        $payment->inv_abs_company_id = Auth::user()->au_company_id;
        $payment->inv_abs_branch_id = Auth::user()->au_branch_id;
        $payment->inv_abs_inventory_id = $table->sl_id;
        $payment->inv_abs_reference_id = $table->oms_staff_id;
        $payment->inv_abs_reference_type = 10; //10= OMS Bill Payment
        $payment->inv_abs_bank_id = $bank->inv_abi_id;
        $payment->inv_abs_debit = $request->input('oms_credit');
        $payment->inv_abs_credit = 0;
        $payment->inv_abs_transaction_date = $table->oms_transaction_date;
        $payment->inv_abs_voucher_no = $voucherNo;
        $payment->inv_abs_description = $description;
        $payment->inv_abs_status = 1;
        $payment->inv_abs_submit_by = Auth::user()->au_id;
        $payment->inv_abs_submit_at = Carbon::now();
        $payment->save();

        // Optional service charge is cut from the same bank as its own transaction
        if ($request->input('service_charge') > 0) {
            $charge = new Inv_acc_bank_statement();
            $charge->inv_abs_company_id = Auth::user()->au_company_id;
            $charge->inv_abs_branch_id = Auth::user()->au_branch_id;
            $charge->inv_abs_inventory_id = $table->sl_id;
            $charge->inv_abs_reference_id = $table->oms_staff_id;
            $charge->inv_abs_reference_type = 11; //11= Bank Service Charge
            $charge->inv_abs_bank_id = $bank->inv_abi_id;
            $charge->inv_abs_debit = $request->input('service_charge');
            $charge->inv_abs_credit = 0;
            $charge->inv_abs_service_charge = $request->input('service_charge');
            $charge->inv_abs_transaction_date = $table->oms_transaction_date;
            $charge->inv_abs_voucher_no = $voucherNo;
            $charge->inv_abs_description = 'Service charge: '.$description;
            $charge->inv_abs_status = 1;
            $charge->inv_abs_submit_by = Auth::user()->au_id;
            $charge->inv_abs_submit_at = Carbon::now();
            $charge->save();
        }
    });

    return redirect('/accounts')->with([
        'success' => 'Due Bill Paid Successfully'
    ]);
}
}
