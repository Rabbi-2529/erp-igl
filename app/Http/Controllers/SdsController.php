<?php /** @noinspection ALL */

namespace App\Http\Controllers;

use App\DataTables\Followup_sds_query_bookDataTable;
use App\DataTables\Sds_query_bookDataTable;
use App\DataTables\My_sds_query_bookDataTable;
use App\Exports\TotalCustomerExport;
use App\Exports\TotalCustomerExportWithNames;
use App\Models\Admin_user;
use App\Models\Client_feedback;
use App\Models\EmployeeComission;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_bank;
use App\Models\Inv_customer;
use App\Models\OrderDetail;
use App\Models\Sds_feedback_msg;
use App\Models\FailedSubmission;
use App\Models\Sds_find_us;
use App\Models\Sds_query_book;
use App\Models\Sds_reason;
use App\Models\Sds_reason_category;
use App\Models\Inv_product_inventory;
use App\Models\Sds_reason_com;
use App\Models\Sds_sms;
use App\Models\Sds_temp;
use App\Models\Au_access;
use App\Models\Oms_inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;


class SdsController extends Controller
{
    public function company_register()
    {
        $today = Carbon::now()->format('Y-m-d');

        $companys = Admin_user::where('au_user_type', '4')->whereDate('au_created_date', $today)->get();
        return view('pages.company_register', compact('companys'));
    }

    public function company_register_insert(Request $request)
    {
        
$emailExists = Admin_user::where('au_email', $request->input('au_email'))->exists();
$mobileExists = Admin_user::where('au_mobile', $request->input('au_mobile'))->exists();

if ($emailExists || $mobileExists) {
    $msg = 'The following already exist: ';
    $parts = [];
    if ($emailExists) {
        $parts[] = 'Email';
    }
    if ($mobileExists) {
        $parts[] = 'Mobile Number';
    }
    $msg .= implode(' and ', $parts);

    return redirect()->back()
        ->withInput()
        ->with(['msg' => $msg]);
}

        $pre_max_com_id = Admin_user::where('au_company_id', '!=', '')->max('au_company_id');
        $new_com_id = $pre_max_com_id + 1;

        $table = Input::all();
        $todayD = Carbon::now()->format('Y-m-d');
        $table = new Admin_user;
        $table->au_company_name = Input::get('au_company_name');
        $table->au_company_id = $new_com_id;
        $table->au_name = Input::get('au_name');
        $table->au_email = Input::get('au_email');
        $table->au_mobile = Input::get('au_mobile');
        $table->au_password = md5(Input::get('au_password'));
        $table->au_address = Input::get('au_address');
        $table->au_created_date = $todayD;
        $table->au_status = 1;
        $table->au_user_type = 3;
        if ($request->hasfile('logo')) {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . random_int(10, 99) . "." . $extension;

            $path = $request->logo->storeAs('image', $filename);

            $table->au_company_logo = $filename;
        } else {
            $table->au_company_logo = "";
        }
        if ($request->hasfile('au_company_img')) {
            $file = $request->file('au_company_img');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;

            $path = $request->au_company_img->storeAs('image', $filename);

            $table->au_company_img = $filename;
        } else {
            $table->au_company_img = "";
        }
        $table->save();

        $addCustomer = new Inv_customer;
        $addCustomer->inv_cus_com_id = $new_com_id;
        $addCustomer->inv_cus_name = 'Cash Customer';
        $addCustomer->inv_cus_com_name = 'Cash Customer';
        $addCustomer->inv_cus_mobile = Input::get('au_mobile');
        $addCustomer->inv_cus_email = Input::get('au_email');
        $addCustomer->inv_cus_address = Input::get('au_address');
        $addCustomer->inv_cus_type = 2; //2=cash customer
        $addCustomer->inv_cus_status = 1;
        $addCustomer->inv_cus_submit_by = Auth::user()->au_id;
        $addCustomer->inv_cus_submit_at = Carbon::now();
        $addCustomer->save();

        $cash_bank = Inv_bank::where('status', 0)->orderBy('id', 'desc')->first();
        $addCashBank = new Inv_acc_bank_info;
        $addCashBank->inv_abi_company_id = $new_com_id;
        $addCashBank->inv_abi_bank_id = $cash_bank->id;
        $addCashBank->inv_abi_branch_name = Input::get('au_company_name');
        $addCashBank->inv_abi_account_name = 'Cash Bank';
        $addCashBank->inv_abi_account_no = '123456789';
        $addCashBank->inv_abi_open_date = Carbon::now();
        $addCashBank->inv_abi_account_type = 2; //2=cash
        $addCashBank->inv_abi_status = 1;
        $addCashBank->inv_abi_submit_by = Auth::user()->au_id;
        $addCashBank->inv_abi_submit_at = Carbon::now();
        $addCashBank->save();
        return redirect('/company_register')->with(['msg' => 'Added Successfully']);
    }

    public function admin_list()
    {
        $admins = Admin_user::all()->where('au_user_type', '3');
        return view('pages.admin_list', compact('admins'));
    }

    public function add_reason()
    {
        $reasons = Sds_reason_category::all();
        return view('pages.add_reason', compact('reasons'));
    }

    public function reason_list(Request $request)
    {
        $reason_list = Sds_reason::where('sr_catagory', $request->cat_id)
            ->get();
        return view('pages.ajax.reason_list', compact('reason_list'));
    }

    public function reason_edit(Request $request)
    {
        $reason_edit = Sds_reason::where('sr_slid', $request->id)
            ->first();
        $reason_edit->sr_reason = $request->input('edit');
        $reason_edit->save();
        return redirect('/add_reason')->with(['reason_edit' => 'Edited Successfully.']);
    }

    public function reason_delete($id)
    {
        $reason_delete = Sds_reason::find($id);
        $reason_delete->delete();
        return redirect('/add_reason')->with(['reason_delete' => 'Deleted Successfully']);
    }

    public function add_reason_insert()
    {
        $date = Carbon::now()->format('Y-m-d');
        $table = Input::all();
        $table = new Sds_reason;
        $table->sr_catagory = Input::get('sr_catagory');
        $table->sr_reason = Input::get('sr_reason');
        $table->sr_reg_date = $date;
        $table->sr_status = 1;
        $table->save();

        return redirect('/add_reason')->with(['msg' => 'Reason Added Successfully']);
    }

    public function add_category()
    {
        $cat = Sds_reason_category::all();
        return view('pages.add_category', compact('cat'));
    }

    public function catagory_edit(Request $request)
    {
        $catagory_edit = Sds_reason_category::where('sc_slid', $request->id)
            ->first();
        $catagory_edit->sc_catagory = $request->input('catagory_edit');
        $catagory_edit->save();
        return redirect('/add_category')->with(['catagory_edit' => 'Category Edit Successfully']);
    }

    public function catagory_delete($id)
    {
        $catagory_delete = Sds_reason_category::find($id);
        $catagory_delete->delete();
        return redirect('/add_category')->with(['catagory_delete' => 'Category Deleted Successfully']);
    }

    public function add_category_insert()
    {
        $table = Input::all();

        $table = new Sds_reason_category;
        $table->sc_catagory = Input::get('sc_catagory');
        $table->save();
        return redirect('/add_category')->with(['msg' => 'Category Add Successfully']);
    }

    public function provide_reason()
    {
        $provide_reasons = Admin_user::where('au_user_type', 4)
            ->get();
        $reasons = Sds_reason::all();
        $catagories = Sds_reason_category::all();
        return view('pages.provide_reason', compact('provide_reasons', 'catagories', 'reasons'));
    }

