@extends('layouts.master')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> Follow up</h4>
                    <h3 class="box-title">Today ({{ date('d-m-Y') }})</h3>
                </div>
            </div>
            <div class="panel-body">
                <table class="datatable table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Customer ID</th>
                        <th>Mobile</th>
                        <th>Company Name</th>
                        <th>Reason</th>
                        <th>Result</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tc_list as $index => $list)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $list->qb_serial }}</td>
                        <td>{{ $list->qb_mobile }}</td>
                        <td>{{ $list->qb_company_name }}</td>
                        <td>{{ $list->reas_info['sr_reason'] ?? 'N/A' }}</td>
                        <td>
                            @for ($i = 0; $i < $list->qb_result; $i++)
                                <b style="color: green; font-size: 20px;">*</b>
                            @endfor
                        </td>
                        <td>
                            @php
                                $customerTypes = [
                                    0 => 'Potential',
                                    1 => 'Potential',
                                    2 => 'Pipeline',
                                    3 => 'Regular'
                                ];
                                $customerColors = [
                                    0 => 'text-success',
                                    1 => 'text-success',
                                    2 => 'text-warning',
                                    3 => 'text-primary'
                                ];
                            @endphp
                            <span class="{{ $customerColors[$list->qb_cus_type] ?? 'text-secondary' }}">
                                {{ $customerTypes[$list->qb_cus_type] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ url('/client_feedback', ['id' => $list->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a>
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
