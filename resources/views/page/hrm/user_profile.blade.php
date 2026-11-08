@extends('layouts.master')
@section('content')
@section('title', $user_infos->au_name . ' - Profile')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('manage_employee_class','active')

<style>
    .erp-profile-head { overflow: hidden; border: 1px solid var(--erp-border); border-radius: 6px; background: var(--erp-white); margin-bottom: 16px; }
    .erp-profile-cover { height: 88px; background: linear-gradient(120deg, var(--erp-green-dark), var(--erp-green-btn)); }
    .erp-profile-main { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; padding: 0 20px 18px; }
    .erp-profile-photo { width: 112px; height: 112px; margin-top: -56px; border-radius: 50%; object-fit: cover; border: 4px solid var(--erp-white); background: var(--erp-white); box-shadow: 0 2px 8px rgba(0, 0, 0, .15); flex: 0 0 auto; }
    .erp-profile-id { flex: 1 1 260px; min-width: 0; padding-top: 10px; }
    .erp-profile-id h2 { margin: 0 0 2px; font-size: 22px; line-height: 1.25; color: var(--erp-text); }
    .erp-profile-role { margin: 0 0 8px; color: var(--erp-muted); font-weight: 700; }
    .erp-profile-chips { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 0; padding: 0; list-style: none; color: var(--erp-text); }
    .erp-profile-chips li { white-space: nowrap; }
    .erp-profile-chips i { width: 16px; color: var(--erp-green-btn); text-align: center; }
    .erp-profile-actions { display: flex; flex-wrap: wrap; gap: 6px; flex: 0 0 auto; }
    .erp-profile-card { margin-bottom: 16px; }
    .erp-profile-card .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .erp-profile-card .panel-heading h4 { margin: 0; font-size: 16px; }
    .erp-profile-card .panel-heading h4 i { color: var(--erp-green-btn); margin-right: 6px; }
    .erp-profile-list { margin: 0; }
    .erp-profile-list > div { display: flex; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--erp-border); }
    .erp-profile-list > div:last-child { border-bottom: 0; }
    .erp-profile-list dt { flex: 0 0 42%; max-width: 190px; font-weight: 400; color: var(--erp-muted); }
    .erp-profile-list dd { flex: 1 1 auto; margin: 0; font-weight: 700; color: var(--erp-text); word-break: break-word; }
    .erp-profile-list dd.is-empty { font-weight: 400; color: var(--erp-muted); }
    .erp-profile-cols { display: flex; flex-wrap: wrap; }
    .erp-profile-cols > [class*="col-"] { display: flex; flex-direction: column; }
    .erp-profile-cols > [class*="col-"] > .panel { flex: 1 1 auto; }
    .erp-profile-badge { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; color: var(--erp-green-btn); background: var(--erp-green-tint); vertical-align: 1px; }
    .erp-profile-empty { margin: 12px 0; color: var(--erp-muted); }
    .erp-profile-timeline { margin: 0; padding: 0; list-style: none; }
    .erp-profile-timeline li { position: relative; padding: 0 0 14px 22px; }
    .erp-profile-timeline li:last-child { padding-bottom: 0; }
    .erp-profile-timeline li::before { content: ""; position: absolute; left: 5px; top: 12px; bottom: -2px; width: 2px; background: var(--erp-border); }
    .erp-profile-timeline li:last-child::before { display: none; }
    .erp-profile-dot { position: absolute; left: 0; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--erp-white); border: 3px solid var(--erp-green-btn); }
    .erp-profile-when { font-size: 12px; font-weight: 700; color: var(--erp-muted); }
    .erp-profile-what { color: var(--erp-text); line-height: 1.45; }
    @media (max-width: 600px) {
        .erp-profile-main { justify-content: center; text-align: center; }
        .erp-profile-chips, .erp-profile-actions { justify-content: center; }
        .erp-profile-actions { flex: 1 1 100%; }
        .erp-profile-actions .btn { flex: 1 1 auto; }
        .erp-profile-list dt { flex-basis: 45%; }
    }
</style>

