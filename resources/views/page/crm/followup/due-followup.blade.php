@extends('layouts.master')
@section('title', 'Due Followup')

@section('customer_relation_class','active')
@section('followup_class','active')



@if (Auth::user()->au_user_type == 5 || Auth::user()->au_user_type == 6)
    @section('followup_class', 'active')
@endif

@section('due_followup_class', 'active')

@section('content')
<section class="content">
    @if(session()->has('confirm'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('confirm') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session()->has('sub_err'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('sub_err') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <section class="content-header">
        <div class="box">
            <div class="box-header">
                <form action="" method="get">
                    @csrf
                    <div class="col-xs-3">
                        <input type="text" name="start_date" value="{{ request()->get('start_date', $today) }}" class="form-control" id="start_date" placeholder="Enter Date">
                    </div>
                    <div class="col-xs-3">
                        <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                    </div>
                </form>
            </div>
            <!-- /.box-header -->
            <div class="box-body"></div>
        </div>
        <br><br><br>
        <h1>Due Follow Up of {{ Auth::user()->au_name }} {{ (Auth::user()->au_user_type == 5) ? ' And His Team' : '' }}</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Report</h4>
                    </div>
                </div>

                <div class="panel-body">
                    <table class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Entry By</th>
                                <th>Mobile</th>
                                <th>Cus Name</th>
                                <th>Company Name</th>
                                <th>Location</th>
                                <th>Reason</th>
                                <th>Result</th>
                                <th>Type</th>
                                <th>Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sl = 0 @endphp
                            @foreach($due_clients as $client)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>
                                    @if(empty($client->cus_mobile['qb_staff_id']) || $client->cus_mobile['qb_staff_id'] == 0)
                                        {{ $client->cus_mobile->entry_info['au_name'] }}
                                    @else
                                        {{ $client->cus_mobile->entry_staff['au_name'] }}<br>( Admin Reference )
                                    @endif
                                </td>
                              <td>
                                    <a href="{{ url('/client_feedback', ['id' => $client->cus_mobile['qb_id']]) }}"
                                       title="{{ $client->cus_mobile['qb_company_name'] }}" target="_blank">
                                        {{ maskNumber($client->cus_mobile['qb_mobile'] ?? '') }}
                                        {{ maskNumber($client->cus_mobile['qb_mobile1'] ?? '') }}
                                        {{ maskNumber($client->cus_mobile['qb_mobile2'] ?? '') }}
                                    </a>
                                </td>
                                <td>{{ $client->cus_mobile['qb_name'] }}</td>
                                <td>{{ $client->cus_mobile['qb_company_name'] }}</td>
                                <td>{{ $client->cus_mobile['qb_address'] }}</td>
                                <td>{{ @$client->cus_mobile->reas_info['sr_reason'] ?? 'N/A' }}</td>
                                <td>
                                    @for($i = 1; $i <= $client->cf_result; ++$i)
                                        <b style="color: green; font-size: 20px;">*</b>
                                    @endfor
                                </td>
                                <td>
                                    @if ($client->cus_mobile['qb_cus_type'] == 1 || $client->cus_mobile['qb_cus_type'] == 0)
                                        <span class="text-success">Potential</span>
                                    @elseif($client->cus_mobile['qb_cus_type'] == 2)
                                        <span class="text-warning">Pipeline</span>
                                    @elseif($client->cus_mobile['qb_cus_type'] == 3)
                                        <span class="text-primary">Regular</span>
                                    @endif
                                </td>
                                <td>{{ $client->feedbackinfo['fm_msg'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="sellReports" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Invoice Details</h4>
                </div>
                <div class="modal-body" id="sellModalDetails"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .action_btn {
        border: 1px solid;
        padding: 5px;
    }
    .btn_show {
        background-color: green;
        color: white;
    }
    .btn_delete {
        background-color: red;
        color: white;
    }
    .btn_edit {
        background-color: cornflowerblue;
        color: white;
    }
</style>
@endsection

@section('custom_script')
<!-- Include jQuery -->

<!-- Include Bootstrap Datepicker CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    $("#start_date").datepicker({
        format: 'yyyy-mm-dd', // Set the format here
        todayHighlight: true,
        autoclose: true
    });

    $("#end_date").datepicker({
        format: 'yyyy-mm-dd', // Set the format here
        todayHighlight: true,
        autoclose: true
    });

    $("#to").datepicker({
        format: 'yyyy-mm-dd', // Set the format here
        todayHighlight: true
    });
});
</script>
@endsection
