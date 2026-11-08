<?php

namespace App\Http\Controllers;

use App\Models\Oms_inventory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Inv_customer;
use App\Models\Projects;
use App\Models\Inv_product_inventory;
use App\Models\Inv_bank;
use App\Models\Admin_user;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;

class ProjectsController extends Controller
{

//modified by me

    public function add()
    {
        $com = Auth::user()->au_company_id;
              $branch_id = Auth::user()->au_branch_id;


        // Fetching the customers
        $inv_cuses = Inv_customer::where('inv_cus_com_id', $com)
                              ->where('inv_cus_branch_id', $branch_id)
                                ->where('inv_cus_status', 1)
                                ->get();

        // Fetching admin users
        $admin_users = Admin_user::where('au_company_id', $com)
            ->where('inv_cus_branch_id', $branch_id)
                                ->where('au_status', 1) // Add more conditions if needed
                                ->get();

        $departments = explode('-', Auth::user()->au_department);

        return view('inventory.projects.add', compact('inv_cuses', 'departments', 'admin_users'));
    }


    public function show_invoice_ajax(Request $request) {
        $invoices = Inv_product_inventory::where('inv_pro_inv_party_id', $request->cus_id)
                                ->where('inv_pro_inv_deal_type',2)
                                ->where('inv_pro_inv_tran_type',1)
//                                ->where('inv_pro_inv_isproject',1)
                                ->groupBy('inv_pro_inv_invoice_no')
                                ->get();
        return view('inventory.projects.ajax.invoice-list', compact('invoices'));
    }

    public function show_invoice_amount_ajax(Request $request) {
        $invoices = Inv_product_inventory::select(DB::raw('sum(inv_pro_inv_debit - inv_pro_inv_credit) as total'))->where('inv_pro_inv_invoice_no', $request->invoice_id)
                                ->where('inv_pro_inv_deal_type',2)
                                ->whereIn('inv_pro_inv_tran_type',[1,10,11,12])

                                ->get();
        foreach ($invoices as $inv) {
            $total = $inv->total;
        }
        // dd($total);

        return $total;
    }

    public function departmentUserAjax(Request $request){
        $com = Auth::user()->au_company_id;
       $branch_id = Auth::user()->au_branch_id;
        $users = Admin_user::where('au_company_id',$com)
                           ->where('au_branch_id', $branch_id)
                           ->where('au_status',1)
                           ->where('au_department',$request->dept_id)
                           ->where('au_user_type','!=',4)
                           ->get();
                        //    dd($users);
     //   return $users;
        return view('inventory.projects.ajax.department',compact('users'));
    }
    public function allUserAjax2(){
        return '$request';
//        $com = Auth::user()->au_company_id;
//        $users = Admin_user::where('au_company_id',$com)
//            ->where('au_status',1)
//            ->where('au_user_type','!=',4)
//            ->get();
//        return view('inventory.projects.ajax.all-users',compact('users'));
    }

