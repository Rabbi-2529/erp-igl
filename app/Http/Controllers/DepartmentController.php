<?php

namespace App\Http\Controllers;
use App\Models\Department;
use App\Models\Admin_user;

use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function department(){
        $depts = Department::all();
        return view('root.department',compact('depts'));
    }

    public function departmentStore(Request $request){
        $message =
        [
            'dept.required' => 'This field must be Required',
        ];
        $request->validate([
            'dept' => 'required',

        ],$message);

        $dept = Department::where('name',$request->dept)->first();
        if (!$dept) {
            $data = new Department();
            $data->name = $request->dept;
            $data->save();
            return redirect()->back()->with(['success' => 'Department Added Successfully']);
        }else {
            return redirect()->back()->with(['exist' => 'Department Exist']);
        }
    }

    public function departmentEdit(Request $request){
        Department::where('id',$request->id)->update([
            'name' => $request->dept_name
        ]);
        return redirect()->back()->with(['update' => 'Department Updated Successfully']);
    }

    public function departmentDelete($id){
        Department::where('id',$id)->delete();
        return redirect()->back()->with(['delete' => 'Department Deleted Successfully']);
    }

    public function departmentAssign(){
        $company = Admin_user::where('au_user_type',4)->get();
        $department = Department::all();
        return view('root.dept-assign',compact('company','department'));
    }

    public function departmentAssignStore(Request $request){
        $message =
        [
            'comapny.required' => 'This field must be Required',
            'dept.required' => 'This field must be Required',
        ];
        $request->validate([
            'company' => 'required',
            'dept' => 'required',

        ],$message);
        $dept = Admin_user::where('au_id',$request->company)->first();
        $dept->au_department = implode('-',$request->dept);
        $dept->save();
        return redirect()->back()->with(['dept_add' => 'Department Assigned Successfully']);
    }
    public function departmentAssignEdit($id){
        $company = Admin_user::where('au_user_type',4)->get();
        $comp = Admin_user::where('au_id',$id)->where('au_user_type',4)->first();
        $department = Department::all();
        return view('root.dept-assign-edit',compact('company','department','comp'));
    }
    public function departmentAssignUpdate(Request $request,$id){
        $message =
        [
            'dept.required' => 'This field must be Required',
        ];
        $request->validate([
            'dept' => 'required',

        ],$message);
        $dept = Admin_user::where('au_id',$id)->first();

        $dept->au_department = implode('-',$request->dept);
        $dept->save();
        return redirect()->route('department-assign')->with(['dept_add' => 'Department Updated Successfully']);
    }
}
