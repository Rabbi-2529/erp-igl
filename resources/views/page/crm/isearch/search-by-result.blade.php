@extends('layouts.master')

@section('title', 'Search By Result')

@section('customer_relation_class', 'active')
@section('customer_class', 'active')
@section('intelligent_search_class', 'active')
@section('search_result_class', 'active')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Search By Result</h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="box-header">
                    <form class="form-inline" action="">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group col-sm-12">
                                    <label for="inputReason" class="col-sm-3 control-label">Client Response:</label>
                                    <div class="col-sm-5">
                                        <select class="form-control" name="result">
                                            <option value="">Select</option>
                                            <option value="1" {{ ((request()->result == 1) ? 'selected' : '') }} style="color: green; font-size: 25px;">*</option>
                                            <option value="2" {{ ((request()->result == 2) ? 'selected' : '') }} style="color: green; font-size: 25px;">**</option>
                                            <option value="3" {{ ((request()->result == 3) ? 'selected' : '') }} style="color: green; font-size: 25px;">***</option>
                                            <option value="4" {{ ((request()->result == 4) ? 'selected' : '') }} style="color: green; font-size: 25px;">****</option>
                                            <option value="5" {{ ((request()->result == 5) ? 'selected' : '') }} style="color: green; font-size: 25px;">*****</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                       {{-- Blank --}}
                                    </div>
                                </div>
                            </div>
                
                            <div class="col-sm-6">
                                <div class="form-group col-sm-3">
                                    <input type="text" class="form-control datepicker" name="from" data-date-format="yyyy-mm-dd" autocomplete="off" placeholder="From Date">
                                </div>
                                <div class="form-group col-sm-3">
                                    <input type="text" class="form-control datepicker" name="to" data-date-format="yyyy-mm-dd" autocomplete="off" placeholder="To Date">
                                </div>

                                <div class="form-group col-sm-4" id="user-select" style="display: none;">
                                    <label for="inputReason" class="col-sm-3 control-label">Entry:</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="user">
                                            <option value="">Select User</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->au_id }}">{{ $user->au_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="submit" class="btn btn-info" name="search_result" value="Search">
                            </div>
                        </div>
                    </form>
                </div>
                
                <br>
                <hr>
                
                <div class="box-body">
                    <table class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th style="width: 15px;" class="text-center">SL</th>
                                <th style="width: 60px;" class="text-center">Cus ID</th>
                                <th style="width: 70px;" class="text-center">Mobile</th>
                                <th class="text-center">Cus Name</th>
                                <th style="width: 150px;" class="text-center">Company Name</th>
                                <th style="width: 200px;" class="text-center">Location</th>
                                <th class="text-center">Reason</th>
                                <th style="width: 70px;" class="text-center">Result</th>
                                <th style="width: 60px;" class="text-center">Submit</th>
                                <th style="width: 55px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($find_users as $fu)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $fu->qb_serial }}</td>
                                <td class="text-center">{{ $fu->qb_mobile }}</td>
                                <td>{{ $fu->qb_name }}</td>
                                <td class="text-center">{{ $fu->qb_company_name }}</td>
                                <td>{{ $fu->qb_address }}</td>
                                <td class="text-center">{{ $fu->reas_info['sr_reason'] }}</td>
                                <td class="text-center">
                                    @for($i=0; $i<$fu->qb_result; $i++)
                                    <b style="color: green; font-size: 20px;">*</b>
                                    @endfor
                                </td>
                                <td class="text-center">{{ $fu->qb_submit_date }}</td>
                                <td class="text-center"><a href="{{ url('/client_feedback',['id' => $fu->qb_id]) }}" target="_blank" class="btn btn-info btn-sm">Follow Up</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            daysOfWeekHighlighted: "7",
            autoclose: true,
            todayHighlight: true
        });

        var userType = {{ Auth::user()->au_user_type }};
        if(userType == 4) {
            $('#user-select').show(); // Show user selection if user type is 4
        } else {
            $('#user-select').hide(); // Hide user selection for other types
        }
    });
</script>

@endsection