    public function reason_response(Request $request)
    {
        $reasons = Sds_reason_com::where('src_company_id', $request->com_id)
            ->pluck('src_reson_id')
            ->toArray();
        return $reasons;
    }

    public function provide_reason_submit()
    {

        DB::beginTransaction();
        try {
            $create_date = Carbon::now()->format('Y-m-d');
            $reason_idS = Input::get('src_reason_id');
            $company_id = Input::get('src_company_id');

            foreach ($reason_idS as $reason_id) {
                $reason = Sds_reason_com::where('src_company_id', $company_id)->where('src_reson_id', $reason_id)->first();

                if (empty($reason)) {

                    $table = new Sds_reason_com();
                    $table->src_company_id = $company_id;
                    $table->src_reson_id = $reason_id;
                    $table->src_create_date = $create_date;
                    $table->save();
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput();
        }

        DB::commit();
        return redirect()->back()->with(['provide' => 'Provided Selected Reason']);
    }

   public function create_customer()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id; // Get branch_id from the authenticated user
    $user_id = Auth::user()->au_id;
    
    $finds = Sds_find_us::all();
    
    // Filter users based on company_id and branch_id
    $users = Admin_user::where('au_user_type', '!=', '4')
        ->where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id) // Filter by branch_id
        ->get();
    
    $reasons = Sds_reason_category::all();
    $categories = Sds_reason::all();
    $today = Carbon::now()->format('Y-m-d');

    // Filter customers based on company_id, branch_id, and user_id
    $customers = Sds_query_book::where('qb_submit_date', $today)
        ->where('qb_entry_by', $user_id)
        ->where('qb_branch_id', $branch_id) // Filter by branch_id
        ->get();
    
    return view('page.crm.create-customer', compact('finds', 'customers', 'reasons', 'users', 'categories'));
}


    public function create_customer_submit(Request $request)
    {
        // dd($request->all());
        $com = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        $table = Input::all();
        $message =
            [
                'qb_mobile.required' => 'Mobile Number Required',
                'qb_mobile.regex' => 'Invalid Mobile Number',
                'qb_result.required' => 'This Field Must Be Fill Up',
                'qb_staff_id.required' => 'Must be Fill this Field',
                'cus_type.required' => 'This Field Is Required'
            ];
        if (Auth::user()->au_user_type == 4) {
            $request->validate([
                'qb_mobile' => 'required|regex:/01[0-9]\d{8}$/',
                'qb_result' => 'required',
                'qb_staff_id' => 'required',
                'cus_type' => 'required'

            ], $message);
        } else {
            $request->validate([
                'qb_mobile' => 'required|regex:/01[0-9]\d{8}$/',
                'qb_result' => 'required',
                'cus_type' => 'required'
            ], $message);
        }
        // $serial = Sds_query_book::orderBy('qb_serial','desc')->first();
        $serial = Sds_query_book::where('qb_company_id', $com)
        ->where('qb_branch_id',  $branch_id)
            ->orderBy('qb_serial', 'desc')
            ->first();
        if ($serial == null) {
            $serial = Sds_query_book::orderBy('qb_serial', 'desc')->first();
        } else {
            $serial = Sds_query_book::where('qb_company_id', $com)
            ->where('qb_branch_id', $branch_id)
                ->orderBy('qb_serial', 'desc')
                ->first();
        }
        // dd($serial);
        $entry = Auth::user()->au_id;
        $date = Carbon::now()->format('Y-m-d');
        // dd($date);
        try {
            $table = new Sds_query_book;
            $table->qb_mobile = Input::get('qb_mobile');
            $table->qb_entry_by = $entry;
            $table->qb_company_id = $com;
            $table->qb_branch_id = $branch_id;
            $table->qb_submit_date = $date;
            $table->qb_serial = $serial->qb_serial + 1;
            $table->qb_status = 1;
            $table->qb_phone = Input::get('qb_phone');
            $table->qb_name = Input::get('qb_name');
            $table->qb_company_name = Input::get('qb_company_name');
            $table->qb_find_us = Input::get('qb_find_us');
            $table->qb_address = Input::get('qb_address');
            $table->qb_birth_date = Input::get('qb_birth_date');
            $table->qb_marriage_date = Input::get('qb_marriage_date');
            $table->qb_reason = Input::get('qb_reason');
            $table->qb_email = Input::get('qb_email');
            $table->qb_result = Input::get('qb_result');
            $table->qb_cus_type = Input::get('cus_type');
            $table->qb_staff_id = Input::get('qb_staff_id');
            // dd($table);
            $table->save();
        } catch (\Exception $e) {
            return redirect()->back()->with(['err' => $e->getMessage()]);
        }
        return redirect('/create_customer')->with(['msgcustomer' => 'Customer Added Successfully']);
    }

 public function mobile_num_exist(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id; // Add this line - it was missing
    
    // Fix the query - group the orWhere conditions properly
    $exist_no = Sds_query_book::where('qb_company_id', $com)
      
        ->where(function($query) use ($request) {
            $query->where('qb_mobile', $request->qb_mobile)
                ->orWhere('qb_mobile1', $request->qb_mobile)
                ->orWhere('qb_mobile2', $request->qb_mobile);
        })
        ->first();

    if (!empty($exist_no)) {
        // Save to failed submission
        try {
            $failedSubmission = new FailedSubmission(); // Now this will work with the use statement
            $failedSubmission->fs_mobile = $request->qb_mobile;
            $failedSubmission->fs_phone = $request->qb_phone;
            $failedSubmission->fs_name = $request->qb_name;
            $failedSubmission->fs_company_name = $request->qb_company_name;
            $failedSubmission->fs_find_us = $request->qb_find_us;
            $failedSubmission->fs_address = $request->qb_address;
            $failedSubmission->fs_birth_date = $request->qb_birth_date;
            $failedSubmission->fs_marriage_date = $request->qb_marriage_date;
            $failedSubmission->fs_reason = $request->qb_reason;
            $failedSubmission->fs_email = $request->qb_email;
            $failedSubmission->fs_result = $request->qb_result;
            $failedSubmission->fs_cus_type = $request->cus_type;
            $failedSubmission->fs_staff_id = $request->qb_staff_id;
            $failedSubmission->fs_attempted_by = Auth::user()->au_id;
            $failedSubmission->fs_attempted_date = Carbon::now()->format('Y-m-d');
            $failedSubmission->fs_company_id = $com;
            $failedSubmission->fs_branch_id = $branch_id; // Now $branch_id is defined
            $failedSubmission->fs_status = 1;
            $failedSubmission->save();
            
            // Log to see if it saved
            \Log::info('Failed submission saved for mobile: ' . $request->qb_mobile);
            
        } catch (\Exception $e) {
            // Log the error to see what went wrong
            \Log::error('Failed to save submission: ' . $e->getMessage());
        }
        
        return $exist_no->qb_id;
    } else {
        return "false";
    }
}
public function failed_submissions()
{
    $com_id = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    
    // Get all failed submissions for this company and branch
    $failedSubmissions = FailedSubmission::where('fs_company_id', $com_id)
        ->where('fs_branch_id', $branch_id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Debug: Check what data we have
    \Log::info('Failed Submissions Data', [
        'count' => $failedSubmissions->count(),
        'first_record' => $failedSubmissions->first()
    ]);
    
    return view('page.crm.failed-submissions', compact('failedSubmissions'));
}
public function searchCustomersByMobile(Request $request)
{
    try {
        $mobile = $request->mobile;
        $company_id = Auth::user()->au_company_id;
        
        \Log::info('Searching customers by mobile', [
            'mobile' => $mobile,
            'company_id' => $company_id
        ]);
        
        if (!$mobile) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number is required.'
            ]);
        }
        
