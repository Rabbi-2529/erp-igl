@extends('layouts.master')
@section('content')
@section('title', 'Birthday')
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
              <a href="{{url('/payroll/today_birth')}}"><button class="btn btn-primary btn-sm mb-1">Today</button></a>
              <a href="{{url('/payroll/employee_dob')}}"><button class="btn btn-primary btn-sm mb-1">All</button></a>
              
              <table id="example1" class="table table-bordered table-striped table-hover datatable">
                <thead>
                <tr>
                  <th>SL</th>
                 
                  <th>Name</th>
                  <th>Emp Id</th>
                  <th>Department</th>
                  <th>DoB</th>
                 
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($users as $user)
                <tr>
                  <td>{{ ++$sl }}</td>
                  
                  <td>{{ $user->au_name }}</td>
                  <td>{{ @$user->au_user_id }}</td>
                  <td>{{ @$user->department->name}}</td>
                  <td>{{ date("j-F-Y", strtotime(@$user->orginal_birthday)) }}</td>
                 
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
 
