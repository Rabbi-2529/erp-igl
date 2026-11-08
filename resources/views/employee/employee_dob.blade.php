@extends('layouts.master')
@section('content')
@section('title', 'Employee List')
@section('title', 'Employee List')
@section('human_resource_class','active')
@section('payroll','active')
@section('employee_dob','active')

<section class="content">
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                  
  <h4>
        All Employee List
      </h4>
                  <a href="{{url('/payroll/today_birth')}}"><button class="btn btn-primary btn-sm mb-1">Today</button></a>
                </div>
            </div>

            <div class="panel-body">
                     @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif


              
               <table class="table table-bordered table-striped table-hover datatable">
            
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
                  <td>{{ @$user->department->name }}</td>
              <td>
    {{ $user->orginal_birthday 
        ? \Carbon\Carbon::createFromFormat('d/m/Y', $user->orginal_birthday)->format('Y-M-d') 
        : 'No Data' }}
</td>
                 
                </tr>
                @endforeach
                </tbody>
              </table>
            
            </div>
                  </div>
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
 