@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session()->has('notPermitted'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('notPermitted') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

{{-- Profile header: photo, name, role and the buttons this viewer may use --}}
<div class="erp-profile-head">
    <div class="erp-profile-cover"></div>
    <div class="erp-profile-main">
        <img src="{{ $profileImage }}" class="erp-profile-photo" alt="{{ $user_infos->au_name }}">
        <div class="erp-profile-id">
            <h2>{{ $user_infos->au_name }}</h2>
            <p class="erp-profile-role">{{ $departmentNames ?: 'Employee' }}{{ $shiftName ? ' · ' . $shiftName : '' }}</p>
            <ul class="erp-profile-chips">
                @if($user_infos->au_user_id)<li title="Employee ID"><i class="fa fa-id-badge"></i> {{ $user_infos->au_user_id }}</li>@endif
                @if($user_infos->au_mobile)<li title="Office mobile"><i class="fa fa-phone"></i> {{ $user_infos->au_mobile }}</li>@endif
                @if($user_infos->au_email)<li title="Email"><i class="fa fa-envelope-o"></i> {{ $user_infos->au_email }}</li>@endif
                @if($user_infos->au_blood_group)<li title="Blood group"><i class="fa fa-tint"></i> {{ $user_infos->au_blood_group }}</li>@endif
            </ul>
        </div>
        <div class="erp-profile-actions">
            @if($isOwnProfile)
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#erpPasswordModal"><i class="fa fa-lock"></i> Change Password</button>
                <a href="{{ route('crm.my-lead-link') }}" class="btn btn-default"><i class="fa fa-share-alt"></i> My Lead Link</a>
            @endif
            @if($isCompanyAdmin)
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#userSettings"><i class="fa fa-cog"></i> Settings</button>
                @if(!$isOwnProfile)
                    <a href="{{ url('user_edit/' . $user_infos->au_id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> Edit Employee</a>
                @endif
            @endif
            @if (App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)->value('au_flexi_permission') == 1)
                @if (Auth::user()->au_user_type != 4)
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">Flexi</button>
                    <button type="button" class="btn btn-info" onclick="flexi_history('{{ Auth::user()->au_id }}')">Flexi History</button>
                @else
                    <button type="button" class="btn btn-info" onclick="flexi_all_user_history('{{ Auth::user()->au_company_id }}')">Flexi History</button>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Details --}}
