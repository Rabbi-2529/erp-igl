<?php

namespace App\Http\Controllers;

//use Auth;
use Illuminate\Support\Facades\Auth;
use App\Models\Client_feedback;
use App\Models\Sds_query_book;
use App\Models\Inv_product_detail;
use App\Models\Admin_user;
use App\Models\Au_access;
use App\Models\Au_access_company;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    //
public function todays_followup() {
    $today = Carbon::now()->format('Y-m-d');
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    $teamId = Auth::user()->au_team_id;
    $team_m_f = array();
    $user = Auth::user()->au_id;

    $team = Admin_user::where('au_company_id', $com)
                      ->where('au_branch_id', $branch)
                      ->where('au_team_id', $teamId)
                      ->first();

    $admin = Admin_user::where('au_user_type', 4)
                       ->where('au_company_id', $com)
                       ->where('au_branch_id', $branch)
                       ->first();

    $team_m_f[] = $user;

    foreach (Admin_user::team_member($teamId, $com) as $member) {
        $team_m_f[] = $member->au_id;
    }

    $client = Sds_query_book::where('qb_company_id', $com)
                            ->where('qb_branch_id', $branch)
                            ->first();

    if (Auth::user()->au_user_type == 6) {
        $my_clients = Sds_query_book::where('qb_company_id', $com)
                                    ->where('qb_branch_id', $branch)
                                    ->where('qb_entry_by', $user)
                                    ->pluck('qb_id')
                                    ->toArray();

        $all_clients = Client_feedback::select('cf_qb_id')
            ->where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_qb_id', $my_clients)
            ->where('cf_next_date', $today)
            ->pluck('cf_qb_id')
            ->toArray();

        $all_latest_feedback_id = [];
        foreach ($all_clients as $client_id) {
            $client_last_feedback = Client_feedback::select('cf_id')
                ->where('cf_company_id', $com)
                ->where('cf_branch_id', $branch)
                ->whereIn('cf_qb_id', $my_clients)
                ->where('cf_qb_id', $client_id)
                ->orderBy('cf_id', 'DESC')
                ->first();
            if (!empty($client_last_feedback)) {
                $all_latest_feedback_id[] = $client_last_feedback->cf_id;
            }
        }

        $todays = Client_feedback::where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_qb_id', $my_clients)
            ->whereIn('cf_id', $all_latest_feedback_id)
            ->where('cf_next_date', $today)
            ->get();

    } elseif (Auth::user()->au_user_type == 5) {
        $team_m_f[] = $admin->au_id;

        $all_clients = Client_feedback::select('cf_qb_id')
            ->where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_entry_by', $team_m_f)
            ->where('cf_next_date', $today)
            ->pluck('cf_qb_id')
            ->toArray();

        $all_latest_feedback_id = [];
        foreach ($all_clients as $client_id) {
            $client_last_feedback = Client_feedback::select('cf_id')
                ->where('cf_company_id', $com)
                ->where('cf_branch_id', $branch)
                ->whereIn('cf_entry_by', $team_m_f)
                ->where('cf_qb_id', $client_id)
                ->orderBy('cf_id', 'DESC')
                ->first();
            if (!empty($client_last_feedback)) {
                $all_latest_feedback_id[] = $client_last_feedback->cf_id;
            }
        }

        $todays = Client_feedback::where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_entry_by', $team_m_f)
            ->whereIn('cf_id', $all_latest_feedback_id)
            ->where('cf_next_date', $today)
            ->get();

    } elseif (Auth::user()->au_user_type == 4) {
        $all_clients = Client_feedback::select('cf_qb_id')
            ->where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->where('cf_next_date', $today)
            ->pluck('cf_qb_id')
            ->toArray();

        $all_latest_feedback_id = [];
        foreach ($all_clients as $client_id) {
            $client_last_feedback = Client_feedback::select('cf_id')
                ->where('cf_company_id', $com)
                ->where('cf_branch_id', $branch)
                ->where('cf_qb_id', $client_id)
                ->orderBy('cf_id', 'DESC')
                ->first();
            if (!empty($client_last_feedback)) {
                $all_latest_feedback_id[] = $client_last_feedback->cf_id;
            }
        }

        $todays = Client_feedback::where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_id', $all_latest_feedback_id)
            ->where('cf_next_date', $today)
            ->get();

    } elseif (Auth::user()->au_user_type == 7) {
        $admin_access = Auth::user()->agm_permission;
        $agm = explode('-', $admin_access);

        foreach (Admin_user::team_member($agm, $com) as $member) {
            $team_m_f[] = $member->au_id;
        }

        $all_clients = Client_feedback::select('cf_qb_id')
            ->where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_entry_by', $team_m_f)
            ->where('cf_next_date', $today)
            ->pluck('cf_qb_id')
            ->toArray();

        $all_latest_feedback_id = [];
        foreach ($all_clients as $client_id) {
            $client_last_feedback = Client_feedback::select('cf_id')
                ->where('cf_company_id', $com)
                ->where('cf_branch_id', $branch)
                ->whereIn('cf_entry_by', $team_m_f)
                ->where('cf_qb_id', $client_id)
                ->orderBy('cf_id', 'DESC')
                ->first();
            if (!empty($client_last_feedback)) {
                $all_latest_feedback_id[] = $client_last_feedback->cf_id;
            }
        }

        $todays = Client_feedback::where('cf_company_id', $com)
            ->where('cf_branch_id', $branch)
            ->whereIn('cf_entry_by', $team_m_f)
            ->whereIn('cf_id', $all_latest_feedback_id)
            ->where('cf_next_date', $today)
            ->get();
    }

    return view('page.crm.followup.today-followup', compact('todays'));
}

  public function given_followup() {
    $today = Carbon::now()->format('Y-m-d');
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    $teamId = Auth::user()->au_team_id;
    $user = Auth::user()->au_id;

    $user_cus = Sds_query_book::where('qb_company_id', $com)
                              ->where('qb_branch_id', $branch)
                              ->where('qb_staff_id', $user)
                              ->first();

    $admin = Admin_user::where('au_user_type', 4)
                       ->where('au_company_id', $com)
                       ->where('au_branch_id', $branch)
                       ->first();

    $team_m = array();
    $team = Admin_user::where('au_company_id', $com)
                      ->where('au_branch_id', $branch)
                      ->where('au_team_id', $teamId)
                      ->first();

    $team_m[] = Auth::user()->au_id;

    foreach (Admin_user::team_member($teamId, $com) as $member) {
        $team_m[] = $member->au_id;
    }

    if (Auth::user()->au_user_type == 6) {
        $given_fu = Client_feedback::where('cf_date', $today)
                                   ->where('cf_company_id', $com)
                                   ->where('cf_branch_id', $branch)
                                   ->where('cf_entry_by', $user)
                                   ->get();

    } elseif (Auth::user()->au_user_type == 5) {
        $team_m[] = $admin->au_id;

        $given_fu = Client_feedback::where('cf_date', $today)
                                   ->where('cf_company_id', $com)
                                   ->where('cf_branch_id', $branch)
                                   ->whereIn('cf_entry_by', $team_m)
                                   ->get();

    } elseif (Auth::user()->au_user_type == 4) {
        $given_fu = Client_feedback::where('cf_date', $today)
                                   ->where('cf_company_id', $com)
                                   ->where('cf_branch_id', $branch)
                                   ->get();

    } elseif (Auth::user()->au_user_type == 7) {
        $admin_access = Auth::user()->agm_permission;
        $agm = explode('-', $admin_access);

        foreach (Admin_user::team_member($agm, $com) as $member) {
            $team_m[] = $member->au_id;
        }

        $given_fu = Client_feedback::where('cf_date', $today)
                                   ->where('cf_company_id', $com)
                                   ->where('cf_branch_id', $branch)
                                   ->whereIn('cf_entry_by', $team_m)
                                   ->get();
    }

    return view('page.crm.followup.given_followup', compact('given_fu'));
}


   public function followup_list() {
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;
    $teamId = Auth::user()->au_team_id;
    $today = Carbon::now()->format('Y-m-d');
    $user = Auth::user()->au_id;

    $admin = Admin_user::where('au_user_type', 4)
                       ->where('au_company_id', $com)
                       ->where('au_branch_id', $branch)
                       ->first();

    $team_m_f_l = array();

    $team = Admin_user::where('au_company_id', $com)
                      ->where('au_branch_id', $branch)
                      ->where('au_team_id', $teamId)
                      ->first();

    $team_m_f_l[] = Auth::user()->au_id;

    foreach (Admin_user::team_member($teamId, $com) as $member) {
        $team_m_f_l[] = $member->au_id;
    }

    if (Auth::user()->au_user_type == 4) {
        $tc_list = Sds_query_book::where('qb_submit_date', $today)
                                 ->where('qb_company_id', $com)
                                 ->where('qb_branch_id', $branch)
                                 ->get();

    } elseif (Auth::user()->au_user_type == 5) {
        $team_m_f_l[] = $admin->au_id;

        $tc_list = Sds_query_book::where('qb_submit_date', $today)
                                 ->where('qb_company_id', $com)
                                 ->where('qb_branch_id', $branch)
                                 ->whereIn('qb_entry_by', $team_m_f_l)
                                 ->get();

    } elseif (Auth::user()->au_user_type == 6) {
        $tc_list = Sds_query_book::where('qb_submit_date', $today)
                                 ->where('qb_company_id', $com)
                                 ->where('qb_branch_id', $branch)
                                 ->where('qb_entry_by', $user)
                                 ->get();

    } elseif (Auth::user()->au_user_type == 7) {
        $admin_access = Auth::user()->agm_permission;
        $agm = explode('-', $admin_access);

        foreach (Admin_user::team_member($agm, $com) as $member) {
            $team_m_f_l[] = $member->au_id;
        }

        $tc_list = Sds_query_book::where('qb_submit_date', $today)
                                 ->where('qb_company_id', $com)
                                 ->where('qb_branch_id', $branch)
                                 ->whereIn('qb_entry_by', $team_m_f_l)
                                 ->get();
    }

    return view('page.crm.followup.followup_list', compact('tc_list'));
}

   public function page_permission($au_id) {
    $com = Auth::user()->au_company_id;
    $branch = Auth::user()->au_branch_id;

    $admin_user = Admin_user::where('au_id', $au_id)
                            ->where('au_company_id', $com)
                            ->where('au_branch_id', $branch)
                            ->first();

    if (!empty($admin_user)) {
        $company_access = explode('-', Auth::user()->au_permission_status);
        $exist_permissions = explode('-', $admin_user->au_permission_status);

        $permissions = Au_access::whereNull('parent_menu')
                                ->where('status', 1)
                                ->whereIn('au_access_company_id', $company_access)
                                ->get();

        return view('page.hrm.permission', compact('permissions', 'exist_permissions', 'admin_user'));
    } else {
        session()->flash('type', 'danger');
        session()->flash('message', 'Invalid User');
        return redirect()->back();
    }
}


    public function permission_user_type(){
      $permissions = Au_access::all();
      return view('pages.permission_user_set',compact('permissions'));
    }

    public function page_permission_submit(Request $request, $au_id)
    {
      $admin_user = Admin_user::where('au_id', $au_id)->first();
      if(!empty($admin_user)) {
        if (!empty($request->au_permission)) {
          $new_permission = implode('-', $request->au_permission);
        } else {
          $new_permission = null;
        }
        $admin_user->au_permission_status = $new_permission;
        $admin_user->save();

        session()->flash('type', 'success');
        session()->flash('message', 'Permission Updated Successfully');
        return redirect()->back();

      } else {
        session()->flash('type', 'danger');
        session()->flash('message', 'Invalid User');
        return redirect()->back();
      }
    }

 public function page_permission_com($au_com_id)
{
    $branchId = Auth::user()->au_branch_id;

    $admin_user = Admin_user::where('au_company_id', $au_com_id)
                            ->where('au_branch_id', $branchId)
                            ->where('au_user_type', 4)
                            ->first();

    if (!empty($admin_user)) {
        $exist_permissions = explode('-', $admin_user->au_permission_status);
        $permissions = Au_access_company::where('status', 1)->get();

        return view('pages.page_permission_com', compact('permissions', 'exist_permissions', 'admin_user'));
    } else {
        session()->flash('type', 'danger');
        session()->flash('message', 'Invalid Company or Branch');
        return redirect()->back();
    }
}

   public function page_permission_com_submit(Request $request, $au_id)
{
    $branchId = Auth::user()->au_branch_id;

    $admin_user = Admin_user::where('au_company_id', $au_id)
                            ->where('au_branch_id', $branchId)
                            ->where('au_user_type', 4)
                            ->first();

    if (!empty($admin_user)) {
        $new_permission = !empty($request->au_permission)
            ? implode('-', $request->au_permission)
            : null;

        $admin_user->au_permission_status = $new_permission;
        $admin_user->save();

        session()->flash('type', 'success');
        session()->flash('message', 'Permission Updated Successfully');
        return redirect()->back();
    } else {
        session()->flash('type', 'danger');
        session()->flash('message', 'Invalid Company or Branch');
        return redirect()->back();
    }
}


 public function due_followup(Request $request){
    $com = Auth::user()->au_company_id;
    $user = Auth::user()->au_id;
    $branchId = Auth::user()->au_branch_id; // Added branch_id
    $today = $request->start_date;
    $teamId = Auth::user()->au_team_id;
    $team_m_f = array();
    
    // Include au_branch_id in team member query
    $team = Admin_user::where('au_company_id', $com)
                      ->where('au_team_id', $teamId)
                      ->where('au_branch_id', $branchId) // Added branch filter
                      ->first();
    
    $admin = Admin_user::where('au_user_type', 4)
                       ->where('au_company_id', $com)
                       ->where('au_branch_id', $branchId) // Added branch filter
                       ->first();
    
    $team_m_f[] = Auth::user()->au_id;
    
    foreach(Admin_user::team_member($teamId, $com) as $member) {
        if ($member->au_branch_id == $branchId) { // Ensure team member is from the same branch
            $team_m_f[] = $member->au_id;
        }
    }

    $client = Sds_query_book::where('qb_company_id', $com)
                            ->where('qb_branch_id', $branchId) // Added branch filter
                            ->first();

    if ($request->has('searchbtn')) {
        if (Auth::user()->au_user_type == 6) {

            $my_clients = Sds_query_book::where('qb_company_id', $com)
                                        ->where('qb_entry_by', $user)
                                        ->where('qb_branch_id', $branchId) // Added branch filter
                                        ->pluck('qb_id')->toArray();

            $all_clients = Client_feedback::select('cf_qb_id')
                ->where('cf_company_id', $com)
                ->whereIn('cf_qb_id', $my_clients)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->pluck('cf_qb_id')
                ->toArray();

            $all_latest_feedback_id = array();
            foreach ($all_clients as $client_id) {
                $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id', $com)
                    ->whereIn('cf_qb_id', $my_clients)
                    ->where('cf_qb_id', $client_id)
                    ->orderBy('cf_id', 'DESC')
                    ->first();
                if(!empty($client_last_feedback)) {
                    $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                }
            }

            $due_clients = Client_feedback::where('cf_company_id', $com)
                ->whereIn('cf_qb_id', $my_clients)
                ->whereIn('cf_id', $all_latest_feedback_id)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->get();

        } elseif (Auth::user()->au_user_type == 5) {
            $team_m_f[] = $admin->au_id;
            $all_clients = Client_feedback::select('cf_qb_id')
                ->where('cf_company_id', $com)
                ->whereIn('cf_entry_by', $team_m_f)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->pluck('cf_qb_id')
                ->toArray();

            $all_latest_feedback_id = array();
            foreach ($all_clients as $client_id) {
                $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id', $com)
                    ->whereIn('cf_entry_by', $team_m_f)
                    ->where('cf_qb_id', $client_id)
                    ->orderBy('cf_id', 'DESC')
                    ->first();
                if(!empty($client_last_feedback)) {
                    $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                }
            }

            $due_clients = Client_feedback::where('cf_company_id', $com)
                ->whereIn('cf_entry_by', $team_m_f)
                ->whereIn('cf_id', $all_latest_feedback_id)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->get();

        } elseif (Auth::user()->au_user_type == 4) {
            $all_clients = Client_feedback::select('cf_qb_id')
                ->where('cf_company_id', $com)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->pluck('cf_qb_id')
                ->toArray();

            $all_latest_feedback_id = array();
            foreach ($all_clients as $client_id) {
                $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id', $com)
                    ->where('cf_qb_id', $client_id)
                    ->orderBy('cf_id', 'DESC')
                    ->first();
                if(!empty($client_last_feedback)) {
                    $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                }
            }

            $due_clients = Client_feedback::where('cf_company_id', $com)
                ->whereIn('cf_id', $all_latest_feedback_id)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->get();
        } elseif (Auth::user()->au_user_type == 7) {
            $admin_access = Auth::user()->agm_permission;
            $agm = explode('-', $admin_access);

            foreach(Admin_user::team_member($agm, $com) as $member) {
                if ($member->au_branch_id == $branchId) { // Ensure team member is from the same branch
                    $team_m_f[] = $member->au_id;
                }
            }

            $all_clients = Client_feedback::select('cf_qb_id')
                ->where('cf_company_id', $com)
                ->whereIn('cf_entry_by', $team_m_f)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->pluck('cf_qb_id')
                ->toArray();

            $all_latest_feedback_id = array();
            foreach ($all_clients as $client_id) {
                $client_last_feedback = Client_feedback::select('cf_id')->where('cf_company_id', $com)
                    ->whereIn('cf_entry_by', $team_m_f)
                    ->where('cf_qb_id', $client_id)
                    ->orderBy('cf_id', 'DESC')
                    ->first();
                if(!empty($client_last_feedback)) {
                    $all_latest_feedback_id[] = $client_last_feedback->cf_id;
                }
            }

            $due_clients = Client_feedback::where('cf_company_id', $com)
                ->whereIn('cf_entry_by', $team_m_f)
                ->whereIn('cf_id', $all_latest_feedback_id)
                ->where('cf_next_date', $today)
                ->where('cf_branch_id', $branchId) // Added branch filter
                ->get();
        }
    } else {
        $due_clients = [];
    }

    return view('page.crm.followup.due-followup', compact('due_clients', 'today'));
}


   public function userFollowUp(Request $request){
    // Get today's date based on the request or current date
    if ($request->has('searchbtn')) {
        $today = $request->start_date;
    } else {
        $today = Carbon::now()->format('Y-m-d');
    }

    // Get the company ID, team ID, and branch ID from the authenticated user
    $com_id = Auth::user()->au_company_id;
    $team_id = Auth::user()->au_team_id;
    $branch_id = Auth::user()->au_branch_id;  // Retrieve the branch ID

    // Get users excluding type 4 and filter by company and branch
    $users = Admin_user::where('au_user_type', '!=', 4)
                        ->where('au_company_id', $com_id)
                        ->where('au_branch_id', $branch_id)  // Add branch filter
                        ->get();

    return view('pages.user-followup', compact('users', 'today'));
}

