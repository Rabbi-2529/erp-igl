@extends('layouts.master')

@section('content')
@section('title', 'Employee List')
@section('human_resource_class','active')
@section('payroll','active')
@section('employee_bg','active')

<section class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>All Employee List</h4>
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
                                <th>Blood Group</th>
                                <th>Donation Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($sl=0)
                            @foreach($usersWithBloodDonationStatus as $user)
                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>{{ $user->au_name }}</td>
                                    <td>{{ @$user->au_user_id }}</td>
                                    <td>{{ @$user->department->name }}</td>
                                    <td>
                                        @if($user->au_blood_group)
                                            <span class="badge" style="background-color: 
                                                @switch($user->au_blood_group)
                                                    @case('A+')
                                                        #007bff; /* Blue */
                                                        @break
                                                    @case('A-')
                                                        #28a745; /* Green */
                                                        @break
                                                    @case('B+')
                                                        #dc3545; /* Red */
                                                        @break
                                                    @case('B-')
                                                        #ffc107; /* Yellow */
                                                        @break
                                                    @case('AB+')
                                                        #17a2b8; /* Teal */
                                                        @break
                                                    @case('AB-')
                                                        #6c757d; /* Gray */
                                                        @break
                                                    @case('O+')
                                                        #6610f2; /* Purple */
                                                        @break
                                                    @case('O-')
                                                        #fd7e14; /* Orange */
                                                        @break
                                                    @default
                                                        #6c757d; /* Gray for default */
                                                @endswitch
                                            ">{{ $user->au_blood_group }}</span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                         <td>
    @if($user->donation_status == 'Available')
        <span style="color: green;">Available</span>
    @elseif($user->donation_status == 'Not Available')
        <span style="color: orange;">Not Available</span>
    @else
        <span style="color: gray;">{{ $user->donation_status }}</span>
    @endif
</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
    <link rel="stylesheet" href="{{ asset('asset/css/icheck-bootstrap.min.css') }}">
@endsection

@section('custom_script')
    <script type="text/javascript">
        function show_terget(value) {
            if (value == '1') {
                $('#balance').show();           
            } else if (value == '0') {
                $('#balance').hide();   
            }
        }

        $("#checkAll").click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });
    </script>
@endsection