<div class="row erp-profile-cols">
    <div class="col-sm-12 col-md-6">
        <div class="panel panel-bd erp-profile-card">
            <div class="panel-heading">
                <h4><i class="fa fa-user"></i>Personal Information</h4>
                @if($isOwnProfile)<a href="{{ route('myBioUpdate_1') }}" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Update</a>@endif
            </div>
            <div class="panel-body">
                <dl class="erp-profile-list">
                    @foreach([
                        'Full name' => $user_infos->au_name,
                        "Father's name" => $user_infos->f_name,
                        "Father's mobile" => $user_infos->f_mobile,
                        "Mother's name" => $user_infos->m_name,
                        "Mother's mobile" => $user_infos->m_mobile,
                        'NID' => $user_infos->nid,
                        'Date of birth' => $user_infos->orginal_birthday,
                        'Birthday on certificate' => $user_infos->cerfiticate_birthday,
                        'Blood group' => $user_infos->au_blood_group,
                        'Nationality' => $user_infos->nationality,
                    ] as $label => $value)
                        <div><dt>{{ $label }}</dt><dd class="{{ trim((string) $value) === '' ? 'is-empty' : '' }}">{{ trim((string) $value) === '' ? 'Not added' : $value }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-6">
        <div class="panel panel-bd erp-profile-card">
            <div class="panel-heading">
                <h4><i class="fa fa-briefcase"></i>Job Information</h4>
                @if($isOwnProfile)<a href="{{ route('myInfoUpdate_1') }}" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Update</a>@endif
            </div>
            <div class="panel-body">
                <dl class="erp-profile-list">
                    @foreach([
                        'Employee ID' => $user_infos->au_user_id,
                        'Department' => $departmentNames,
                        'Shift' => $shiftName,
                        'Joining date' => $user_infos->au_created_date,
                        'Office mobile' => $user_infos->au_mobile,
                        'Email' => $user_infos->au_email,
                    ] as $label => $value)
                        <div><dt>{{ $label }}</dt><dd class="{{ trim((string) $value) === '' ? 'is-empty' : '' }}">{{ trim((string) $value) === '' ? 'Not added' : $value }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>

        <div class="panel panel-bd erp-profile-card">
            <div class="panel-heading">
                <h4><i class="fa fa-map-marker"></i>Contact and Address</h4>
            </div>
            <div class="panel-body">
                <dl class="erp-profile-list">
                    @foreach([
                        'Personal mobile' => $user_infos->persional_mobile,
                        'Present address' => $user_infos->au_address,
                        'Permanent address' => $user_infos->au_parmament_address,
                        'Division' => $place['division'],
                        'District' => $place['district'],
                        'Thana' => $place['thana'],
                    ] as $label => $value)
                        <div><dt>{{ $label }}</dt><dd class="{{ trim((string) $value) === '' ? 'is-empty' : '' }}">{{ trim((string) $value) === '' ? 'Not added' : $value }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</div>

{{-- Team and recent activity --}}
<div class="row erp-profile-cols">
    <div class="col-sm-12 col-md-5">
        <div class="panel panel-bd erp-profile-card">
            <div class="panel-heading">
                <h4><i class="fa fa-users"></i>Team</h4>
            </div>
            <div class="panel-body">
                <dl class="erp-profile-list">
                    <div><dt>Branch</dt><dd class="{{ $user_infos->au_branch_name ? '' : 'is-empty' }}">{{ $user_infos->au_branch_name ?: 'Not added' }}</dd></div>
                    <div><dt>Team</dt><dd class="{{ $team ? '' : 'is-empty' }}">{{ $team ? $team->tl_team_name : 'No team' }}@if($team && $teamSize) <span class="erp-profile-badge">{{ $teamSize }} {{ $teamSize === 1 ? 'member' : 'members' }}</span>@endif</dd></div>
                    <div>
                        <dt>Team leader</dt>
                        <dd class="{{ $teamLeader ? '' : 'is-empty' }}">
                            @if($teamLeader)
                                @if($canSeeLeader && $teamLeader->au_id != $user_infos->au_id)
                                    <a href="{{ url('/user_profile/' . $teamLeader->au_id) }}">{{ $teamLeader->au_name }}</a>
                                @else
                                    {{ $teamLeader->au_name }}{{ $teamLeader->au_id == $user_infos->au_id ? ' (this employee)' : '' }}
                                @endif
                            @else
                                Not assigned
                            @endif
                        </dd>
                    </div>
                    <div><dt>Shift</dt><dd class="{{ $shiftName ? '' : 'is-empty' }}">{{ $shiftName ?: 'Not set' }}</dd></div>
                    <div><dt>Office hours</dt><dd class="{{ $shiftTiming ? '' : 'is-empty' }}">{{ $shiftTiming ?: 'Not set' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-7">
        <div class="panel panel-bd erp-profile-card">
            <div class="panel-heading">
                <h4><i class="fa fa-history"></i>Recent Activity</h4>
                @if($isOwnProfile)
                    <a href="{{ route('employee-activity.create') }}" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> Add activity</a>
                @endif
            </div>
            <div class="panel-body">
                @if($activities->isEmpty())
                    <p class="erp-profile-empty">No activity added yet.</p>
                @else
                    <ul class="erp-profile-timeline">
                        @foreach($activities as $activity)
                            <li>
                                <span class="erp-profile-dot"></span>
                                <div class="erp-profile-when">{{ $activity->date ? \Carbon\Carbon::parse($activity->date)->format('d M Y') : '' }} &middot; {{ $activity->title }}</div>
                                <div class="erp-profile-what">{{ $activity->text !== '' ? $activity->text : 'No details' }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Flexi windows (unchanged) --}}
@if (App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)->value('au_flexi_permission') == 1)
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <form action="{{ route('user-flexiload') }}" method="post">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Enter Amount</h5>
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

{{-- Employee settings: company admin only (the server checks this too) --}}
@if($isCompanyAdmin)
<div class="modal fade" id="userSettings">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Employee Settings: {{ $user_infos->au_name }}</h4>
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
@endif
@endsection

@section('custom_script')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <script>
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
@endsection