    public function store(Request $request){
        // dd($request->all());
        $messages =
        [
            'customer:required' => 'Customer Name is Required',
            'project_name:required' => 'Project Name is Required',
            'project_addr:required' => 'Address is Required',
            'project_da:required' => 'Date is Required',
            'amount:required' => 'Amount is Required',
        ];
        $request->validate([
            'customer' => 'required',
            'project_name' => 'required',
            'project_addr' => 'required',
            'project_da' => 'required',
            'amount' => 'required',
        ],$messages);
        DB::beginTransaction();
        try {
            $com_id = Auth::user()->au_company_id;
            $branch_id = Auth::user()->au_branch_id;
            $user_id = Auth::user()->au_id;
            $submit_at = Carbon::now()->format('Y-m-d H:i:s');
            $engineer = implode(',',$request->engineer);

            $project = new Projects();
            $project->company_id = $com_id;
            $project->branch_id = $branch_id;
            $project->project_name = $request->project_name;
            $project->customer_id = $request->customer;
            $project->address = $request->project_addr;
            $project->project_date = $request->project_da;
            $project->project_engineer = $engineer;
            $project->entry_by = $user_id;
            $project->save();

            $projectInventory = new Inv_product_inventory();
            $projectInventory->inv_pro_inv_com_id = $com_id;
            $projectInventory->inv_pro_inv_branch_id = $branch_id;
            $projectInventory->inv_pro_inv_party_id = $request->customer;
            $projectInventory->inv_pro_inv_project_id = $project->id;
            $projectInventory->inv_pro_inv_invoice_no = $request->invoice;
            $projectInventory->inv_pro_inv_credit = $request->amount;
            $projectInventory->inv_pro_inv_issue_date = $request->project_da;
            $projectInventory->inv_pro_inv_tran_desc = "Project Create";
            $projectInventory->inv_pro_inv_deal_type = 6; //project
            $projectInventory->	inv_pro_inv_tran_type = 26; //project orderd transaction
            $projectInventory->inv_pro_inv_submit_at = $submit_at;
            $projectInventory->inv_pro_inv_submit_by = $user_id;
            $projectInventory->save();
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Something Went Wrong: " . $e->getMessage();
            return redirect()->back()->with(['err' => $msg]);
        }
        DB::commit();
        return redirect()->back()->with(['success' => 'Project Added Successfully!']);
    }

    public function projectsPayments(){
        $com_id = Auth::user()->au_company_id;
              $branch_id = Auth::user()->au_branch_id;
        $user_id = Auth::user()->au_id;
        $projects = Projects::where('company_id',$com_id)
                           ->where('branch_id', $branch_id)
                            ->where('entry_by',$user_id)
                            ->where('status',1)
                            ->get();
        $banks = Inv_bank::where('status',1)->get();
        $users = Admin_user::all();
        return view('inventory.projects.project-payments',compact('projects','banks','users'));
    }

    public function projectAmountAjax(Request $request){
        $restAmount = Inv_product_inventory::select(DB::raw('SUM(inv_pro_inv_credit - inv_pro_inv_debit) as total'))
                                            ->where('inv_pro_inv_project_id',$request->project_id)
                                            ->where('inv_pro_inv_deal_type',6)
                                            ->get();
            foreach ($restAmount as $value) {
                $total = $value->total;
            }
            return $total;
    }

