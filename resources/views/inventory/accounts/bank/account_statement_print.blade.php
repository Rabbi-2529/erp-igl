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
    $countData=$statements->count();       

    $slno=0;    
    $totalBalance=App\Models\Inv_acc_bank_statement::getOpenningBalance(request()->sdate);
    // Header logo, embedded so the PDF shows it (dompdf can't load it by URL)
    $logoPath = base_path('assets/img/icons/logo.png');
    $companyLogo = is_file($logoPath) ? 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath)) : null;  
@endphp

<center>

 @foreach($statements as $statement)
@if($slno==0)
<center>
  <hr style="margin-top: 5px;">
                <h1 > @if($companyLogo)<img src="{{ $companyLogo }}" style="height: 40px; width: auto; vertical-align: middle;">@else
                  {{ Auth::user()->au_company_name}} 
                  @endif
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
      <b>From: </b>
         <span>
             {{request()->sdate}} 
         </span><br>
      <b>To: </b>
        <span>
          {{request()->edate}}
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
   <th style="font-weight: bolder;">Date</th>
    <th style="font-weight: bolder;">Details</th>
    <th style="font-weight: bolder;">Bank</th>

    <th style="font-weight: bolder;">Debit</th>
    <th style="font-weight: bolder;">Credit</th>
    <th style="font-weight: bolder;"> Balance</th>
  </tr>
  <!--=========== Openning Balance================-->
  <tr >
    <td style="text-align: center; white-space: nowrap;" >
      {{request()->sdate}}
     </td>
      
    <td colspan="4" style="text-align: center;">
      Openning Balance as on 

      {{request()->sdate}}
    </td>
    <td style="text-align: right;">
      {{number_format(App\Models\Inv_acc_bank_statement::getOpenningBalance(request()->sdate),2)}}
    </td>
  </tr>
@endif
  <!--=========== End Opening Openning Balance================-->

  @php

  $slno++;
  $countData--;
  $totalCredit+=$statement->inv_abs_credit;
  $totalDebit+=$statement->inv_abs_debit;
  $totalBalance+=$statement->inv_abs_credit-$statement->inv_abs_debit;
  
  @endphp

  <tr>
      <td style="text-align: center; white-space: nowrap;">
        {{ \Carbon\Carbon::parse($statement->inv_abs_transaction_date)->format('Y-m-d') }}
      </td>
      <td>
        {{$statement->inv_abs_description}}
      </td>
      <td>
        @php($bank_info = App\Models\Inv_acc_bank_statement::get_Bank_Name_By_Bank_ID($statement-> inv_abs_bank_id) )
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
  @if($countData!=0 &&  $slno%20==0)
  </table>
  <div style="margin-top: 20px; ">
                   <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                     Powered By IGL Web Ltd | +880-1958-666999
                   </span>
  </div>
<div class="page-break"></div>

<hr style="margin-top: 5px;">
                <h1 > @if($companyLogo)<img src="{{ $companyLogo }}" style="height: 40px; width: auto; vertical-align: middle;">@else
                  {{ Auth::user()->au_company_name}} 
                  @endif
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
      <b> From: </b>
         <span>
            {{request()->sdate}}
         </span><br>
      <b>To: </b>
        <span>
            {{request()->edate}}
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
   <th style="font-weight: bolder;">Date</th>
    <th style="font-weight: bolder;">Details</th>
    <th style="font-weight: bolder;">Bank</th>

    <th style="font-weight: bolder;">Debit</th>
    <th style="font-weight: bolder;">Credit</th>
    <th style="font-weight: bolder;"> Balance</th>
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
 <div style="margin-top: 20px; ">
                   <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                     Powered By IGL Web Ltd | +880-1958-666999
                   </span>
  </div>
@endif
@endforeach

</body>
<script type="text/javascript">
  window.print();
</script>
</html>
</center>
