@extends('layouts.master')

@section('title', 'Employee List')
@section('title', 'Employee List')
@section('human_resource_class','active')
@section('payroll','active')
@section('employee_list','active')
@section('content')
<section class="content">
  @if(session()->has('success'))
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
  @endif
<section class="content-header" style="margin-bottom: 40px;">
      <h1>
        Employee Details
      </h1>
</section>
<div class="row">
    <div class="col-md-10">
        <div class="box">
            <div class="box-body">
                <img src="{{ asset('/storage/image/'.$user->au_company_img) }}" style="height: 150px; width: 150px; border-radius: 50% !important; margin-bottom:10px;" class="center-block">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th style="width: 20%">Name:</th>
                        <td>{{ $user->au_name}} </td>
                    </tr>
                    <tr>
                        <th>Employee ID:</th>
                        <td>{{ @$user->au_user_id }} </td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ @$user->au_email }}</td>
                    </tr>
                    <tr>
                        <th>Mobile:</th>
                        <td>{{ @$user->au_mobile }} </td>
                    </tr>
                    <tr>
                        <th>Persional Mobile:</th>
                        <td>{{ @$user->persional_mobile }} </td>
                    </tr>
                    <tr>
                        <th>User Type:</th>
                        <td>{{ @$user->team_info['tl_team_name'] }} </td>
                    </tr>
                    <tr>
                        <th>Department:</th>
                        <td>{{ @App\Models\Admin_user::userDepartment($user->au_department) }} </td>
                    </tr>
                    <tr>
                        <th>Team Leader:</th>
                        <td>{{ @App\Models\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }}</td>
                    </tr>
                    <tr>
                        <th>Shift:</th>
                        <td>{{ @App\Models\Admin_user::shiftDataByUserId($user->au_shifts) }} </td>
                    </tr>
                    <tr>
                        <th>Joining Date:</th>
                        <td>{{ @date('d-m-Y',strtotime($user->au_created_date))  }} </td>
                    </tr>
                </table>
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
                        <td>{{ $user->f_name}} </td>
                    </tr>
                    <tr>
                        <th>Father Mobile:</th>
                        <td>{{ @$user->f_mobile }} </td>
                    </tr>
                    <tr>
                        <th>Mother Name:</th>
                        <td>{{ @$user->m_name }}</td>
                    </tr>
                    <tr>
                        <th>Mother Mobile:</th>
                        <td>{{ @$user->m_mobile }} </td>
                    </tr>
                    <tr>
                        <th>Present Address:</th>
                        <td>{{ @$user->au_address }} </td>
                    </tr>
                    <tr>
                        <th>Permament Address:</th>
                        <td>{{ @$user->au_parmament_address }} </td>
                    </tr>
                    <tr>
                        <th>Nationality:</th>
                        <td>{{ @$user->nationality }} </td>
                    </tr>
                    <tr>
                        <th>Divison:</th>
                        <td>
                            @php
                                $divisions = DB::table('divisions')->where('id',$user->divison)->first();

                            @endphp
                            {{ @$divisions->name }}
                         </td>
                    </tr>
                    <tr>
                        <th>District:</th>
                        <td>
                            @php
                                $districts = DB::table('districts')->where('id',$user->district)->first();

                            @endphp
                            {{ @$districts->name }}
                         </td>
                    </tr>
                    <tr>
                        <th>Thana:</th>
                        <td>
                            @php
                                $upazilas = DB::table('upazilas')->where('id',$user->thana)->first();

                            @endphp
                            {{ @$upazilas->name }}
                         </td>
                    </tr>
                    <tr>
                        <th>NID:</th>
                        <td>{{ @$user->nid }} </td>
                    </tr>
                    <tr>
                        <th>Passport:</th>
                        <td>{{ @$user->passport }} </td>
                    </tr>
                    <tr>
                        <th>Driving Licence:</th>
                        <td>{{ @$user->driving_licence }} </td>
                    </tr>
                    <tr>
                        <th>Orginal Birthday:</th>
                        <td>{{ @date('d-m-Y',strtotime($user->orginal_birthday))  }} </td>
                    </tr>
                    <tr>
                        <th>Cerfiticate Birthday:</th>
                        <td>{{ @date('d-m-Y',strtotime($user->cerfiticate_birthday))  }} </td>
                    </tr>
                </table>
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
        @foreach ($experience as $exp)
        <div class="col-md-6">
            <div class="box">
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 20%">Company Name:</th>
                            <td>{{ $exp->company_name }}</td>
                        </tr>
                        <tr>
                            <th>Designation:</th>
                            <td>{{ $exp->designation }}</td>
                        </tr>
                        <tr>
                            <th>Company address:</th>
                            <td>{{ $exp->company_address }}</td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            @php
                               $duration = explode(',',$exp->duration);
                           @endphp
                           <td>{{ $duration[0] }} To {{ $duration[1] }}</td>
                        </tr>
                        <tr>
                            <th>Department Head Name:</th>
                            <td>{{ $exp->dep_head_name }}</td>
                        </tr>
                        <tr>
                            <th>Department Head Mobile:</th>
                            <td>{{ $exp->dep_head_phn }}</td>
                        </tr>
                        <tr>
                            <th>Leave Reason:</th>
                            <td>{{ $exp->leave_reason }}</td>
                        </tr>
                    </table>
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

    </div>
</div>
<div class="experience">
    <h3>Reference</h3>
    <div class="row">
        @if (count($reference) > 0)
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

    </div>
</div>
@endsection