        // Search for customers matching the mobile number in any of the mobile fields
        $customers = Sds_query_book::with(['find_info', 'reas_info', 'entry_info', 'entry_staff'])
                    ->where('qb_company_id', $company_id)
                    ->where(function($query) use ($mobile) {
                        $query->where('qb_mobile', $mobile)
                              ->orWhere('qb_mobile1', $mobile)
                              ->orWhere('qb_mobile2', $mobile);
                    })
                    ->orderBy('qb_submit_date', 'desc')
                    ->get();
        
        \Log::info('Search results', [
            'count' => $customers->count(),
            'mobile' => $mobile
        ]);
        
        if ($customers->count() > 0) {
            return response()->json([
                'success' => true,
                'data' => $customers,
                'count' => $customers->count(),
                'mobile' => $mobile
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "No customer records found with mobile number: {$mobile}"
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error in searchCustomersByMobile', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
}



    public function show_reason_by_ajax(Request $request)
    {
        $reasons = Sds_reason::where('sr_catagory', $request->cat_id)->get();
        return view('pages.ajax.show_reasons_by_category', compact('reasons'));
    }

    public function customer_list(my_sds_query_bookDataTable $datatable)
    {
        return $datatable->render('page.crm.manage-customer');
    }
    public function user_customer_list($id)
    {
        // Get the company ID of the authenticated user
        $com_id = Auth::user()->au_company_id;
               $branch_id= Auth::user()->au_branch_id;
        // Get the user ID from the parameter
        $user_id = $id;
    
        // Fetch customers filtered by user ID and company ID
        $my_customers = Sds_query_book::where('qb_entry_by', $user_id)
            ->where('qb_company_id', $com_id)
                ->where('qb_branch_id', $branch_id)
            ->get();
    
        // Fetch reasons and map them by their IDs
        $reasons = Sds_reason::pluck('sr_reason', 'sr_slid')->all();
    
        // Fetch findus data and map them by their IDs
        $find_us_options = Sds_find_us::pluck('sf_howto', 'sf_slid')->all();
    
        // Fetch user names associated with qb_entry_by
        $entry_by_users = Admin_user::whereIn('au_user_id', $my_customers->pluck('qb_entry_by')->unique())->pluck('au_name', 'au_user_id');
        
        $user_name = Admin_user::where('au_id', $user_id)->value('au_name');
        // Ensure the user_name is being fetched

        if (!$user_name) {
            $user_name = 'Unknown User';
        }
        return view('page.hrm.manage-customer-user', compact('my_customers', 'reasons', 'find_us_options', 'entry_by_users','user_name'));
    }

    public function client_feedback(Request $request, $id)
    {
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $customer = Sds_query_book::find($id);
        $reason = Sds_reason::all();
        $comEmp = Admin_user::where('au_company_id', $com_id)->get();
        $branchEmp = Admin_user::where('au_branch_id', $branch_id)->get();
        $client_feedbacks = Sds_feedback_msg::all();
        $sms_temps = Sds_temp::where('st_com_id', $com_id)->get();
        $feedbacks = Client_feedback::where('cf_qb_id', $customer->qb_id)->where('cf_company_id', $com_id)->where('cf_branch_id', $branch_id)->get();

        $isUserEntry = Auth::user()->au_id == $customer->qb_entry_by || Auth::user()->au_user_type == 4;
        
        $oms_inventories = Oms_inventory::where('oms_what_for', $customer->qb_id)->get();
        $total_debit_convence = Oms_inventory::where('oms_what_for', $customer->qb_id)
        ->where('oms_status', 4)
        ->sum('oms_debit');
        $total_debit_lunch = Oms_inventory::where('oms_what_for', $customer->qb_id)
        ->where('oms_status', 6)
        ->sum('oms_debit');

        $convence_entries = Oms_inventory::where('oms_what_for', $customer->qb_id)
                                        ->where('oms_status', 4) // conveyance
                                        ->get();

        $lunch_entries = Oms_inventory::where('oms_what_for', $customer->qb_id)
                                    ->where('oms_status', 6) // lunch
                                    ->get();


        return view('page.crm.client_feedback', compact('customer','convence_entries','lunch_entries','oms_inventories','total_debit_convence','total_debit_lunch', 'reason', 'feedbacks', 'client_feedbacks', 'sms_temps', 'comEmp', 'isUserEntry'));
    }

    public function client_feedback_oms(Request $request, $id)
    {
        $customer = Sds_query_book::find($id);
        $client_feedbacks = Sds_feedback_msg::all();
        $com_id = Auth::user()->au_company_id;
            $branch_id = Auth::user()->au_branch_id;
        $sms_temps = Sds_temp::where('st_com_id', $com_id)
        ->where('st_branch_id', $branch_id)
            ->get();
        $feedbacks = Client_feedback::where('cf_qb_id', $customer->qb_id)
        ->where('cf_branch_id', $branch_id)
            ->where('cf_company_id', $com_id)
            ->get();

        return view('pages.client_feedback', compact('customer', 'feedbacks', 'client_feedbacks', 'sms_temps'));
    }

    public function smsNotify(Request $request, $id)
    {
        $sms = Sds_sms::where('sms_cf_id', $id)
            ->first();
        return view('pages.ajax.sms_notify_partial_table', compact('sms'));
    }

    public function send_notification(Request $request, $id)
    {
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $client = Sds_query_book::where('qb_id', $id)->first();

        if (empty($client)) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Customer !');

            return redirect()->back();
        }

        $api_sender = Admin_user::where('au_company_id', $com_id)
        ->where('au_branch_id', $branch_id)
            ->where('au_user_type', 4)
            ->first();

        $query = Client_feedback::where('cf_qb_id', $client->qb_id)
            ->orderBy('cf_id', 'DESC')
            ->first();
        if (empty($query)) {
            session()->flash('followup', 'Provide Follow Up First!');
            return redirect()->back();
        }

        $table = Input::all();
        $sent = Carbon::now()->format('Y-m-d H:i:s');
        $table = new Sds_sms;

        $table->sms_mobileno1 = Input::get('sms_mobileno1');
        $table->sms_mobileno2 = Input::get('sms_mobileno2');
        $table->sms_mobileno3 = Input::get('sms_mobileno3');
        $table->sms_text = Input::get('sms_text');
        $table->sms_cf_id = $query->cf_id;
        $table->sms_cf_next_date = $query->cf_next_date;
        $table->sms_sent_at = $sent;
        $table->sms_status = 1;


        $mobile_number1 = Input::get('sms_mobileno1');
        $mobile_number2 = Input::get('sms_mobileno2');
        $mobile_number3 = Input::get('sms_mobileno3');
        $mobile_number4 = Input::get('sms_mobileno4');

        $message = Input::get('sms_text');
        $message = urlencode($message);
        $api_key = $api_sender->au_api_key;
        $sender_id = $api_sender->au_sender_id;
        $client = new \GuzzleHttp\Client();
        $api_url = "http://sms.iglweb.com/api/v1/send?api_key=" . $api_key . "&contacts=" . $mobile_number1 . "," . $mobile_number2 . "," . $mobile_number3 . "," . $mobile_number4 . "&senderid=" . $sender_id . "&msg=" . $message;
        $response = $client->request('GET', "$api_url");

        $json_response = $response->getBody()->getContents();
        $api_response = json_decode($json_response);

        if ($api_response->code == "445000") {
            $type = 'success';
            $table->save();
            $msg = "SMS Sending Successfully!";
        } else if ($api_response->code == "445040") {
            $type = 'danger';
            $msg = "SMS Sending failed because of invalid API key";
        } else if ($api_response->code == "445080") {
            $type = 'danger';
            $msg = "SMS Sending failed because of invalid Sender ID";
        } else if ($api_response->code == "445120") {
            $type = 'danger';
            $msg = "SMS Sending failed because of your sms balance is low";
        } else if ($api_response->code == "445110") {
            $type = 'danger';
            $msg = "SMS Sending failed because of Client number are invalid";
        } else {
            $type = "danger";
            $msg = "SMS Sending failed because of " . $api_response->message;
        }
        session()->flash('type', $type);
        session()->flash('message', $msg);

        return redirect()->back();
    }

    public function visit_notify(Request $request, $id)
    {
        $com_id = Auth::user()->au_company_id;
        $client = Sds_query_book::where('qb_id', $id)->first();

        if (empty($client)) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Customer !');

            return redirect()->back();
        }

        $query = Client_feedback::where('cf_qb_id', $client->qb_id)
            ->orderBy('cf_id', 'DESC')
            ->first();
        if (empty($query)) {
            session()->flash('followup', 'Provide Follow Up First!');
            return redirect()->back();
        }

        $table = Input::all();
        $sent = Carbon::now()->format('Y-m-d H:i:s');
        $table = new Sds_sms;

        $table->sms_mobileno1 = Input::get('visit');
        $table->sms_text = Input::get('visit_text');
        $table->sms_cf_id = $query->cf_id;
        $table->sms_cf_next_date = $query->cf_next_date;
        $table->sms_sent_at = $sent;
        $table->sms_status = 2;
        $table->save();
        return redirect()->back();
    }

public function client_feedback_submit(Request $request, $id)
{
    $entry_id = Auth::user()->au_id;
    $previous = Auth::user()->au_name;
        
    $message = [
        'cf_client_feedback.required' => 'This Field must be required',
        'cf_result.required' => 'This field must required',
    ];

    $request->validate([
        'cf_client_feedback' => 'required',
        'cf_result' => 'required',
    ], $message);

    $cf_date = Carbon::now()->format('Y-m-d H-i-s');
    $cf_com_id = Auth::user()->au_company_id;
    $cf_branch_id = Auth::user()->au_branch_id;

    /* ===== FIX 1: DO NOT use find($id) (no id column) ===== */
    $customer = Sds_query_book::where('qb_id', $id)->first();

    if (!$customer) {
        return redirect()->back()->with('error', 'Client record not found.');
    }
    /* ===== END FIX 1 ===== */

    $query_update = Sds_query_book::where('qb_company_id', $cf_com_id)
        ->where('qb_branch_id', $cf_branch_id)
        ->where('qb_id', $id)
        ->first();

    /* ===== FIX 2: prevent null qb_feedback update ===== */
    if ($query_update) {
        $query_update->qb_feedback = $request->input('cf_client_feedback');
    }
    /* ===== END FIX 2 ===== */

    $convertedDate = null;

    if ($request->has('cf_next_date') && $request->input('cf_next_date')) {
        $convertedDate = \Carbon\Carbon::createFromFormat(
            'd/m/Y',
            $request->input('cf_next_date')
        )->format('Y-m-d');
    } else {
        $convertedDate = "";
    }

    $table = new Client_feedback;
    $table->cf_call_duration = $request->input('cf_call_duration');
    $table->cf_entry_by = $entry_id;
    $table->cf_previous_followup = $previous;
    $table->cf_company_id = $cf_com_id;
    $table->cf_branch_id = $cf_branch_id;
    $table->cf_result = $request->input('cf_result');
    $table->cf_connect_us = $request->input('cf_connect_us');
    $table->cf_qb_id = $customer->qb_id;
    $table->cf_client_feedback = $request->input('cf_client_feedback');
    $table->cf_next_date = $convertedDate;
    $table->cf_price = $request->input('cf_price');
    $table->cf_client_message = $request->input('cf_client_message');
    $table->cf_date = $cf_date;
    $table->status = 0;

    if ($request->input('cf_client_feedback') == 6) {

        $message = [
            'cf_price.required' => 'Price is required for Ordered product'
        ];

        $request->validate([
            'cf_price' => 'required'
        ], $message);

        $order = new OrderDetail();
        $order->company = $cf_com_id;
        $order->branch_id = $cf_branch_id;
        $order->user_id = $entry_id;
        $order->order_client = $customer->qb_id;
        $order->description = $request->input('cf_client_message');
        $order->order_price_credit = $request->input('cf_price');
        $order->save();
    }

    /* ===== FIX 3: save only if exists ===== */
    if ($query_update) {
        $query_update->save();
    }
    /* ===== END FIX 3 ===== */

    $table->save();

    return redirect('/client_feedback/' . $customer->qb_id)
        ->with(['msg_cf' => 'Client Feedback Updated']);
}


