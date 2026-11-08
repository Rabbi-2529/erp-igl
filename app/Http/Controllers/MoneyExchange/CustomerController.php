<?php

namespace App\Http\Controllers\MoneyExchange;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use App\Models\MexCustomer;

class CustomerController extends Controller
{
    public function customer_add(){
        return view('moneyExchange.customer.add');
    }
    public function customer_add_submit(Request $request){
        // dd($request->all());
        $com = Auth::user()->au_company_id;
        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');

        $request->validate([
            'name' => 'required',
            'mobile' => 'required',
            'type' => 'required',
            'bal_type' => 'required'
        ]);
        $customer = new MexCustomer();
        $customer->name = $request->name;
        $customer->com_id = $com;
        $customer->mobile = $request->mobile;
        $customer->com_name = $request->com_name;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->identity = $request->identity;
        $customer->type = $request->type;
        $customer->bal_type = $request->bal_type;
        $customer->submit_by = $submit_by;
        $customer->submit_at = $submit_at;
        $customer->save();
       return redirect()->back()->with(['cus_add' => 'Customer Added Successfully']);
    }

    public function customer_list(){
        $com_id = Auth::user()->au_company_id;
        $customers = MexCustomer::where('com_id',$com_id)->where('status',1)->get();
        return view('moneyExchange.customer.list',compact('customers'));
    }

    public function customer_edit_submit(Request $request, $id){
        // dd($request->all());
        $message =
        [
            'name.required' => 'Name Field is Required',
            'mobile.required' => 'Number Field is Required',
            'type.required' => 'Type Field is Required',
            'bal_type.required' => 'Balance Type Field is Required',
        ];
        $request->validate([
            'name' => 'required',
            'mobile' => 'required',
            'type' => 'required',
            'bal_type' => 'required'
        ],$message);

        $submit_by = Auth::user()->au_id;
        $submit_at = Carbon::now()->format('Y-m-d H:i:s');

        $customer = MexCustomer::find($id);
        $customer->name = $request->name;
        $customer->mobile = $request->mobile;
        $customer->phone = $request->phone;
        $customer->com_name = $request->com_name;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->identity = $request->identity;
        $customer->type = $request->type;
        $customer->bal_type = $request->bal_type;
        $customer->submit_by = $submit_by;
        $customer->submit_at = $submit_at;
        $customer->save();
       return redirect()->back()->with(['cus_up' => 'Customer Updated Successfully']);
    }

    public function customer_delete($id){
        $customer = MexCustomer::find($id);
        $customer->delete();
        return redirect()->back()->with(['cus_del' => 'Customer Deleted Successfully']);
    }
}