    public function projectsPaymentsStore(Request $request){
     //   return $request;
        // dd($request->all());
        $messages =
        [
            'project:required' => 'Project Is Required',
            'purpose:required' => 'Purpose Is Required',
            'amount:required' => 'Amount Is Required',
            'project_da:required' => 'Date Is Required',
        ];
        $request->validate([
            'project' => 'required',
            'purpose' => 'required',
            'amount' => 'required',
            'project_da' => 'required',
        ],$messages);
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $user_id = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');
        $party = Projects::where('company_id',$com_id)
          ->where('branch_id', $branch_id)
        ->where('id',$request->project)->first();
        $invoice = Inv_product_inventory::where('inv_pro_inv_project_id',$party->id)
                                        ->where('inv_pro_inv_deal_type',6)
                                        ->where('inv_pro_inv_tran_type',26)
                                        ->first();

        $projectInventory = new Inv_product_inventory();
        $projectInventory->inv_pro_inv_com_id = $com_id;
                $projectInventory->inv_pro_inv_branch_id = $branch_id;
        $projectInventory->inv_employee_id = Auth::user()->au_id;
        $projectInventory->inv_pro_inv_party_id = $party->customer_id;
        $projectInventory->inv_pro_inv_project_id = $party->id;
        $projectInventory->inv_pro_inv_invoice_no = $invoice->inv_pro_inv_invoice_no;
        $projectInventory->inv_pro_inv_debit = $request->amount;
        $projectInventory->inv_pro_inv_issue_date = $request->project_da;
        if ($request->purpose == 1) {
            $projectConveyance = new Oms_inventory;
            $projectConveyance->oms_com_id = $com_id;
              $projectConveyance->oms_branch_id = $branch_id;
//            $projectConveyance->oms_staff_id = Auth::user()->au_id;
            $projectConveyance->oms_staff_id = $request->employee_id;
            $projectConveyance->oms_insert_at = Carbon::now()->format('Y-m-d');
            $projectConveyance->oms_project_id = $request->project;
            $projectConveyance->oms_transaction_date = $request->project_da;
            $projectConveyance->oms_bill_date = $request->project_da;
            $projectConveyance->oms_credit =  $request->amount;
            $projectConveyance->oms_status = 1;
            $projectConveyance->save();



            $projectInventory->inv_pro_inv_tran_desc = "Conveyance";
            $projectInventory->	inv_pro_inv_tran_type = 27; //project conveyance
            $msg = "Collection For Conveyance";
        }elseif ($request->purpose == 2) {
            $projectInventory->inv_pro_inv_tran_desc = "Cash Collection";
            $projectInventory->	inv_pro_inv_tran_type = 28; //project cash collection
            $msg = "Collected project amount in Cash";
        }elseif ($request->purpose == 3) {
            $projectInventory->inv_pro_inv_tran_desc = "Bank Collection With Bank name: " . $request->bank . " ACC No: " . $request->acc . "Date: " . $request->bank_date;
            $projectInventory->	inv_pro_inv_tran_type = 29; //project bank collection
            $msg = "Bank Collection With Bank name: " . $request->bank . "ACC No: " . $request->acc . " Date: " . $request->bank_date;
        }elseif ($request->purpose == 4) {
            $projectInventory->inv_pro_inv_tran_desc = "Mobile Bank Collection With " . $request->mbank . " TRX ID: " . $request->trx;
            $projectInventory->	inv_pro_inv_tran_type = 30; //project mobile banking collection
            $msg = "Mobile Bank Collection With " . $request->mbank . " TRX ID: " . $request->trx;
        }elseif ($request->purpose == 5) {
            $projectInventory->inv_pro_inv_tran_desc = "Extra Collection For " . $request->collection_desc;
            $projectInventory->	inv_pro_inv_tran_type = 31; //Others Collection
            $msg = "Extra Collection For " . $request->collection_desc;
        }

        $projectInventory->inv_pro_inv_deal_type = 6; //project

        $projectInventory->inv_pro_inv_submit_at = $submit_at;
        $projectInventory->inv_pro_inv_submit_by = $user_id;
        $projectInventory->save();

        return redirect()->back()->with(['success' => $msg]);
    }

    public function details(){
        $com_id = Auth::user()->au_company_id;
         $branch_id = Auth::user()->au_branch_id;
        $user_id = Auth::user()->au_id;
        $lists = Projects::with('cusInfo')->where('company_id',$com_id)
                        ->where('branch_id',$branch_id)
                         ->where('entry_by',$user_id)
                         ->where('status',1)
                         ->get();
                        //  dd($lists);
        return view('inventory.projects.details',compact('lists'));
    }

    public function showDetails($id){
        $com_id = Auth::user()->au_company_id;
             $branch_id = Auth::user()->au_branch_id;
        $proName = Projects::where('id',$id)
                         ->where('status',1)
                         ->first();
        $project = Inv_product_inventory::with('getCustomerInfo')->where('inv_pro_inv_com_id',$com_id)
                           ->where('inv_pro_inv_branch_id',$branch_id)
                           ->where('inv_pro_inv_project_id',$id)
                           ->where('inv_pro_inv_deal_type',6)
                           ->whereIn('inv_pro_inv_tran_type',[26,27,28,29,30,31])
                           ->get();
                        //    dd($project);
        return view('inventory.projects.show',compact('project','proName'));
    }