public function userFollowUpList($id, $date) {
    // Get the company ID and branch ID from the authenticated user
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;  // Retrieve the branch ID
    
    // Fetch the user by ID, company, and branch ID
    $user = Admin_user::where('au_id', $id)
                      ->where('au_company_id', $com)
                      ->where('au_branch_id', $branch_id)  // Add branch filter for the user
                      ->first();

    // Get the given follow-ups for the user on the specified date and company, and filter by branch ID
    $given_fu = Client_feedback::where('cf_entry_by', $id)
                               ->where('cf_date', $date)
                               ->where('cf_company_id', $com)
                               ->where('cf_branch_id', $branch_id)  // Add branch filter for the feedback
                               ->get();

    return view('pages.user-follow-up-list', compact('given_fu', 'user', 'date'));
}

   public function userFollowUpDuplicate($id) {
    // Get the current date
    $today = Carbon::now()->format('Y-m-d');
    
    // Get the company and branch ID of the authenticated user
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;  // Retrieve the branch ID

    // Fetch the user by ID, company, and branch ID
    $user = Admin_user::where('au_id', $id)
                      ->where('au_company_id', $com)
                      ->where('au_branch_id', $branch_id)  // Add branch filter for the user
                      ->first();

    // Get the duplicate follow-up records for the user on the specified date, filtered by company and branch
    $duplicates = Client_feedback::where('cf_entry_by', $id)
                                 ->where('cf_date', $today)
                                 ->where('cf_company_id', $com)
                                 ->where('cf_branch_id', $branch_id)  // Add branch filter for the feedback
                                 ->groupBy('cf_qb_id')
                                 ->pluck('cf_qb_id')->toArray();
    
    // Fetch the given follow-ups based on duplicates
    $given_fu = Client_feedback::where('cf_entry_by', $id)
                               ->where('cf_date', $today)
                               ->where('cf_company_id', $com)
                               ->where('cf_branch_id', $branch_id)  // Add branch filter for the feedback
                               ->whereIn('cf_qb_id', $duplicates)
                               ->get();

    // Return the view with the duplicates
    return view('pages.user-follow-up-duplicate', compact('given_fu', 'user'));
}

        public function getTopSellingProducts()
{
    $companyId = auth()->user()->au_company_id; // Get the logged-in user's company ID
    $branchId = auth()->user()->au_branch_id; // Get the logged-in user's branch ID

    // Fetch top-selling products
    $topProducts = Inv_product_detail::select(
            'inv_product_details.inv_pro_det_id',
            'inv_product_details.inv_pro_det_pro_id',
            \DB::raw('SUM(inv_product_inventories.inv_pro_inv_qty) as total_sold')
        )
        ->join('inv_product_inventories', 'inv_product_details.inv_pro_det_id', '=', 'inv_product_inventories.inv_pro_inv_project_id')
        ->where('inv_product_inventories.inv_pro_inv_com_id', $companyId)
        ->where('inv_product_inventories.inv_pro_inv_branch_id', $branchId) // Filter by branch ID
        ->where('inv_product_inventories.inv_pro_inv_deal_type', 2) // Sell transactions
        ->where('inv_product_inventories.inv_pro_inv_tran_type', 1) // Confirmed sales
        ->groupBy('inv_product_details.inv_pro_det_id', 'inv_product_details.inv_pro_det_pro_id')
        ->orderByDesc('total_sold')
        ->limit(10) // Get top 10
        ->get();

    // Fetch all products, filtered by company and branch ID
    $allProducts = Inv_product_detail::where('inv_pro_det_com_id', $companyId)
                                      ->where('inv_pro_det_branch_id', $branchId) // Filter by branch ID
                                      ->get();

    return view('page.top_selling_products', compact('topProducts', 'allProducts'));
}

}
