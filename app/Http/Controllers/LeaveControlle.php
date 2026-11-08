<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rats\Zkteco\Lib\ZKTeco;
use Auth;
use Carbon\Carbon;
use DateTime;
use App\Models\Admin_user;
use App\Models\LeaveType;
use App\Models\Leave;
use DB;
use PDF;

class LeaveControlle extends Controller
{
    public function leaveTypeList()
    {
        $leaveType = LeaveType::get();
        return view('leave.leaveType.leave-type-list', compact('leaveType'))->with(['success' => 'New Leave Type added Succesfully']);
    }

    public function leaveTypeadd()
    {
        return view('leave.leaveType.leave-type-add');
    }

public function leaveTypeStore(Request $request)
{
    $request->validate([
        'leave_type' => 'required|string|max:255',
        'l_day' => 'required|numeric|min:1',
    ]);

    $authUser = Auth::user();

    $leaveType = new LeaveType();
    $leaveType->company_id = $authUser->au_company_id;
    $leaveType->branch_id = $authUser->au_branch_id; // <-- store branch_id
    $leaveType->leave_type = $request->leave_type;
    $leaveType->l_day = $request->l_day;
    $leaveType->save();

    session()->flash('type', 'success');
    session()->flash('message', 'Leave Type Added Successfully');
    return redirect()->back();
}

    public function leaveTypeDelete($id)
    {
        $data = LeaveType::find($id);
        $data->delete();
        return redirect()->back()->with(['del' => 'Leave Type deleted Succesfully']);
    }

    public function leaveApplication()
    {
        $leaveType = LeaveType::get();
        
        return view('page.hrm.leave-application', compact('leaveType'));
    }

    public function leaveApplicationSubmit(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required',
            'leave_title' => 'required',
            'date_1' => 'required',
            'date_2' => 'required',
            'user_desc' => 'required',
        ]);


        $data = new Leave();
        $data->user_id = Auth::user()->au_id;
        $data->company_id = Auth::user()->au_company_id;
          $data->branch_id = Auth::user()->au_branch_id;
        $data->leave_type_id = $request->leave_type_id;
        $data->leave_title = $request->leave_title;
        $data->total_day_request = $request->total_day_request;
        $data->user_desc = $request->user_desc;

        $days_1 = $request->date_1;
        $days_2 = $request->date_2;

        $merge = array($days_1, $days_2);

        $data->leave_date = implode(',', $merge);


        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . "." . $extension;

            $path = $request->file->storeAs('leavefiles', $filename);

            $data->file = $filename;
        }else{
            $data->file = "";
        }

        // dd($data);

        $data->save();

        session()->flash('type', 'success');
        session()->flash('message', 'Your application submited Successfully');
        return redirect()->back();

    }

    
public function leaveRequest()
{
    $userType = Auth::user()->au_user_type;
    $companyId = Auth::user()->au_company_id;
    $userId = Auth::user()->au_id;
    $branchId = Auth::user()->au_branch_id; // Get authenticated user's branch ID

    if ($userType == 4 || $userType == 5) {
        // Admin or similar user types: fetch all leave data for the company and branch
        $leaveData = Leave::where('company_id', $companyId)
            ->where('branch_id', $branchId) // Filter by branch
            ->orderBy('id', 'desc')
            ->get();
    } else {
        // Regular employee: fetch leave data for the specific user, company, and branch
        $leaveData = Leave::where('company_id', $companyId)
            ->where('branch_id', $branchId) // Filter by branch
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    return view('page.hrm.leave-request', compact('leaveData'));
}

public function leaveRequestProcess($id)
{
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;  // Assuming the branch ID is available on the user model

    $leaveDetails = Leave::where('company_id', $com)
                         ->where('branch_id', $branchId)  // Adding the branch_id filter
                         ->where('id', $id)
                         ->first();

    return view('page.hrm.leave-processing', compact('leaveDetails'));
}


    public function leaveRequestProcessDelete($id)
    {
        $data = Leave::find($id);
        $data->delete();
        return redirect()->back()->with(['del' => 'Leave Request deleted Succesfully']);
    }
    public function leaveAccepting(Request $request , $id)
    {
        $request->validate([
            'date_1' => 'required',
            'date_2' => 'required',
            'total_day_accepted' => 'required',
            'desc' => 'required',
            'status' => 'required',
        ]);
        $data = Leave::find($id);

        $data->leave_date_accept_start = $request->date_1;
        $data->leave_date_accept_end = $request->date_2;


        if (Auth::user()->au_user_type == 5) {
            $data->team_leader_id = Auth::user()->au_id;
            $data->desc_1 = $request->desc;
            if($request->status == 'accept'){
                $data->status = 1;
            }else if($request->status == 'reject'){
                $data->status = 5;
            }

        }else if(Auth::user()->au_user_type == 3){
            $data->dep_head_id = Auth::user()->au_id;
            $data->desc_2 = $request->desc;
            if($request->status == 'accept'){
                $data->status = 2;
            }else if($request->status == 'reject'){
                $data->status = 5;
            }
        }else if(Auth::user()->au_user_type == 4){
            $data->hr_admin_id = Auth::user()->au_id;
            $data->desc_3 = $request->desc;
            if($request->status == 'accept'){
                $data->status = 3;
            }else if($request->status == 'reject'){
                $data->status = 5;
            }
        }
        $data->total_day_accepted = $request->total_day_accepted;
        $data->save();

        session()->flash('type', 'success');
        session()->flash('message', 'Your have Accepted this application');
        return redirect()->route('leaveRequest');


    }


    public function getEndDate(Request $request)
    {
        $date = Carbon::createFromFormat('d/m/Y', $request->day);

        if ($request->value == '') {
            $daysToAdd = 0;
        } else {
            $daysToAdd = $request->value - 1;
        }

        $date = $date->addDays($daysToAdd);

        $final = $date->format('d/m/Y');

        return $final;
    }


    
public function leaveRequestSearchByStatus(Request $request)
{
    $com = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;  // Assuming the branch ID is available on the user model

    $leaveData = Leave::where('company_id', $com)
                      ->where('branch_id', $branchId)  // Adding the branch_id filter
                      ->where('status', $request->status)
                      ->orderBy('id', 'desc')
                      ->get();

    return view('page.hrm.leave-request', compact('leaveData'));
}

}
