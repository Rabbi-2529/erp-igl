@extends('layout.master')
@section('content')
    <section class="content">
        <div class="alert alert-success alert-dismissible" id="bladeSuccess" role="alert" style="display: none">
            Password Changed Successfully done!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('msg'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('notPermitted'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('notPermitted') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <section class="content-header">
            <div class="row">
                <div class="pull-left">
                    <h3 style="margin: 4px;">
                        User
                    </h3>
                </div>
                @php
                    $flexi = Auth::user()->au_company_id;
                    $permission = App\Models\Admin_user::where('au_company_id',$flexi)->first();
                @endphp

                @if ($permission->au_flexi_permission == 1)
                    @if (Auth::user()->au_user_type != 4)
                        <button type="submit" class="btn btn-info pull-right" onclick="flexi_history('{{ Auth::user()->au_id }}')">Flexi History</button>
                        <button type="submit" class="btn btn-info pull-right" data-toggle="modal" data-target="#exampleModal" style="margin-right: 5px;">Flexi</button>
                    @else
                        <button type="button" class="btn btn-info pull-right" onclick="flexi_all_user_history('{{ Auth::user()->au_company_id }}')">Flexi History</button>
                    @endif


                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <form action="{{ route('user-flexiload') }}" method="post">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Your Password</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">

                                        <input type="password" name="pass" class="form-control">

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="HistoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">User History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="modalData">

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                    <div class="pull-right">
                        <button data-toggle="modal" data-target="#userSettings" class="btn btn-info" style="margin-right: 5px;">Settings</button>
                    </div>
                @endif
                <div class="pull-right">
                    <button data-toggle="modal" data-target="#password_change" class="btn btn-info" style="margin-right: 5px;">Password Change</button>
                </div>
                <div class="pull-right">
                    <!--<a href="{{ route('profileUpdateStep_1') }}" class="btn btn-info" style="margin-right: 5px;">Profile Update</a>-->
                </div>
            </div>
        </section>
        <div class="row">
            <div class="col-md-10">
                <div class="box">
                    <div class="box-body">
                        <img src="{{ config('app.custom_image_path') }}{{$user_infos->au_company_img }}" style="height: 150px; width: 150px; border-radius: 50% !important; margin-bottom:10px;" class="center-block" alt="profile">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 20%">Name:</th>
                                <td>{{ $user_infos->au_name}} </td>
                            </tr>
                            <tr>
                                <th>Employee ID:</th>
                                <td>{{ @$user_infos->au_user_id }} </td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ @$user_infos->au_email }}</td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td>{{ @$user_infos->au_mobile }} </td>
                            </tr>
                            <tr>
                                <th>Persional Mobile:</th>
                                <td>{{ @$user_infos->persional_mobile }} </td>
                            </tr>
                            <tr>
                                <th>User Type:</th>
                                <td>{{ @$user_infos->team_info['tl_team_name'] }} </td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{{ @App\Models\Admin_user::userDepartment($user_infos->au_department) }} </td>
                            </tr>
                            <tr>
                                <th>Team Leader:</th>
                                <td>{{ @App\Models\Admin_user::tl_name($user_infos->au_team_id, $user_infos->au_company_id)->au_name }}</td>
                            </tr>
                            <tr>
                                <th>Shift:</th>
                                <td>{{ @App\Models\Admin_user::shiftDataByUserId($user_infos->au_shifts) }} </td>
                            </tr>
                            <tr>
                                <th>Joining Date:</th>
                                <td>{{ @date('d-m-Y',strtotime($user_infos->au_created_date))  }} </td>
                            </tr>
                        </table>
                        <div class="pull-right">
                            <a href="{{ route('myInfoUpdate_1') }}" class="btn btn-info" style="margin-right: 5px;"> Update</a>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
            <div class="col-md-10">
                <div class="box">
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width: 20%">Father Name:</th>
                                <td>{{ $user_infos->f_name}} </td>
                            </tr>
                            <tr>
                                <th>Father Mobile:</th>
                                <td>{{ @$user_infos->f_mobile }} </td>
                            </tr>
                            <tr>
                                <th>Mother Name:</th>
                                <td>{{ @$user_infos->m_name }}</td>
                            </tr>
                            <tr>
                                <th>Mother Mobile:</th>
                                <td>{{ @$user_infos->m_mobile }} </td>
                            </tr>
                            <tr>
                                <th>Present Address:</th>
                                <td>{{ @$user_infos->au_address }} </td>
                            </tr>
                            <tr>
                                <th>Permament Address:</th>
                                <td>{{ @$user_infos->au_parmament_address }} </td>
                            </tr>
                            <tr>
                                <th>Nationality:</th>
                                <td>{{ @$user_infos->nationality }} </td>
                            </tr>
                            <tr>
                                <th>Divison:</th>
                                <td>
                                    @php
                                        $divisions = DB::table('divisions')->where('id',$user_infos->divison)->first();

                                    @endphp
                                    {{ @$divisions->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>District:</th>
                                <td>
                                    @php
                                        $districts = DB::table('districts')->where('id',$user_infos->district)->first();

                                    @endphp
                                    {{ @$districts->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>Thana:</th>
                                <td>
                                    @php
                                        $upazilas = DB::table('upazilas')->where('id',$user_infos->thana)->first();

                                    @endphp
                                    {{ @$upazilas->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>NID:</th>
                                <td>{{ @$user_infos->nid }} </td>
                            </tr>
                            <tr>
                                <th>Passport:</th>
                                <td>{{ @$user_infos->passport }} </td>
                            </tr>
                            <tr>
                                <th>Driving Licence:</th>
                                <td>{{ @$user_infos->driving_licence }} </td>
                            </tr>
                            <tr>
                                <th>Orginal Birthday:</th>
                                <!--td>{{ @date('d/m/Y',strtotime($user_infos->orginal_birthday))  }} </td-->
                                <td>{{$user_infos->orginal_birthday}} </td>
                            </tr>
                            <tr>
                                <th>Cerfiticate Birthday:</th>
                                <!--td>{{ @date('d/m/Y',strtotime($user_infos->cerfiticate_birthday))  }} </td-->
                                <td>{{$user_infos->cerfiticate_birthday}}</td>
                            </tr>
                        </table>
                        <div class="pull-right">
                            <a href="{{ route('myBioUpdate_1') }}" class="btn btn-info" style="margin-right: 5px;"> Update</a>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
        <div class="experience">
            <h3>Experiences</h3>
            {{-- @if (count($experience) > 0)
            @foreach ($experience as $exp)

            @endforeach
            @endif --}}
            <div class="row">
                @if (count($experience) > 0)
                    {{--        @if (isset($experience))--}}
                    @foreach ($experience as $exp)
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20%">Company Name:</th>
                                            <td>{{ @$exp->company_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Designation:</th>
                                            <td>{{ @$exp->designation }}</td>
                                        </tr>
                                        <tr>
                                            <th>Company address:</th>
                                            <td>{{ @$exp->company_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Duration:</th>
                                            @php
                                                $duration = explode(',',$exp->duration);
                                            @endphp
                                            <td>{{ @$duration[0] }} To {{ @$duration[1] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Department Head Name:</th>
                                            <td>{{ @$exp->dep_head_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Department Head Mobile:</th>
                                            <td>{{ @$exp->dep_head_phn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Leave Reason:</th>
                                            <td>{{ @$exp->leave_reason }}</td>
                                        </tr>
                                    </table>

                                    <div class="pull-right">
                                        <a href="{{ route('experienceEdit',$exp->id) }}" class="btn btn-info" style="margin-right: 5px;"> Update</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-md-6">
                        <div class="box">
                            <div class="box-body">
                                <p>Employee didn't add any Experience</p>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="pull-right">
                        <a href="{{ route('addExperience') }}" class="btn btn-info" style="margin-right: 5px;"> Add New</a>
                    </div>
                </div>

            </div>
        </div>

        <div class="experience">
            <h3>Reference</h3>
            <div class="row">
                {{--      @if (count($reference) > 0)--}}
                @if (isset($reference))
                    @foreach ($reference as $ref)
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20%">Reference Name:</th>
                                            <td>{{ $ref->ref_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference Mobile:</th>
                                            <td>{{ $ref->ref_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Company Name:</th>
                                            <td>{{ $ref->company_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Company address:</th>
                                            <td>{{ $ref->company_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Designation:</th>
                                            <td>{{ $ref->designation }}</td>
                                        </tr>
                                    </table>
                                    <div class="pull-right">
                                        <a href="{{ route('referenceEdit',$ref->id) }}" class="btn btn-info" style="margin-right: 5px;">Update</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-md-6">
                        <div class="box">
                            <div class="box-body">
                                <p>Employee didn't add any Reference</p>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="pull-right">
                        <a href="{{ route('addReferance') }}" class="btn btn-info" style="margin-right: 5px;"> Add New</a>
                    </div>
                </div>

            </div>
        </div>


        {{-- <div class="box">
                <div class="box-header">
                  <h3 class="box-title">User Details</h3>
                  <div class="box-tools">
                  </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                <h2 class="text-center">Profile Picture</h2>
                <img src="{{ asset('/asset/image/'.$user_infos->au_company_img) }}" style="height: 150px; width: 150px; border-radius: 50% !important;" class="center-block">
                  <table class="table table-bord-bordered table-striped">
                    <tr>
                          <th>Name</th>
                          <td>{{ $user_infos->au_name }}</td>
                    </tr>
                    <tr>
                        <th>Employee ID</th>
                        <td>{{ $user_infos->au_user_id }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user_infos->au_email }}</td>
                    </tr>
                    <tr>
                        <th>Mobile</th>
                        <td>{{ $user_infos->au_mobile }}</td>
                    </tr>
                    <tr>
                        <th>Company ID</th>
                        <td>{{ $user_infos->au_company_id }}</td>
                    </tr>
                    <tr>
                        <th>User Type</th>
                        <td>
                       @if(  $user_infos->au_user_type == 4  )
                       Admin
                       @elseif(  $user_infos->au_user_type == 5  )
                       Team Leader
                       @elseif(  $user_infos->au_user_type == 6  )
                       Team Member
                       @else
                       Root
                       @endif
                      </td>
                    </tr>
                    <tr>
                        <th>Joining Date</th>
                        <td>{{ $user_infos->au_created_date }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $user_infos->au_address }}</td>
                    </tr>
                    <tr>
                        <th>Shift</th>
                        <td>{{ @App\Admin_user::shiftDataByUserId($user_infos->au_shifts)  }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                        @if($user_infos->au_status == 1)
                        Activate
                        @elseif($user_infos->au_status == 0)
                        Deactivate
                        @endif
                      </td>
                    </tr>
                  </table>

                </div>
                <!-- /.box-body -->
              </div> --}}





        <div class="modal fade" id="userSettings">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Create Template</h4>
                    </div>
                    <div class="modal-body">
                        {{Form::open(['action' => ['UserController@userSettings', 'id' => $user_infos->au_id] , 'method' => 'post' , 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data'])}}
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="user" class="form-control" id="inputEmail3" value="{{ $user_infos->au_name }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Mobile</label>
                                <div class="col-sm-9">
                                    <input type="text" name="cell" class="form-control" id="inputEmail3" value="{{ $user_infos->au_mobile }}">
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
                                    <input type="text" name="au_user_id" class="form-control" id="au_user_id" value="{{ $user_infos->au_user_id }}" placeholder="User id..." required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">E-mail</label>
                                <div class="col-sm-9">
                                    <input type="text" name="email" value="{{ $user_infos->au_email }}" class="form-control" id="inputEmail3" placeholder="Provide New Password">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                                <div class="col-sm-9">
                                    <input type="text" name="address" value="{{ $user_infos->au_address }}" class="form-control" id="inputEmail3" placeholder="Provide New Password">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">IP Address</label>
                                <div class="col-sm-9">
                                    <input type="text" name="ip_address" class="form-control" id="inputEmail3" placeholder="Put IP Address..................." value="{{ $user_infos->au_ip_address }}">
                                    <p>For one more IP address,you have to separate the IP address by comma(,).Any space will not be allow. </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Working Hour</label>
                                <div class="col-sm-2">
                                    <input type="text" name="working_hour" class="form-control" id="inputEmail3" placeholder="Working Hour" value="{{ number_format($user_infos->au_working_hour/60,2) }}">
                                </div>
                            </div>
                            @php($workShift = explode(',',$user_infos->au_work_shift))
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
                                            <option {{ in_array($dept->id, explode('-',$user_infos->au_department)) ? 'selected': ''}} value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="form-group" id="select_div">
                                <label for="inputEmail3" class="col-sm-2 control-label">Shift</label>
                                <div class="col-sm-6">
                                    <select name="au_shifts" class="form-control " id="" required>
                                        <option value=""  disabled>Select</option>
                                        @foreach($shifts as $shift)
                                            <option {{ $shift->id == $user_infos->au_shifts ? 'selected': ''}} value="{{ $shift->id }}">{{ $shift->name }}</option>
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
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
                {{Form::close()}}
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <div class="modal fade" id="password_change">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Password Change</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-4 control-label" >Old Password</label>
                                <div class="input-group col-sm-6" id="show_hide_password">
                                    <input id="old_password" class="form-control input1" type="password" name="old_password" required tabindex="1">
                                    <div class="input-group-addon">
                                        <a href=""><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-4 control-label" >New Password</label>
                                <div class="input-group col-sm-6" id="show_hide_password2">
                                    <input id="new_password" class="form-control input1" type="password" name="new_password" required tabindex="2">
                                    <div class="input-group-addon">
                                        <a href=""><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-4 control-label" >Confirm Password</label>
                                <div class="input-group col-sm-6" id="show_hide_password3">
                                    <input id="confirm_password" class="form-control input1" type="password" name="confirm_password" required tabindex="3">
                                    <div class="input-group-addon">
                                        <a href=""><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div id="message" style="display: none">
                                <br/>
                                <div id="error"  style="text-align: center;" class="alert alert-danger">Error Message </div>
                            </div>



                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>

                        <button id="saveBtn" class="btn btn-primary">Save</button>
                    </div>
                </div>
                {{--              {{Form::close()}}--}}
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </section>
@endsection
@section('custom_style')
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
@endsection
@section('custom_script')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    {{--    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>--}}
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.1/axios.min.js" integrity="sha512-zJYu9ICC+mWF3+dJ4QC34N9RA0OVS1XtPbnf6oXlvGrLGNB8egsEzu/5wgG90I61hOOKvcywoLzwNmPqGAdATA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function() {
            $("#saveBtn").click(function () {

                var old_password =   $("#old_password").val();
                var new_password =   $("#new_password").val();
                var confirm_password =   $("#confirm_password").val();

                var val = true

                $('.input1').each(function() {
                    if(!$(this).val()){
                        // alert('Some fields are empty');
                      return   val = false
                    }
                });
                if (!val){
                    $('#message').css('display','')
                    $('#error').html('* All field required !')
                    return
                }
                if (new_password !== confirm_password){

                    $('#message').css('display','')
                    $('#error').html('Confirm password not matched!')
                }else{
                    $('#message').css('display','none')
                    //send post request
                    axios.post('/change-password', {
                        old_password:  old_password,
                        new_password:  new_password,
                    })
                        .then(function (response) {

                            console.log(response)
                            // if response error
                            if (response.data.state =='401'){
                                $('#message').css('display','none')
                                $('#bladeSuccess').css('display','')
                                $("#password_change .close").click()
                            }else{
                                $('#message').css('display','')
                                $('#error').html('Old password not matched!')
                            }



                            // else modal close



                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            });

            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if($('#show_hide_password input').attr("type") == "text"){
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass( "fa-eye-slash" );
                    $('#show_hide_password i').removeClass( "fa-eye" );
                }else if($('#show_hide_password input').attr("type") == "password"){
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass( "fa-eye-slash" );
                    $('#show_hide_password i').addClass( "fa-eye" );
                }
            });

            $("#show_hide_password2 a").on('click', function(event) {
                event.preventDefault();
                if($('#show_hide_password2 input').attr("type") == "text"){
                    $('#show_hide_password2 input').attr('type', 'password');
                    $('#show_hide_password2 i').addClass( "fa-eye-slash" );
                    $('#show_hide_password2 i').removeClass( "fa-eye" );
                }else if($('#show_hide_password2 input').attr("type") == "password"){
                    $('#show_hide_password2 input').attr('type', 'text');
                    $('#show_hide_password2 i').removeClass( "fa-eye-slash" );
                    $('#show_hide_password2 i').addClass( "fa-eye" );
                }
            });

            $("#show_hide_password3 a").on('click', function(event) {
                event.preventDefault();
                if($('#show_hide_password3 input').attr("type") == "text"){
                    $('#show_hide_password3 input').attr('type', 'password');
                    $('#show_hide_password3 i').addClass( "fa-eye-slash" );
                    $('#show_hide_password3 i').removeClass( "fa-eye" );
                }else if($('#show_hide_password3 input').attr("type") == "password"){
                    $('#show_hide_password3 input').attr('type', 'text');
                    $('#show_hide_password3 i').removeClass( "fa-eye-slash" );
                    $('#show_hide_password3 i').addClass( "fa-eye" );
                }
            });
        });
        $('#startdatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });
        $('#enddatetime').datetimepicker({
            format: 'hh:mm:ss A'
        });

        function flexi_history(userData){
            // console.log(value);
            $.ajax({
                type: "GET",
                url: "{{ route('user-flexiload-history') }}",
                data: {userData:userData},
                success:function(result){
                    $('#modalData').html(result);
                    $('#HistoryModal').modal('show');
                }
            })
        }

        function flexi_all_user_history(companyId){
            // console.log(value);
            $.ajax({
                type: "GET",
                url: "{{ route('user-flexiload-company-history') }}",
                data: {companyId:companyId},
                success:function(result){
                    $('#modalData').html(result);
                    $('#HistoryModal').modal('show');
                }
            })
        }
    </script>
    <script type="text/javascript">
        // $('form').submit(function() {
        //
        //   $(this).find("button[type='submit']").prop('disabled',true);
        // });





    </script>
@endsection
