@extends('layouts.master')
@section('content')
@section('title', 'Manage Employee')

@section('human_resource_class', 'active')
@section('hrm_class', 'active')
@section('manage_employee_class', 'active')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>My Customer ({{ $user_name }})</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped table-hover datatable" id="customerTable">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Mobile</th>
                                <th>Name</th>
                                <th>Company Name</th>
                                <th>Find Us</th>
                                <th>Address</th>
                                <th>Reason</th>
                                <th>Result</th>
                                <th>Customer Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($my_customers as $customer)
                                <tr>
                                    <td>{{ $customer->qb_serial }}</td>
                                    {{-- <td>{{ $customer->qb_id }}</td>
                                    <td>{{ $customer->qb_company_id }}</td> --}}
                                    <td>
                                        {{ $customer->qb_mobile }}<br>
                                        {{ $customer->qb_mobile1 }}<br>
                                        {{ $customer->qb_mobile2 }}<br>
                                        {{ $customer->qb_phone }}
                                    </td>
                                    <td>{{ $customer->qb_name }}</td>
                                    <td>{{ $customer->qb_company_name }}</td>
                                    <td>{{ $find_us_options[$customer->qb_find_us] ?? 'N/A' }}</td>
                                    <td>{{ $customer->qb_address }}</td>
                                    <td>{{ $reasons[$customer->qb_reason] ?? 'N/A' }}</td>
                                    {{-- <td>{{ $customer->qb_email }}</td> --}}
                                    {{-- <td>{{ $customer->qb_result }}</td> --}}
                                    <td>
                                        @for ($i = 0; $i < $customer->qb_result; $i++)
                                            ★
                                        @endfor
                                        {{-- @for ($i = $customer->qb_result; $i < 5; $i++)
                                            ☆
                                        @endfor --}}
                                    </td>
                                    <td>
                                        @if ($customer->qb_cus_type == 1)
                                            <span class="text-success">Potential</span>
                                        @elseif($customer->qb_cus_type == 2)
                                            <span class="text-warning">Pipeline</span>
                                        @else
                                            <span class="text-primary">Regular</span>
                                        @endif
                                    </td>
                                    {{-- <td>{{ $customer->qb_birth_date }}</td>
                            <td>{{ $customer->qb_marriage_date }}</td>
                            <td>{{ $customer->qb_submit_date }}</td>
                            <td>{{ $customer->qb_status }}</td>
                            <td>{{ $customer->qb_feedback }}</td>
                            <td>{{ $customer->qb_staff_id }}</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



@endsection