    public function projectsUpdate($id){
        $com_id = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
        $users = Admin_user::where('au_company_id',$com_id)
        ->where('au_branch_id',$branch_id)
                           ->where('au_status',1)
                           ->whereIn('au_user_type',[5,6])
                           ->get();
        $inv_cuses = Inv_customer::where('inv_cus_com_id',$com_id)
                               ->where('inv_cus_status',1)
                               ->get();
        $proName = Projects::where('id',$id)
                         ->where('status',1)
                         ->first();

        return view('inventory.projects.update',compact('inv_cuses','proName','users'));
    }

    public function show_update_invoice_ajax(Request $request) {
        $invoices = Inv_product_inventory::where('inv_pro_inv_party_id', $request->cus_id)
                                ->where('inv_pro_inv_deal_type',2)
                                ->where('inv_pro_inv_tran_type',1)
                                ->groupBy('inv_pro_inv_invoice_no')
                                ->get();

        $invoiceupdate = Inv_product_inventory::where('inv_pro_inv_party_id', $request->cus_id)
                                        ->where('inv_pro_inv_deal_type',2)
                                        ->where('inv_pro_inv_tran_type',1)
                                        ->groupBy('inv_pro_inv_invoice_no')
                                        ->first();

        return view('inventory.projects.ajax.update-invoice-list', compact('invoices','invoiceupdate'));
    }

    public function updateInvoice(Request $request,$id){

        $messages =
        [
            'customer:required' => 'Customer Name is Required',
            'invoice:required' => 'Invoice is Required',
            'project_name:required' => 'Project Name is Required',
            'project_addr:required' => 'Address is Required',
            'project_da:required' => 'Date is Required',
            'amount:required' => 'Amount is Required',
        ];
        $request->validate([
            'customer' => 'required',
            'invoice' => 'required',
            'project_name' => 'required',
            'project_addr' => 'required',
            'project_da' => 'required',
            'amount' => 'required',
        ],$messages);
        DB::beginTransaction();
        try {
            $com_id = Auth::user()->au_company_id;
            $branch_id = Auth::user()->au_branch_id;
            $user_id = Auth::user()->au_id;
            $submit_at = Carbon::now()->format('Y-m-d H:i:s');
            $engineer = implode(',',$request->engineer);

            $project = Projects::where('id',$id)->where('company_id',$com_id)->where('branch_id',$branch_id)->first();
            $project->company_id = $com_id;
            $project->branch_id = $branch_id;
            $project->project_name = $request->project_name;
            $project->customer_id = $request->customer;
            $project->address = $request->project_addr;
            $project->project_date = $request->project_da;
            $project->project_engineer = $engineer;
            $project->entry_by = $user_id;
            $project->save();

            $projectInventory = Inv_product_inventory::where('inv_pro_inv_project_id',$project->id)->where('inv_pro_inv_com_id',$com_id)->where('inv_pro_inv_branch_id',$branch_id)->first();
            $projectInventory->inv_pro_inv_com_id = $com_id;
                  $projectInventory->inv_pro_inv_branch_id = $branch_id;
            $projectInventory->inv_pro_inv_party_id = $request->customer;
            $projectInventory->inv_pro_inv_project_id = $project->id;
            $projectInventory->inv_pro_inv_invoice_no = $request->invoice;
            $projectInventory->inv_pro_inv_credit = $request->amount;
            $projectInventory->inv_pro_inv_issue_date = $request->project_da;
            $projectInventory->inv_pro_inv_tran_desc = "Project Create";
            $projectInventory->inv_pro_inv_deal_type = 6; //project
            $projectInventory->	inv_pro_inv_tran_type = 26; //project orderd transaction
            $projectInventory->	inv_pro_inv_edit_count = 1;
            $projectInventory->inv_pro_inv_update_at = $submit_at;
            $projectInventory->inv_pro_inv_update_by = $user_id;
            $projectInventory->save();
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Something Went Wrong: " . $e->getMessage();
            return redirect()->back()->with(['err' => $msg]);
        }
        DB::commit();
        return redirect()->route('projects.details-project')->with(['success' => 'Project Updated Successfully!']);
    }


}
