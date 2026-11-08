@extends('layouts.master')
@section('title', 'Settings')

@section('system_settings_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>System Settings</h4>
                </div>
            </div>

            <div class="panel-body">
               


                <div class="modal-body">
                    {{Form::open(['action' => ['UserController@userSettings', 'id' => $user->au_id] , 'method' => 'post' , 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data'])}}
                    <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Admin Name</label>
                            <div class="col-sm-9">
                                <input type="text" name="user" class="form-control" id="inputEmail3" value="{{ $user->au_name }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Admin Mobile</label>
                            <div class="col-sm-9">
                                <input type="text" name="cell" class="form-control" id="inputEmail3" value="{{ $user->au_mobile }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Password</label>
                            <div class="col-sm-9">
                                <input type="text" name="entry" class="form-control" id="inputEmail3" placeholder="Provide New Password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="au_user_id" class="col-sm-2 control-label">Employee Id</label>
                            <div class="col-sm-6">
                                <input type="text" name="au_user_id" class="form-control" id="au_user_id" value="{{ $user->au_user_id }}" placeholder="User id..." required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Admin E-mail</label>
                            <div class="col-sm-9">
                                <input type="text" name="email" value="{{ $user->au_email }}" class="form-control" id="inputEmail3" placeholder="Provide New Password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Company Address</label>
                            <div class="col-sm-9">
                                <input type="text" name="address" value="{{ $user->au_address }}" class="form-control" id="inputEmail3" placeholder="Provide New Password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Authorized IP</label>
                            <div class="col-sm-9">
                                <input type="text" name="ip_address" class="form-control" id="inputEmail3" placeholder="Put IP Address." value="{{ $user->au_ip_address }}">
                                <p>For one more IP address,you have to separate the IP address by comma(,).Any space will not be allow. </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Working Hour</label>
                            <div class="col-sm-2">
                                <input type="text" name="working_hour" class="form-control" id="inputEmail3" placeholder="Working Hour" value="{{ number_format($user->au_working_hour/60,2) }}">
                            </div>
                        </div>
                        @php($workShift = explode(',',$user->au_work_shift))
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Work Shift</label>
                            <div class="col-sm-3">
                                <input type="text" name="working_shift_begin" id="startdatetime" class="form-control" id="working_shift_begin" placeholder="Working Shift" value="{{ $workShift[0] }}">
                            </div>
                            <div class="col-sm-3">
                                <input type="text" name="working_shift_end" id="enddatetime" class="form-control" id="inputEmail3" placeholder="Working Shift" value="{{ $workShift[0] == '' ? '' : $workShift[1] }}">
                            </div>
                        </div>
                        <div class="form-group" id="select_div">
                            <label for="inputEmail3" class="col-sm-2 control-label">Department</label>
                            <div class="col-sm-6">
                                <select name="au_department[]" class="form-control " id="" required multiple>
                                    <option value="" disabled>Select</option>
                                    @foreach($depts as $dept)
                                        <option {{ in_array($dept->id, explode('-',$user->au_department)) ? 'selected': ''}} value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                       
        
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Logo</label>
                            <div class="col-sm-9">
                                <input type="file" name="logo" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">Image</label>
                            <div class="col-sm-9">
                                <input type="file" name="au_company_img" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                    </div>
        
                    <button type="submit" class="btn btn-primary">Save</button>
        
                    {{Form::close()}}
                </div>





            </div>
        </div>
    </div>
</div>


@endsection

