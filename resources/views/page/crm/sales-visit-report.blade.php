@extends('layouts.master')
@section('content')
@section('title', 'Visit Report')

@section('customer_relation_class','active')
@section('visit_class','active')
@section('visit_report_class','active')


<style>
    .status-badge {
        padding: 5px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: bold;
    }

    .incomplete {
        background-color: #dc3545;
        color: #fff;
    }

    .complete {
        background-color: #28a745;
        color: #fff;
    }

</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> Sales Visit Report</h4>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th style="width: 15px">SL</th>
                            <th>Follow-up By</th>
                            <th>Assigned Emp</th>
                            <th>Visit Date</th>
                            <th>Reason</th>
                            <th>Client Phone</th>
                            <th style="width: 380px">Address</th>
                            <th style="width: 50px">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sl = 0;
                        @endphp
                        @foreach($visitMonth as $visit)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>
                                    @foreach($users as $user)
                                        @if($user->au_user_id == $visit->entry_by)
                                            (<b>{{ $user->au_user_id }}</b>)
                                            {{ $user->au_name }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($users as $user)
                                        @if($user->au_id == $visit->emp_id)
                                            (<b>{{ $user->au_user_id }}</b>)
                                            {{ $user->au_name }}
                                        @endif
                                    @endforeach
                                </td>

                                <td>{{ $visit->visit_date }}</td>

                                <td>
                                    @if(empty($visit->reason))
                                        NO VISIT REASON
                                    @else
                                        @foreach($reasonList as $reason)
                                            @if($reason->sr_slid == $visit->reason)
                                                {{ $reason->sr_reason }}
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                
                                <td>
                                    @foreach($clientList as $client)
                                        @if($client->qb_id == $visit->client_id)
                                            <a href="{{ url('/client_feedback',['qb_id' => $client->qb_id]) }}"
                                            class="opener" 
                                            target="_blank">
                                            {{ $client->qb_mobile }}
                                            </a>
                                        @endif
                                    @endforeach
                                </td>

                                <td>
                                    @foreach($clientList as $client)
                                        @if($client->qb_id == $visit->client_id)
                                            {{ $client->qb_address }}
                                        @endif
                                    @endforeach
                                </td>

                                <td class="text-center">
                                    @if($visit->status == 0)
                                        <span class="status-badge incomplete">INCOMPLETE</span>
                                    @elseif($visit->status == 1)
                                        <span class="status-badge complete">COMPLETE</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> User Visit Summery</h4>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Follow-up By</th>
                            <th>Assigned Emp</th>
                            <th>Visit Date</th>
                            <th>Reason</th>
                            <th>Client Phone</th>
                            <th>Address</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sl = 0;
                        @endphp
                        @foreach($visitMonth as $visit)
                            @if($visit->status == 1)
                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>
                                        @foreach($users as $user)
                                            @if($user->au_user_id == $visit->entry_by)
                                                (<b>{{ $user->au_user_id }}</b>)
                                                {{ $user->au_name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($users as $user)
                                            @if($user->au_id == $visit->emp_id)
                                                (<b>{{ $user->au_user_id }}</b>)
                                                {{ $user->au_name }}
                                            @endif
                                        @endforeach
                                    </td>

                                    <td>{{ $visit->visit_date }}</td>

                                    <td>
                                        @if(empty($visit->reason))
                                            NO VISIT REASON
                                        @else
                                            @foreach($reasonList as $reason)
                                                @if($reason->sr_slid == $visit->reason)
                                                    {{ $reason->sr_reason }}
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @foreach($clientList as $client)
                                            @if($client->qb_id == $visit->client_id)
                                                <a href="{{ url('/client_feedback',['qb_id' => $client->qb_id]) }}"
                                                class="opener" 
                                                target="_blank">
                                                {{ $client->qb_mobile }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </td>

                                    <td>
                                        @foreach($clientList as $client)
                                            @if($client->qb_id == $visit->client_id)
                                                {{ $client->qb_address }}
                                            @endif
                                        @endforeach
                                    </td>

                                    <td></td>

                                </tr>
                            @else
                                <tr>
                                    <td class="text-center" colspan="8">No Data Available</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
