<!DOCTYPE html>
<html>
<head>
  <title>Report</title>

   <style type="text/css">
     .table {
              width: 100%;
          }

          .table th{
              text-align: center;
              font-weight: bolder;
          }

          .table {
              border-collapse: collapse;
              
              display: table;

          }
          .table tr{
            
          }
          .table th, .table td {
              border: 1px solid black;
              font-weight: normal;
              padding: 5px 3px;
              font-size: 16px;
          }
          
  .page-break {
  page-break-after: always;
  }
  /*
  @
  page{
    size: 21.59cm 27.94cm ;
  }*/
  </style>
</head>
<body>
  @php
   $sl=0;
    $totalCredit =0;
    $totalDebit=0;
    $totalBalance=0;
    $countData=$ledgers->count();       
    $slno=0;      
@endphp

<center>

 @foreach($ledgers as $ledger)
@if($slno==0)
  <hr style="margin-top: 5px;">
                <h1 > <img src="{{ asset('/asset/image/')}}/{{ Auth::user()->au_company_img }}" style="height: 50px; width:50px;">
                  {{ Auth::user()->au_company_name}} 
                </h1>
                <h3 style="margin-top: -10px;">
                  {{Auth::user()->au_address}}
                  <br>
                  Mobile: {{Auth::user()->au_mobile}}
                </h3>
               <hr >
 <div style="margin-top: 15px;">
  <table style="width: 100%;" >
  <tr>
    <td>
      <b> Company Name: </b>
         <span>
           {{ App\Inv_supplier::getSupplierCompany(request()->supid)->inv_sup_com_name}}
         </span><br>
      <b>Period: </b>
        <span>
           {{request()->sdate}} To {{request()->edate}}
        </span>
     </td>
    <td colspan="3">
      <b>General Ledger<br>(Account Wise)</b>
    </td>
    <td>
        <b>Date: </b>
            <span>
               {{date('l, d-m-Y')}}
             </span><br>
        <b>Time: </b>
            <span>
              {{date('h:i:s A')}}
            </span>
    </td>
  </tr>
</table>

<table class="table" >
  <tr>

    <th style="font-weight: bolder;">Voucher Date</th>
    <th style="font-weight: bolder;">Narration</th>
    <th style="font-weight: bolder;">Cheque No</th>

    <th style="font-weight: bolder;">Credit</th>
    <th style="font-weight: bolder;">Debit</th>
    <th style="font-weight: bolder;">Running Balance</th>
  </tr>
  <!--=========== Openning Balance================-->
  <tr >
    <td colspan="2" >
      <b>Account Name:</b>


      {{App\Inv_supplier::getSupplierNameByID(request()->supid)->inv_sup_person}}
    </td>
    <td colspan="3" style="text-align: center;">
      Openning Balance as on 

      {{request()->sdate}}
    </td>
    <td style="text-align: right;">
      {{App\Inv_product_inventory::getOpenningBalance(request()->sdate,request()->supid)}}
    </td>
  </tr>
@endif
  <!--=========== End Opening Openning Balance================-->

  @php

  $slno++;
  $countData--;
  $totalCredit+=App\Inv_product_inventory::getCreditForLedgerByInvoice($ledger->inv_pro_inv_invoice_no);
  $totalDebit+=App\Inv_product_inventory::getDebitForLedgerByInvoice($ledger->inv_pro_inv_invoice_no);
  $totalBalance+=App\Inv_product_inventory::getRunningBalanceByDate($ledger->inv_pro_inv_issue_date,request()->supid);
  @endphp

  <tr>

    <td>
      {{$ledger->inv_pro_inv_issue_date}}
    </td>
    <td>
      {{$ledger->inv_pro_inv_tran_desc}}
    </td>
    <td>
      {{$ledger->inv_pro_inv_invoice_no}}
    </td>
    <td style="text-align: right;">
      {{App\Inv_product_inventory::getCreditForLedgerByInvoice($ledger->inv_pro_inv_invoice_no)}} CR
    </td>
    <td style="text-align: right;">
      {{App\Inv_product_inventory::getDebitForLedgerByInvoice($ledger->inv_pro_inv_invoice_no)}} DR
    </td>
    <td style="text-align: right;">
      {{ number_format(App\Inv_product_inventory::getRunningBalanceByDate($ledger->inv_pro_inv_issue_date,request()->supid),2 )}}
    </td>

  </tr>
  @if($countData!=0 &&  $slno%20==0)
  </table>
  <div style="margin-top: 20px; ">
                   <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                     Powered By IGL Web Ltd | +880-1958-666999
                   </span>
  </div>
<div class="page-break"></div>

<hr style="margin-top: 5px;">
                <h1 > <img src="{{ asset('/asset/image/')}}/{{ Auth::user()->au_company_img }}" style="height: 50px; width:50px;">
                  {{ Auth::user()->au_company_name}} 
                </h1>
                <h3 style="margin-top: -10px;">
                  {{Auth::user()->au_address}}
                  <br>
                  Mobile: {{Auth::user()->au_mobile}}
                </h3>
 <hr >
   <div style="margin-top: 15px;">     
<table style="width: 100%;" >
  <tr>
    <td>
      <b> Company Name: </b>
         <span>
           {{ App\Inv_supplier::getSupplierCompany(request()->supid)->inv_sup_com_name}}
         </span><br>
      <b>Period: </b>
        <span>
           {{request()->sdate}} To {{request()->edate}}
        </span>
     </td>
    <td colspan="3">
      <b>General Ledger<br>(Account Wise)</b>
    </td>
    <td>
        <b>Date: </b>
            <span>
               {{date('l, d-m-Y')}}
             </span><br>
        <b>Time: </b>
            <span>
              {{date('h:i:s A')}}
            </span>
    </td>
  </tr>
</table>
</div>
  <table class="table" style="margin-top: 10px;">
  <tr>
    <th style="font-weight: bolder;">Voucher Date</th>
    <th style="font-weight: bolder;">Narration</th>
    <th style="font-weight: bolder;">Cheque No</th>

    <th style="font-weight: bolder;">Credit</th>
    <th style="font-weight: bolder;">Debit</th>
    <th style="font-weight: bolder;">Running Balance</th>
  </tr>

@endif

@if($countData==0)
  <tr>
    <td style="text-align: center; font-weight: bolder;">
      #
    </td>
    <td style="text-align: right; font-weight: bolder;" colspan="2">
      Total:
    </td>
    <td style="text-align: right; font-weight: bolder;">
      {{number_format($totalCredit,2)}}
    </td>
    <td style="text-align:right; font-weight: bolder;">
      {{number_format($totalDebit,2)}}
    </td>
    <td style="text-align: right;font-weight: bolder;">
      {{number_format($totalBalance,2)}}
    </td>
  </tr>
</table>
 <div style="margin-top: 20px; ">
                   <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                     Powered By IGL Web Ltd | +880-1958-666999
                   </span>
  </div>
@endif
@endforeach
</center>
</body>
</html>
<script type="text/javascript">
  window.print();
</script>