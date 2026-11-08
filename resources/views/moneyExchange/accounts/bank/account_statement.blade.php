@extends('layouts.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('acc_state_class','active')
@section('content')
<section class="content">
        <section class="content-header">
          <div class="box">
              <div class="box-header">

                @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

           


                   
            <form action="" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token()   }}" id="_token">
            
             <div class="col-xs-3">
              <input type="text" name="start_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ request()->start_date }}" class="form-control" id="start_date" placeholder="Enter Start Date"  required>
             </div>
              <div class="col-xs-3">
              <input type="text" name="end_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ request()->end_date }}" class="form-control" id="end_date" placeholder="Enter End Date"  required>
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
            </section>
            
            @if(request()->has('searchbtn'))
            
            @php
              $totalDebit=0;
              $totalCredit=0;

              $totalBalance=App\Inv_acc_bank_statement::getOpenningBalance(request()->start_date);

            @endphp
              
              <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">
                      Account's Statement

                       </h3>
                   <form method="get" action="" target="_blank">
                         <button class="btn btn-warning" type="button" style="float: right; margin-top: -15px;margin-right: 5px;" id="print_btn"><i class="fa fa-print"></i> Print</button> 
                        
                        <button class="btn btn-primary" type="button" style="float: right; margin-top: -15px;margin-right: 5px;" id="download_btn"><i class="fa fa-download"></i> Download</button> 
                        
                        <input type="hidden" name="sdate" value="{{ request()->start_date}}" id="sdate">
                        <input type="hidden" name="edate" value="{{ request()->end_date}}" id="edate">
                       
                         
                       </form>
                      
                  
                        <hr>
                    </div>
                   
                    <!-- /.box-header -->
                    <div class="box-body">

                      <div class="col-sm-4">
                       <b>From: </b>
                       <span>{{request()->start_date}}</span><br>
                       <b>To: </b>
                       <span>{{request()->end_date}}</span>
                       
                      </div>
                      <div class="col-sm-4">
                        
                      </div>

                      <div class="col-sm-4">
                        <b>Date: </b>
                        <span>
                          {{date('l, d-m-Y')}}
                        </span><br>
                        <b>Time: </b>
                        <span>
                          {{date('h:i:s A')}}
                        </span>
                      </div>

                      <div class="col-sm-12" style="margin-top: 50px;">

                        <table class="table table-bordered table-striped table-hover datatable" style="border-style: none !important;">
                          <tr>
                            <th style="text-align: center;"> Date</th>
                            <th style="text-align: center;">Details</th>
                            <th style="text-align: center;">Bank</th>
                          
                            
                            <th style="text-align: center;">Debit</th>
                            <th style="text-align: center;">Credit</th>
                            <th style="text-align: center;">Running Balance</th>
                          </tr>
       <!--=========== Openning Balance================-->
                          <tr>
                           <td style="text-align: center;">
                             {{request()->start_date}}
                           </td>
                            <td colspan="4" style="text-align: center;font-weight: bold;">
                              Openning Balance as on 
                            
                              {{request()->start_date}}
                            </td>
                            <td style="text-align: right;">
                              {{number_format(App\Inv_acc_bank_statement::getOpenningBalance(request()->start_date),2)}}
                            </td>
                          </tr>

  <!--=========== End Opening Openning Balance================-->
               @foreach($statements as $statement)
            @php
                  $totalCredit+=$statement->inv_abs_credit;
                  $totalDebit+=$statement->inv_abs_debit;
                  $totalBalance+=$statement->inv_abs_credit-$statement->inv_abs_debit;
           @endphp
                      <tr>
                            <td style="text-align: center;">
                                {{$statement->inv_abs_transaction_date}}
                            </td>
                            <td>
                            {{$statement->inv_abs_description}}
                            </td>
                            <td>
                              @php($bank_info = App\Inv_acc_bank_statement::get_Bank_Name_By_Bank_ID($statement-> inv_abs_bank_id) )
                              {{ @$bank_info->bank_name }}

                            </td>
                            
                            <td style="text-align: right;">
                               {{number_format($statement->inv_abs_debit,2)}}
                            </td>
                            <td style="text-align: right;">
                             {{number_format($statement->inv_abs_credit,2)}}
                            </td>
                            <td style="text-align: right;">
                              {{ number_format($totalBalance,2) }}
                            </td>
                           
                          </tr>
                      @endforeach

                          <tr>
                            <td style="text-align: center; font-weight: bolder;">
                              #
                            </td>
                             <td style="text-align: right; font-weight: bolder;" colspan="2">
                              Total:
                            </td>
                             
                             <td style="text-align:right; font-weight: bolder;">
                             {{number_format($totalDebit,2)}}
                            </td>
                            <td style="text-align: right; font-weight: bolder;">
                                {{number_format($totalCredit,2)}}
                            </td>
                            <td style="text-align: right;font-weight: bolder;">
                             {{number_format($totalBalance,2)}}
                            </td>
                          </tr>
                        </table>
                     
                       
                      </div>
                      
                    </div>
                    <!-- /.box-body -->
                  </div>
              @endif
            
            </section>
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



$("#download_btn").click(function () {
            let sdate = $("#sdate").val();
            let edate = $("#edate").val();
            let _token=$("#_token").val();
          
            let route = "{{ route('accounts.account-statement-download')}}?sdate="+ sdate +"&edate="+edate+"&download_btn";
            window.open(route, '_blank');
        });


$("#print_btn").click(function () {
            let sdate = $("#sdate").val();
            let edate = $("#edate").val();
            let _token=$("#_token").val();
           
            let route = "{{ route('accounts.account-statement-download')}}?sdate="+ sdate +"&edate="+edate+"&print_btn";
            window.open(route, '_blank');
        });
</script>
@endsection