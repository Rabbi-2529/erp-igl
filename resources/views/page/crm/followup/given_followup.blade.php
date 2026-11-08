@extends('layouts.master')
@section('title', 'Follow Up')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Given Followup Today</h4>
                    <h3 class="box-title">Today ({{ date('d-m-Y') }})</h3>
                </div>
            </div>
            <div class="panel-body">
                <table class="datatable table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Entry By</th>
                            <th>Mobile</th>
                            <th>Result</th>
                            <th>Type</th>
                            <th>Feedback</th>
                            <th>Next Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($given_fu as $index => $given)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $given->cf_entry_by == 0 ? 'Admin' : $given->feedback_info['au_name'] }}</td>
                            <td>
                                <a href="{{ url('/client_feedback', ['id' => $given->cf_qb_id]) }}" target="_blank" style="text-decoration: none;">
                                    {{ $given->cus_mobile['qb_mobile'] }}
                                </a>
                            </td>
                            <td>
                                @for ($i = 0; $i < $given->cf_result; $i++)
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
                                <span class="{{ $customerColors[$given->cus_mobile['qb_cus_type']] }}">
                                    {{ $customerTypes[$given->cus_mobile['qb_cus_type']] }}
                                </span>
                            </td>
                            <td>{{ $given->feedback_info['fm_msg'] ?? 'N/A' }}</td>
                            <td>{{ $given->cf_next_date ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
