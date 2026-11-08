@extends('layouts.master')
@section('content')
@section('title', 'Sales Visit')

@section('customer_relation_class','active')
@section('visit_class','active')
@section('assign_visit_class','active')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> Sales Visit Request</h4>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('sales-visit-store') }}">
                    @csrf
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10px;">SL</th>
                                <th style="width: 180px;">Client</th>
                                <th style="width: 70px;">Mobile</th>
                                <th style="width: 220px;">Follow-up</th>
                                <th style="width: 200px;">Reason</th>
                                <th style="width: 150px;">Schedule</th>
                                <th>Client Note</th>
                                <th style="width: 200px;">Assign Visit</th>
                                <th style="width: 150px;">Visit Date</th>
                            </tr>
                        </thead>
                        @php($sl=0)
                        <tbody>
                            @foreach($clientFeedPending as $feedback)
                                    <tr>
                                        {{-- Serial --}}
                                        <td>{{ ++$sl }}</td>

                                        <input type="text" name="cf_id[]" value="{{ $feedback->cf_id }}" hidden>

                                        {{-- Client Name --}}
                                        <td>
                                            @foreach($clientListPend as $client)
                                                @if($client->qb_id == $feedback->cf_qb_id)
                                                <input type="text" name="client_id[]" value="{{ $client->qb_id }}" hidden required>
                                                    {{ $client->qb_name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        
                                        {{-- Client Phone --}}
                                        <td>
                                            @foreach($clientListPend as $client)
                                                @if($client->qb_id == $feedback->cf_qb_id)
                                                    <a href="{{ url('/client_feedback',['qb_id' => $client->qb_id]) }}"
                                                    class="opener" 
                                                    target="_blank">
                                                    <input type="text" name="client_phone[]" value="{{ $client->qb_mobile }}" hidden required>
                                                    {{ $client->qb_mobile }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </td>

                                        {{-- Entry By --}}
                                        <td>
                                            @foreach($users as $user)
                                                @if($user->au_id == $feedback->cf_entry_by)
                                                    (<b>{{ $user->au_user_id }}</b>)
                                                    <input type="text" name="entry_by[]" value="{{ $user->au_user_id }}" hidden required>
                                                    {{ $user->au_name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        {{-- Reason --}}
                                        <td>
                                            @if(empty($feedback->cf_reason))
                                                NO VISIT REASON
                                            @else
                                                @foreach($reasonList as $reason)
                                                    @if($reason->sr_slid == $feedback->cf_reason)
                                                    <input type="text" name="reason[]" value="{{ $feedback->cf_reason }}" hidden required>
                                                        <b>{{ $reason->sr_reason }}</b>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        {{-- Visit Schedule --}}
                                        <td>
                                            {{ date('d-m-Y', strtotime($feedback->cf_next_date)) }}
                                        </td>                                    
                                        {{-- Client Note --}}
                                        <td>
                                            @if(empty($feedback->cf_client_message))
                                                N/A
                                            @else
                                                {{ $feedback->cf_client_message }}
                                            @endif
                                        </td>
                                        {{-- Visit User --}}
                                        <td>
                                            <select name="emp_id[]" class="form-control">
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->au_id }}">({{ $user->au_user_id }})- {{ $user->au_name }}</option>
                                                @endforeach
                                            </select>                                                          
                                        </td>

                                        <td>
                                            <input type="text" class="form-control visit-date-picker" name="visit_date[]" autocomplete="off" required>
                                        </td>
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary pull-right">Submit</button>               
                </form>

            </div>
        </div>

        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> Confirmed Visit</h4>
                </div>
            </div>
            <div class="panel-body">
                <table id="bottomTable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th style="width: 10px;">SL</th>
                            <th style="width: 200px;">Assigned User</th>
                            <th style="width: 70px;">Mobile</th>
                            <th>Client Name</th>
                            <th style="width: 210px;">Follow-up</th>
                            <th style="width: 120px;">Confirmed Visit</th>
                            <th style="width: 50px;">Action</th>
                        </tr>
                    </thead>

                    @php($sl=0)
                   
                    <tbody>
                        
                        @foreach($assignedVisits as $visit)
                        <tr>
                            <td>{{ ++$sl }}</td>

                            <td>
                                @foreach($users as $user)
                                    @if($user->au_id == $visit->emp_id)
                                        (<b>{{ $user->au_user_id }}</b>)
                                        {{ $user->au_name }}
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                @foreach($clientListAsgn as $client)
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
                                @foreach($clientListAsgn as $client)
                                    @if($client->qb_id == $visit->client_id)
                                        {{ $client->qb_name }}
                                    @endif
                                @endforeach
                            </td>

                            <td>
                                @foreach($users as $user)
                                    @if($user->au_user_id == $visit->entry_by)
                                        (<b>{{ $user->au_user_id }}</b>)
                                        {{ $user->au_name }}
                                    @endif
                                @endforeach
                            </td>

                            <td>{{ $visit->visit_date }}</td>
                            <td>
                                <!-- Edit Button -->
                                <a href="" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pencil"></i>
                                </a>
                        
                                <!-- Delete Button -->
                                <form action="{{ route('sales-visits-destroy', ['id' => $visit->id]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this visit?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('.visit-date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        minDate: 0
    });
});

</script>

@endsection