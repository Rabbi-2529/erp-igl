@extends('layouts.master')
@section('content')
@section('title', 'Employee List')
@section('title', 'Employee List')
@section('human_resource_class','active')
@section('payroll','active')
@section('employee_list','active')


<section class="content">
  @if(session()->has('success'))
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
  @endif
<section class="content-header">
      <h1>
        All Employee List
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Employee List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

            <table id="" class="table table-bordered table-striped table-hover datatable">
                <thead>
                <tr>
                  <th>SL</th>

                  <th>Name</th>
                  <th>Emp Id</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th>Team Name</th>
                  <th>TL Name</th>
                  <th class="text-center" style="width: 75px !important;">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($users as $user)
                <tr>
                  <td>{{ ++$sl }}</td>

                  <td>{{ $user->au_name }}</td>
                  <td>{{ @$user->au_user_id }}</td>
                  <td>{{ @$user->au_mobile }}</td>
                  <td>{{ @$user->au_email }}</td>
                  <td>{{ @$user->team_info['tl_team_name'] }} ({{ @App\Models\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }})</td>
                  <td>
                    {{ @App\Models\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }}
                    @if ($user->au_user_type == 5 )
                        (Team Leader)
                    @endif
                  </td>

                  <td align="center">
                   <a href="{{ route('payroll.employee_details', $user->au_id) }}" class="btn btn-info">Details</a>
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>

            </div>
            <!-- /.box-body -->
          </div>
         @endsection
         @section('custom_style')
         <link rel="stylesheet" href="{{asset('asset/css/icheck-bootstrap.min.css')}}">
         @endsection
         @section('custom_script')
         <script type="text/javascript">
          function show_terget(value) {
              if (value == '1') {
                  $('#balance').show();
              }
              else if (value == '0') {
                  $('#balance').hide();

              }
          }

          $("#checkAll").click(function () {
              $('input:checkbox').not(this).prop('checked', this.checked);
          });

      </script>
         @endsection

