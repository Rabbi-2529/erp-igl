@extends('layouts.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('bank_class','menu-open')
@section('bank_list','active')
@section('content')
<section class="content">
        <section class="content-header">
            <div class="box">
                <div class="box-header">
                     
              <form action="" method="get">
                <input type="hidden" name="_token" value="" id="_token">
               <div class="col-xs-3">
                <input type="text" name="start_date" value="{{ $start_date }}" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="start_date" placeholder="Enter Start Date" >
               </div>
                <div class="col-xs-3">
                <input type="text" name="end_date" value="{{ $end_date }}" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="end_date" placeholder="Enter End Date" >
               </div>
                  <div class="col-xs-3">
                    <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                    {{-- <a href="{{ route('reports.sell-reports-download') }}" class="btn btn-warning">Download</a> --}}
                  </div>
                  </form>
                      </div>
                      <!-- /.box-header -->
                      <div class="box-body">
                   </div>
               </div>
              @if(Session::has('errmsg'))
              <div class="alert alert-danger alert-dismissible" role="alert">
              {{ session('errmsg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif
              @if(Session::has('msg'))
              <div class="alert alert-success alert-dismissible" role="alert">
              {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif
              
              <h1>
                {{ $bank_info->bank_info->bank_name }} ({{ $bank_info->inv_abi_account_no }})
              </h1>
        
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Bank List</h3>
                    </div>
                   
                    <!-- /.box-header -->
                    <div class="box-body">


                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Transaction Details</th>
                          <th>Debit</th>
                          <th>Credit</th>
                          <th class="text-center">Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @php($total_balance = 0)
                        @php($total_credit = 0)
                        @php($total_debit = 0)
                        @php($reference='')
                        
                      @foreach ($statements as $statement)
                       @php($person='')
                             @php($debit = $statement->inv_abs_debit)
                             @php($credit = $statement->inv_abs_credit)
                             @php($total_debit = $total_debit + $debit)
                             @php($total_credit = $total_credit + $credit)
                             @php($total_balance = $total_balance + $credit - $debit)
                        
                           @if($statement->inv_abs_reference_type==1)
                           @php($person='('.App\Inv_customer::getCustomerNameById($statement->inv_abs_reference_id).')')
                                @php($reference='Customer Transaction.')
                              
                                @elseif($statement->inv_abs_reference_type==2)
                                @php($person='('.App\Inv_supplier::getSupplierNameByID($statement->inv_abs_reference_id)->inv_sup_person.')')
                                  @php($reference='Supplier Transaction.')
                             
                                  @elseif($statement->inv_abs_reference_type==3||$statement->inv_abs_reference_type==4)
                                      @php($reference='Expenses')
                                    
                                  @elseif($statement->inv_abs_reference_type==5)
                                    @php($reference='Contra Transaction.')
                                  @elseif($statement->inv_abs_reference_type==6)
                                  @php($reference='Bank Withdraw.')
                                  @elseif($statement->inv_abs_reference_type==7)
                                  @php($reference='Bank Deposit.')
                                @endif
                            <tr>
                                <td class="text-center">{{ ++$sl }}</td>
                                <td style="width: 100px;">{{ $statement->inv_abs_transaction_date->format('d-m-Y') }}</td>
                                <td>
                                  {{ $statement->inv_abs_description }} | {{ $reference }} {{$person}} 
                                </td>
                               
                                <td style="text-align: right;">{{ number_format($debit,2) }}</td>
                                 <td style="text-align: right;">{{ number_format($credit,2) }}</td>
                                <td style="text-align: right;">{{ number_format($total_balance,2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                                
                                <td style="text-align: right; font-weight: bold;">{{ number_format($total_debit,2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ number_format($total_credit,2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ number_format($total_balance,2) }}</td>
                            </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
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
<script>
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
</script>
@endsection