    public function client_feedback_edit($id)
    {
        $customer_edit = Sds_query_book::find($id);
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $com_users = Admin_user::where('au_user_type', '!=', '4')->where('au_company_id', $com_id)->where('au_branch_id', $branch_id)->get();
        return view('page.crm.client_feedback_edit', compact('customer_edit', 'com_users'));
    }

   
    public function client_feedback_edit_update(Request $request, $id)
    {
        // Custom error messages for validation
        $messages = [
            'qb_mobile.unique' => 'The mobile number already exists.',
            'qb_mobile1.unique' => 'The second mobile number already exists.',
            'qb_mobile2.unique' => 'The third mobile number already exists.',
            'qb_mobile1.different' => 'The second mobile number must be different from the first mobile number.',
            'qb_mobile2.different' => 'The third mobile number must be different from the first and second mobile numbers.',
        ];
    
        // Validate the phone numbers to ensure they are unique and not the same
        $request->validate([
            'qb_mobile' => 'required|numeric|unique:sds_query_book,qb_mobile,' . $id . ',qb_id',
            'qb_mobile1' => 'nullable|numeric|unique:sds_query_book,qb_mobile1,' . $id . ',qb_id|different:qb_mobile',
            'qb_mobile2' => 'nullable|numeric|unique:sds_query_book,qb_mobile2,' . $id . ',qb_id|different:qb_mobile|different:qb_mobile1',
        ], $messages); // Apply the custom messages
    
        // Fetch the existing customer record
        $customer_edit = Sds_query_book::find($id);
    
        if (!$customer_edit) {
            return redirect()->back()->with('error', 'Customer not found.');
        }
    
        try {
            // Update customer details
            $customer_edit->qb_address = $request->input('qb_address');
            $customer_edit->qb_name = $request->input('qb_name');
            $customer_edit->qb_email = $request->input('qb_email');
            $customer_edit->qb_mobile1 = $request->input('qb_mobile1');
            $customer_edit->qb_mobile2 = $request->input('qb_mobile2');
    
            // Handle entry by for authorized users (user type 4)
            if (Auth::user()->au_user_type == 4) {
                $qb_entry_by = $request->input('qb_entry_by');
                if ($qb_entry_by !== null) {
                    $customer_edit->qb_entry_by = $qb_entry_by;
                }
            } else {
                // Preserve the previous value if the user is not authorized
                $customer_edit->qb_entry_by = $customer_edit->qb_entry_by;
            }
    
            // Handle birth and marriage date input, ensuring correct formatting
            $qb_birth = $request->input('birth') 
                ? date('Y-m-d', strtotime(str_replace('/', '-', $request->input('birth')))) 
                : null;
    
            $qb_marriage = $request->input('marriage') 
                ? date('Y-m-d', strtotime(str_replace('/', '-', $request->input('marriage')))) 
                : null;
    
            $customer_edit->qb_birth_date = $qb_birth;
            $customer_edit->qb_marriage_date = $qb_marriage;
    
            // Update other fields
            $customer_edit->qb_company_name = $request->input('company');
            $customer_edit->qb_branch_id = Auth::user()->au_branch_id;
            $customer_edit->qb_cus_type = $request->input('cus_type');
    
            // Save the updated customer details
            $customer_edit->save();
    
            // Flash success message
            return redirect()->route('pages.client_feedback', ['id' => $id])
                ->with('success', 'Customer details updated successfully.');
    
        } catch (\Exception $e) {
            // Flash error message if there is an exception
            return redirect()->back()->with('error', 'Error updating customer details: ' . $e->getMessage());
        }
    }
    

