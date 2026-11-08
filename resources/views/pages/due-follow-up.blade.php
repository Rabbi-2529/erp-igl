@extends('layout.master')
@section('sds_menu_class','menu-open')
@if (Auth::user()->au_user_type == 5 ||Auth::user()->au_user_type == 6)
@section('followup_class','menu-open')
@endif
@section('due_follow_up','active')
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
              <input type="hidden" name="_token" value="" id="_token">
             <div class="col-xs-3">
              <input type="text" name="start_date" value="{{ $today }}" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="start_date" placeholder="Enter Date" >
             </div>
                <div class="col-xs-3">
                  <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                </div>
                </form>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                 </div>
             </div>
              <h1>
                   Due Follow Up of {{ Auth::user()->au_name }} {{ (Auth::user()->au_user_type == 5)? ' And His Team' : '' }}
              </h1>

            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title"> Reports</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example1" class="table table-bordered table-striped">
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
                                @php
                                ($sl=0)
                                @endphp
                                @foreach( $due_clients as $today )
                                <tr>
                                  <td>{{ ++$sl }}</td>
                                  @if( ($today->cus_mobile['qb_staff_id'] == '') ||  ($today->cus_mobile['qb_staff_id'] == 0))
                                  <td>{{ $today->cus_mobile->entry_info['au_name'] }}</td>
                                  @else
                                  <td>{{ $today->cus_mobile->entry_staff['au_name'] }}<br>( Admin Reference )</td>
                                  @endif
                                 <td>
                                     <a href="{{ url('/client_feedback',['id' => $today->cus_mobile['qb_id']]) }}" title="{{ $today->cus_mobile['qb_company_name'] }}" class="opener" target="_blank">
                                         {{ $today->cus_mobile['qb_mobile'] }}  {{ $today->cus_mobile['qb_mobile1'] }}{{ $today->cus_mobile['qb_mobile2'] }}
                                     </a>
                                 </td>
                                 <td>{{ $today->cus_mobile['qb_name'] }}</td>
                                 <td>{{ $today->cus_mobile['qb_company_name'] }}</td>
                                  <td>{{ $today->cus_mobile['qb_address'] }}</td>

                                  <td>{{ @$today->cus_mobile->reas_info['sr_reason'] }}</td>
                                  <td>
                                    @for( $i=1; $i<=$today->cf_result; ++$i )
                                      <b style="color: green; font-size: 20px;">*</b>
                                    @endfor
                                  </td>
                                  <td>
                                       @if ($today->cus_mobile['qb_cus_type'] == 1 || $today->cus_mobile['qb_cus_type'] == 0)
                                            <span class="text-success">Potential</span>
                                         @elseif($today->cus_mobile['qb_cus_type'] == 2)
                                            <span class="text-warning">Pipeline</span>
                                         @elseif($today->cus_mobile['qb_cus_type'] == 3)
                                            <span class="text-primary">Regular</span>
                                         @endif
                                  </td>
                                  <td>{{ $today->feedbackinfo['fm_msg'] }}</td>

                                </tr>
                                @endforeach
                            </tbody>
                          </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>


<!-- Modal -->
<div class="modal fade" id="sellReports" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Invoice Details</h4>
      </div>
      <div class="modal-body" id="sellModalDetails">


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>



@endsection
@section('custom_style')
    <style>
        .action_btn{
            border: 1px solid;
            padding: 5px;
        }
        .btn_show{
            background-color: green;
            color: white;
        }
        .btn_delete{
            background-color: red;
            color: white;
        }
        .btn_edit{
            background-color: cornflowerblue;
            color: white;
        }
    </style>
 @endsection

@section('custom_script')
<script type="text/javascript">

//InitializeDate();
/*
function InitializeDate() {
    var date = new Date();
    var dd = date.getDate();
    var mm = date.getMonth() + 1;
    var yyyy = date.getFullYear();

    var ToDate = yyyy+'-'+mm+'-'+dd;
    var FromDate = yyyy+'-'+(mm-1)+'-'+dd;
    $('#start_date').datepicker('setDate', FromDate);
    $('#end_date').datepicker('setDate', ToDate);
}*/

$(document).ready(function(){

$( "#start_date" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });


$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});

$(document).ready(function(){

$( "#end_date" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});


</script>

@endsection