    public function checkMobileNumberUnique(Request $request)
    {
        // Validate the inputs
        $request->validate([
            'mobile_number' => 'nullable|numeric',
            'field_name' => 'required|string|in:qb_mobile,qb_mobile1,qb_mobile2',
            'id' => 'nullable|numeric',
        ]);
    
        // Get the mobile number, field name, and exclude id (if any)
        $mobileNumber = $request->input('mobile_number');
        $fieldName = $request->input('field_name');
        $excludeId = $request->input('id'); // Optional: Exclude a specific record
    
        // Get all three mobile numbers from the request
        $qbMobile = $request->input('qb_mobile');
        $qbMobile1 = $request->input('qb_mobile1');
        $qbMobile2 = $request->input('qb_mobile2');
    
        // Ensure that no two or more mobile numbers are the same
        if (($qbMobile && $qbMobile1 && $qbMobile === $qbMobile1) ||
            ($qbMobile && $qbMobile2 && $qbMobile === $qbMobile2) ||
            ($qbMobile1 && $qbMobile2 && $qbMobile1 === $qbMobile2)) {
            return response()->json([
                'exists' => true,
                'message' => 'The mobile numbers cannot be the same.',
            ]);
        }
    
        // Check if the mobile number exists for qb_mobile
        if ($fieldName == 'qb_mobile') {
            $exists = Sds_query_book::where('qb_mobile', $mobileNumber)
                ->when($excludeId, function ($query, $excludeId) {
                    return $query->where('qb_id', '!=', $excludeId);
                })
                ->exists();
    
            if ($exists) {
                return response()->json([
                    'exists' => true,
                    'message' => 'The mobile number already exists.',
                ]);
            }
        }
    
        // Check if the mobile number exists for qb_mobile1
        if ($fieldName == 'qb_mobile1') {
            $exists = Sds_query_book::where('qb_mobile1', $mobileNumber)
                ->when($excludeId, function ($query, $excludeId) {
                    return $query->where('qb_id', '!=', $excludeId);
                })
                ->exists();
    
            if ($exists) {
                return response()->json([
                    'exists' => true,
                    'message' => 'The second mobile number already exists.',
                ]);
            }
        }
    
        // Check if the mobile number exists for qb_mobile2
        if ($fieldName == 'qb_mobile2') {
            $exists = Sds_query_book::where('qb_mobile2', $mobileNumber)
                ->when($excludeId, function ($query, $excludeId) {
                    return $query->where('qb_id', '!=', $excludeId);
                })
                ->exists();
    
            if ($exists) {
                return response()->json([
                    'exists' => true,
                    'message' => 'The third mobile number already exists.',
                ]);
            }
        }
    
        return response()->json([
            'exists' => false,
            'message' => 'The mobile number is unique.',
        ]);
    }
    public function total_customer_list(Sds_query_bookDataTable $datatable)
    {

        $com_id = Auth::user()->au_company_id;



        return $datatable->render('page.crm.total-customer');

    }

    public function total_customer_list_new(Sds_query_bookDataTable $datatable)
    {

        return $datatable->render('page.crm.total-customer');

    }
    

    public function get_Totallcustomer_data(Request $request)
    {
        $fields = $request->input('fields');

        if ($fields === 'mobile_name') {
            return Excel::download(new TotalCustomerExportWithNames, 'Sds_query_book.xlsx');
        } else {
            return Excel::download(new TotalCustomerExport, 'Sds_query_book.xlsx');
        }
    }



    public function admin_created(Request $request, $id)
    {
        $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $admin_c = Admin_user::where('au_user_type', 4)
            ->where('au_company_id', $com)
              ->where('au_branch_id', $branch_id)
            ->first();
        $admin = Sds_query_book::where('qb_entry_by', $admin_c->au_id)
            ->where('qb_company_id', $com)
                   ->where('qb_branch_id', $branch_id)
            ->where('qb_staff_id', $id)
            ->get();
        return view('page.crm.admin-create', compact('admin'));
    }
    
    public function order_customer()
{
    $user      = Auth::user();
    $authId    = $user->au_id;
    $com_id    = $user->au_company_id; 
    $branch_id = $user->au_branch_id;
    $userType  = $user->au_user_type;

    // Convert permission string to array
    $permissions = explode('-', $user->au_permission_status);

    // Fetch admins (branch users)
    $admins = Admin_user::where('au_company_id', $com_id)
                        ->where('au_branch_id', $branch_id)
                        ->get();


    if (in_array('104', $permissions)) {

        $orderCustomers = Client_feedback::where('cf_client_feedback', 6)
                                ->where('cf_company_id', $com_id)
                                ->with(['orderInfo', 'feedbackinfo', 'cus_mobile', 'feedback_info'])
                                ->orderBy('status', 'asc')
                                ->get();

    } elseif (in_array('102', $permissions) || $userType == 4) {

        $orderCustomers = Client_feedback::where('cf_client_feedback', 6)
                                ->where('cf_company_id', $com_id)
                                ->where('cf_branch_id', $branch_id)
                                ->whereIn('cf_entry_by', $admins->pluck('au_id'))
                                ->with(['orderInfo', 'feedbackinfo', 'cus_mobile', 'feedback_info'])
                                ->orderBy('status', 'asc')
                                ->get();


    } else {

        $orderCustomers = Client_feedback::where('cf_client_feedback', 6)
                                ->where('cf_company_id', $com_id)
                                ->where('cf_branch_id', $branch_id)
                                ->where('cf_entry_by', $authId)
                                ->with(['orderInfo', 'feedbackinfo', 'cus_mobile', 'feedback_info'])
                                ->orderBy('status', 'asc')
                                ->get();
    }

    return view('page.crm.ordered-customer', compact('orderCustomers', 'admins'));
}
    
    public function updateOrder(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:client_feedback,cf_id',
            'cf_price' => 'required|numeric',
            'cf_next_date' => 'required|date',
            'cf_call_duration' => 'required|string',
        ]);
    
        $customerId = $request->customer_id;
        $entry_id = Auth::user()->au_id;
        $cf_com_id = Auth::user()->au_company_id;
        $cf_branch_id = Auth::user()->au_branch_id;
        // Find existing Client Feedback
        $feedback = Client_feedback::find($customerId);
        if (!$feedback) {
            return response()->json(['success' => false, 'message' => 'Client feedback not found']);
        }
    
        // Update Client Feedback
        $feedback->cf_next_date = \Carbon\Carbon::createFromFormat('Y-m-d', $request->cf_next_date)->format('Y-m-d');
        $feedback->cf_price = $request->cf_price;
        $feedback->cf_call_duration = $request->cf_call_duration;
        $feedback->status = 1; // Mark as confirmed
        $feedback->save();
    
        // Update existing order details
        $order = OrderDetail::where('order_client', $feedback->cf_qb_id)
                            ->where('company', $cf_com_id)
                            ->where('branch_id', $cf_branch_id)
                            ->first();
    
        if ($order) {
            $order->order_price_credit = $request->cf_price;
            $order->description = 'Order Confirmed';
            $order->save();
        }
    
        return response()->json(['success' => true]);
    }
    

    public function orderUpdateForm(Request $request)
    {
        // dd('a');
        $customer = Sds_query_book::where('qb_company_id', Auth::user()->au_company_id)->where('qb_branch_id', Auth::user()->au_branch_id)->get();
        $order = OrderDetail::where('company', Auth::user()->au_company_id)
        ->where('branch_id', Auth::user()->au_branch_id)
            ->where('id', $request->orderId)
            ->first();
        return view('pages.ajax.order-detail', compact('order', 'customer'));
    }

    public function updateOrderSubmit(Request $request)
    {
        $order = new OrderDetail();
        $order->company = Auth::user()->au_company_id;
           $order->branch_id = Auth::user()->au_branch_id;
        $order->user_id = Auth::user()->au_id;
        $order->order_client = $request->client;
        $order->description = $request->desc;
        if ($request->addRemove == 1) {
            $order->order_price_credit = $request->add;
        } elseif ($request->addRemove == 2) {
            $order->order_price_debit = $request->remove;
        }
        $order->save();

        return redirect()->back()->with(['success' => 'Order Updated Sucessfully']);
    }

    public function visited_customer()
    {
        return view('pages.visited_customer');
    }

    public function members_list($id)
    {
        $company_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $members = Admin_user::where('au_team_id', $id)
            ->where('au_company_id', $company_id)
                        ->where('au_branch_id', $branch_id)
            ->where('au_user_type', 6)
            ->get();
        return view('pages.members_list', compact('members'));
    }

    public function followup(Followup_sds_query_bookDataTable $datatable)
    {
        return $datatable->render('page.crm.followup.follow-up');
    }

    public function non_followup()
    {
        $company = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $entry = Auth::user()->au_id;
        $team_id = Auth::user()->au_team_id;

        $all_cf_qb_id = Client_feedback::where('cf_company_id', $company)
        ->where('cf_branch_id', $branch_id)
            ->where('cf_entry_by', $entry)
            ->groupBy('cf_qb_id')
            ->pluck('cf_qb_id');
        $team_m = array();
        $user = Auth::user()->au_id;
        $team = Admin_user::where('au_company_id', $company)
            ->where('au_branch_id', $branch_id)
            ->where('au_team_id', $team_id)
            ->first();
        $team_m[] = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $company) as $member) {
            $team_m[] = $member->au_id;
        }
        if (Auth::user()->au_user_type == 5) {
            $non_followed_cus = Sds_query_book::where('qb_company_id', $company)
              ->where('qb_branch_id', $branch_id)
                ->whereIn('qb_entry_by', $team_m)
                ->whereNotIn('qb_id', $all_cf_qb_id)
                ->get();

        } elseif (Auth::user()->au_user_type == 6) {
            $non_followed_cus = Sds_query_book::where('qb_company_id', $company)
              ->where('qb_branch_id', $branch_id)
                ->where('qb_entry_by', $entry)
                ->whereNotIn('qb_id', $all_cf_qb_id)
                ->get();
        }

        return view('page.crm.followup.non-followup', compact('non_followed_cus'));
    }

       public function search_findus(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $userAcces = Auth::user()->au_user_type;
        $team = Auth::user()->au_team_id;
    
        // Fetch the "Find Us" options
        $finds = Sds_find_us::all();
    
        // Initialize the $users variable
        $users = collect(); // Use a collection to store users
    
        // If user type is 4 (admin), fetch all users in the same company
        if ($userAcces == 4) {
            $users = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->get();
        } else {
            // Otherwise, show only the logged-in user and team members
            $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $branch_id)
                ->where(function ($query) use ($team) {
                    $query->where('au_team_id', $team)
                          ->orWhere('au_id', Auth::user()->au_id);
                })
                ->get();
        }
    
        $find_users = [];
    
        if ($request->search_findus == 'Search') {
            // Date range filtering
            $start_date = $request->from ?: '2000-01-01';
            $end_date = $request->to ?: Carbon::now()->format('Y-m-d');
    
            // Collect user IDs for filtering data
            $user_ids = [];
    
            // Check if a specific user is selected from the dropdown
            if ($request->user != '') {
                $user_ids[] = $request->user; // Use selected user ID
            } else {
                if ($userAcces == 4) {
                    // Admin can search for all users
                     $user_ids = Admin_user::where('au_company_id', $com)
                                  ->where('au_branch_id', $branch_id)
                                  ->pluck('au_id')
                                  ->toArray();
                } else {
                    // Regular users can search only their own data
                    $user_ids[] = Auth::user()->au_id; // Always include the logged-in user
                    // No need to include team members since we're limiting to their own data
                }
            }
    
            // Fetch query results based on find_us filter
            $query = Sds_query_book::whereBetween('qb_submit_date', [$start_date, $end_date]);
    
            // Ensure non-admin users only see their own data
            if ($userAcces != 4) {
                // Non-admin users should only see their entries
                $query->where('qb_entry_by', Auth::user()->au_id);
            } else {
                // Admin users can see all users' entries
                $query->whereIn('qb_entry_by', $user_ids);
            }
    
            // Apply the "Find Us" filter if provided
            if ($request->feedback != '') {
                $find_users = $query->where('qb_find_us', $request->feedback)->get();
            } else {
                // Fetch results without the "Find Us" filter
                $find_users = $query->get();
            }
        }
    
        return view('page.crm.isearch.search-by-find', compact('finds', 'users', 'find_users'));
    }
    

    public function search_reason(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $team = Auth::user()->au_team_id;
        $userAcces = Auth::user()->au_user_type;
    
        // Fetch the reasons list
        $reasons = Sds_reason::all();
    
        // Fetch the list of users based on user type
        if ($userAcces == 4) {
            // User type 4: Fetch all users in the company
            $users = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->get();
        } else {
            // Other users: Fetch only the logged-in user and their team members
            $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $branch_id)
                ->where(function ($query) {
                    $query->where('au_team_id', Auth::user()->au_team_id)
                          ->orWhere('au_id', Auth::user()->au_id);
                })
                ->get();
        }
    
        // Initialize the find_users array
        $find_users = [];
    
        // If the search button is clicked
        if ($request->search_reason == 'Search') {
            // Handle the date filtering
            $start_date = $request->from ?: '2000-01-01';
            $end_date = $request->to ?: Carbon::now()->format('Y-m-d');
    
            // Prepare user IDs for filtering results
            $user_ids = [];
    
            // If a specific user is selected, filter by their ID
            if (!empty($request->user)) {
                $user_ids[] = $request->user;
            } else {
                if ($userAcces == 4) {
                        $user_ids = Admin_user::where('au_company_id', $com)
                                  ->where('au_branch_id', $branch_id)
                                  ->pluck('au_id')
                                  ->toArray();
              
                } else {
                    // Other users: Include only their own data
                    $user_ids[] = Auth::user()->au_id; // Always include the logged-in user
                }
            }
    
            // Fetch results based on reason and date filters
            $query = Sds_query_book::whereBetween('qb_submit_date', [$start_date, $end_date]);
    
            // Filter based on user IDs only if they are not admin
            if ($userAcces != 4) {
                // Ensure non-admin users can only see their own data
                $query->where('qb_entry_by', Auth::user()->au_id);
            } else {
                // Admins can view data of all users
                $query->whereIn('qb_entry_by', $user_ids);
            }
    
            if (!empty($request->reason)) {
                // If a reason is selected, filter by reason
                $query->where('qb_reason', $request->reason);
            }
    
            // Get the filtered results
            $find_users = $query->get();
        }
    
        return view('page.crm.isearch.search-by-reason', compact('reasons', 'users', 'find_users'));
    }
    
    
   public function search_result(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branch_id = Auth::user()->au_branch_id;
    $team = Auth::user()->au_team_id;
    $userAcces = Auth::user()->au_user_type;

    // Retrieve users based on access level
    if ($userAcces == "4") {
        // If user type is 4, get all users from the same company
        $users = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->get();
    } else {
        // For other user types, restrict to users in the same team
        $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $branch_id)
            ->where('au_team_id', $team)
            ->get();
    }

    $find_users = [];

    // Perform search if search_result parameter is set to 'Search'
    if ($request->search_result == 'Search') {
        // Set date range
        $start_date = $request->from ?: '2000-01-01'; // Default to an early date if not provided
        $end_date = $request->to ?: Carbon::now()->format('Y-m-d'); // Default to today if not provided

        // Determine user IDs for filtering
        $user_ids = [];

        // If a specific user is selected, add that user ID to the array
        if ($request->user != '') {
            $user_ids[] = $request->user; // Use the selected user ID
        } else {
            // If user type is 4, fetch all user IDs from the company
            if ($userAcces == "4") {
               $user_ids = Admin_user::where('au_company_id', $com)->pluck('au_id')->toArray();
            } else {
                // For other user types, add only their own user ID
                $user_ids[] = Auth::user()->au_id; // Always include the logged-in user's ID
            }
        }

        // Build the query to fetch results
        $query = Sds_query_book::whereBetween('qb_submit_date', [$start_date, $end_date]);

        // Ensure non-admin users can only see their own data
        if ($userAcces != "4") {
            // Non-admin users should only see their entries
            $query->where('qb_entry_by', Auth::user()->au_id);
        } else {
            // Admin users can see all users' entries
            $query->whereIn('qb_entry_by', $user_ids);
        }

        // If a specific result type is selected, add it to the query
        if ($request->result != '') {
            $query->where('qb_result', $request->result);
        }

        // Execute the query and get results
        $find_users = $query->get();
    }

    return view('page.crm.isearch.search-by-result', compact('users', 'find_users'));
}

    
    
    public function search_feedback(Request $request)
    {
        $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $team = Auth::user()->au_team_id;
        $userAcces = Auth::user()->au_user_type;
    
        // Fetch all feedback messages, if needed.
        $feedbacks = Sds_feedback_msg::all();
    
        // Adjusted logic to check for user type.
        if ($userAcces == "4") {
            // User type 4 can see all users in their company.
            $users = Admin_user::where('au_company_id', $com)->where('au_branch_id', $branch_id)->get();
        } else {
            // Other user types can only see their own team members.
            $users = Admin_user::where('au_company_id', $com)
            ->where('au_branch_id', $com)
                ->where('au_team_id', $team)
                ->get();
        }
    
        $find_users = []; // Initialize an array for found users.
    
        // If the search button is clicked
        if ($request->search_feedback === 'Search') {
            // Set date range.
            $start_date = $request->from ?: '2000-01-01'; // Default start date.
            $end_date = $request->to ?: Carbon::now()->format('Y-m-d'); // Default end date.
    
            // Prepare user IDs array based on access level.
            $user_ids = [];
    
            // Use the selected user from the dropdown if provided
            if ($request->user) {
                $user_ids[] = $request->user;
            } else {
                // If user type is 4, they can see all users.
                if ($userAcces == "4") {
                $user_ids = Admin_user::where('au_company_id', $com)->pluck('au_id')->toArray();

                } else {
                    // Otherwise, get the authenticated user and only their entries.
                    $user_ids[] = Auth::user()->au_id;
                }
            }
    
            // Perform the query based on the selected feedback and dates.
            $query = Sds_query_book::whereBetween('qb_submit_date', [$start_date, $end_date])
                ->whereIn('qb_entry_by', $user_ids);
    
            // Check if specific feedback is provided
            if ($request->feedback) {
                $query->where('qb_feedback', $request->feedback);
            }
    
            // Execute the query and get results
            $find_users = $query->get();
        }
        
        // Return the view with the necessary data.
        return view('page.crm.isearch.search-by-feedback', compact('feedbacks', 'users', 'find_users'));
    }
    
    public function todays_customer_list()
    {
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $user_customers = Admin_user::where('au_user_type', '!=', '4')->where('au_company_id', $com_id)->where('au_branch_id', $branch_id)->get();
        return view('page.crm.todays_customer_list', compact('user_customers'));
    }

    public function customer_list_monthly($id)
    {
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $todayDate = Carbon::now()->format('Y-m-d');
        $monthly = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthly_cus = Sds_query_book::where('qb_company_id', $com_id)
        ->where('qb_branch_id', $branch_id)
            ->where('qb_entry_by', $id)
            ->where('qb_submit_date', '<=', $todayDate)
            ->where('qb_submit_date', '>=', $monthly)
            ->get();
        return view('pages.customer_list_monthly', compact('monthly_cus'));
    }

    public function customer_list_today($id)
    {
        $com_id = Auth::user()->au_company_id;
        $today = Carbon::now()->format('Y-m-d');
        $today_cus = Sds_query_book::where('qb_company_id', $com_id)
            ->where('qb_entry_by', $id)
            ->where('qb_submit_date', $today)
            ->get();
        return view('pages.customer_list_today', compact('today_cus'));
    }

    public function find_us()
    {
        $find_us = Sds_find_us::where('sf_status', 1)->get();
        return view('pages.find_us', compact('find_us'));
    }

    public function find_us_edit(Request $request)
    {
        $find_edit = Sds_find_us::where('sf_slid', $request->id)->first();
        $find_edit->sf_howto = $request->input('how');
        $find_edit->save();
        return redirect('/find_us')->with(['find_edit' => 'Find Us Edited Successfully']);
    }

    public function find_up_delete(Request $request, $id)
    {
        $find_up_del = Sds_find_us::where('sf_slid', $id)->first();
        $find_up_del->sf_status = 0;
        $find_up_del->save();
        return redirect('/find_us')->with(['find_up_del' => 'Find Us Deleted Successfully']);
    }

    public function find_us_submit()
    {
        $table = Input::all();
        $table = new Sds_find_us;
        $date = Carbon::now()->format('Y-m-d');
        $table->sf_howto = Input::get('sf_howto');
        $table->sf_create_date = $date;
        $table->sf_status = 1;
        $table->save();
        return redirect('/find_us')->with(['msgfind' => 'Find us Added Successfully']);
    }

    public function weekly_summery(Request $request)
    {
        $com_id = Auth::user()->au_company_id;
       $branch_id = Auth::user()->au_branch_id;
        $team_id = Auth::user()->au_team_id;
        $team_m = array();
        $user = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
            $team_m[] = $member->au_id;
        }

        if (Auth::user()->au_user_type == 4) {
            $week_customers = Admin_user::where('au_company_id', $com_id)
            ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->get();
        } elseif (Auth::user()->au_user_type == 5) {
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        } elseif (Auth::user()->au_user_type == 7) {
            $admin_access = Auth::user()->agm_permission;
            $agm = explode('-', $admin_access);

            foreach (Admin_user::team_member($agm, $branch_id) as $member) {
                $team_m[] = $member->au_id;

            }
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        }

        if ($request->from != '') {

            $start_date = $request->from;
            $weekly_dates = array();
            for ($i = 0; $i < 6; $i++) {
                if ($i == 0) {
                    $weekly_dates[$i] = $start_date;
                } else {
                    $weekly_dates[$i] = Carbon::make($start_date)->addDays($i)->format('Y-m-d');
                }
            }
            $show_data = true;

            return view('pages.weekly_summery', compact('show_data', 'weekly_dates', 'week_customers'));
        } else {
            return view('pages.weekly_summery');
        }
    }

    public function monthly_summery(Request $request)
    {
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $team_id = Auth::user()->au_team_id;
        $team_m = array();
        $user = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
            $team_m[] = $member->au_id;
        }
        if (Auth::user()->au_user_type == 4) {
            $mon_users = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->get();
        } elseif (Auth::user()->au_user_type == 5) {
            $mon_users = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->whereIn('au_id', $team_m)
                ->get();
        }


        if ($request->year != '' || $request->month != '' || $request->user != '') {

            $user = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_user_type', '!=', '1')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->where('au_id', $request->user)
                ->first();
            if (!empty($user)) {
                $request_month = $request->month;
                $request_year = $request->year;
                $start_date = $request_year . '-' . $request_month . '-' . '1';
                $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
                $show_data = true;

                return view('page.crm.monthly-sell', compact('show_data', 'mon_users', 'days_in_month', 'request_year', 'request_month', 'user'));
            } else {
                return view('page.crm.monthly-sell', compact('mon_users'))->with('invalid_user', 'true');
            }
        } else {
            return view('page.crm.monthly-sell', compact('mon_users'));
        }
    }

    public function search_weekly_sell(Request $request)
    {

        $com_id = Auth::user()->au_company_id;
                $branch_id = Auth::user()->au_branch_id;
        $team_id = Auth::user()->au_team_id;
        $team_m = array();
        $user = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
            $team_m[] = $member->au_id;
        }

        if (Auth::user()->au_user_type == 4) {
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
//                ->where('au_department','=','3')
                ->get();
        } elseif (Auth::user()->au_user_type == 5) {
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        } elseif (Auth::user()->au_user_type == 7) {
            $admin_access = Auth::user()->agm_permission;
            $agm = explode('-', $admin_access);

            foreach (Admin_user::team_member($agm, $com_id) as $member) {
                $team_m[] = $member->au_id;

            }
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        }

        if ($request->from != '') {

            $start_date = $request->from;
            $weekly_dates = array();
            for ($i = 0; $i < 6; $i++) {
                if ($i == 0) {
                    $weekly_dates[$i] = $start_date;
                } else {
                    $weekly_dates[$i] = Carbon::make($start_date)->addDays($i)->format('Y-m-d');
                }
            }
            $show_data = true;

            return view('pages.employee-sell-report.search_weekly_sell', compact('show_data', 'weekly_dates', 'week_customers'));
        }


        return view('pages.employee-sell-report.search_weekly_sell');

    }


    // Search Individual Employee Monthly Sell
    public function search_monthly_sell(Request $request)
    {
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $team_id = Auth::user()->au_team_id;
        $team_m = array();
        $user = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
            $team_m[] = $member->au_id;
        }
        if (Auth::user()->au_user_type == 4) {
            $mon_users = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->get();
        } elseif (Auth::user()->au_user_type == 5) {
            $mon_users = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->whereIn('au_id', $team_m)
                ->get();
        }


        if ($request->year != '' || $request->month != '' || $request->user != '') {

            $user = Admin_user::where('au_user_type', '!=', '4')
                ->where('au_user_type', '!=', '1')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->where('au_id', $request->user)
                ->first();
            if (!empty($user)) {
                $request_month = $request->month;
                $request_year = $request->year;
                $start_date = $request_year . '-' . $request_month . '-' . '1';
                $days_in_month = Carbon::make($start_date)->endOfMonth()->format('d');
                $show_data = true;

                return view('pages.employee-sell-report.search_monthly_sell', compact('show_data', 'mon_users', 'days_in_month', 'request_year', 'request_month', 'user'));
            } else {
                return view('pages.employee-sell-report.search_monthly_sell', compact('mon_users'))->with('invalid_user', 'true');
            }
        } else {
            return view('pages.employee-sell-report.search_monthly_sell', compact('mon_users'));
        }

    }

    public function employees_weekly_sell(Request $request)
    {

    }

    public function employees_monthly_sell(Request $request)
    {
        $com_id = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $team_id = Auth::user()->au_team_id;
        $team_m = array();
        $user = Auth::user()->au_id;
        foreach (Admin_user::team_member($team_id, $branch_id) as $member) {
            $team_m[] = $member->au_id;
        }

        if (Auth::user()->au_user_type == 4) {
            $mon_users = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->get();
        } elseif (Auth::user()->au_user_type == 5) {
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        } elseif (Auth::user()->au_user_type == 7) {
            $admin_access = Auth::user()->agm_permission;
            $agm = explode('-', $admin_access);

            foreach (Admin_user::team_member($agm, $branch_id) as $member) {
                $team_m[] = $member->au_id;

            }
            $week_customers = Admin_user::where('au_company_id', $com_id)
             ->where('au_branch_id', $branch_id)
                ->where('au_user_type', '!=', '4')
                ->whereIn('au_id', $team_m)
                ->get();
        }

        if ($request->year != '' || $request->month != '') {

            $request_month = $request->month;
            $request_year = $request->year;
            $start_month = $request_year . '-' . $request_month . '-' . '1';
            $end_month = Carbon::make($start_month)->endOfMonth();

            $employees = Admin_user::where('au_company_id', $com_id)
                ->with(['inventories' => function ($query) use ($start_month, $end_month) {
                    $query->whereBetween('inv_pro_inv_issue_date', [$start_month, $end_month])
                        ->select('inv_employee_id', 'inv_pro_inv_debit', 'inv_pro_inv_invoice_no');

                }])
                ->select('au_id', 'au_name', 'au_user_id')
                ->where('au_company_id', $com_id)
                 ->where('au_branch_id', $branch_id)
                ->whereIn('au_department', ['3', '4'])
                ->whereNotNull('au_user_id')
                ->orderBy('au_user_id', 'asc')
                ->get();

            $show_data = true;


            return view('pages.employee-sell-report.all_employee_monthly_sell', compact('employees', 'show_data'));

        }

        return view('pages.employee-sell-report.all_employee_monthly_sell');

    }

    public function employee_yearly_sell(Request $request)
    {

    }


    public function proposal()
    {
        return view('pages.proposal');
    }

    public function createPdf(Request $request)
    {
        return view('pages.proposal-print');
    }

    public function sell_comission()
    {
        $com_details = EmployeeComission::with('user_name')->where('company_id', Auth::user()->au_company_id) ->where('branch_id', Auth::user()->au_branch_id)
            ->get();
        // dd($com_details);
        return view('inventory.comission.sell-comission', compact('com_details'));
    }

}
