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
    $total_credit=0;
    $total_debit=0;
    $total_balance=0;
    $countData=$inv_pros->count();       
    $slno=0;      
@endphp

<center>
    @foreach($inv_pros as $inv_pro)
    @if($slno==0)
            <hr style="margin-top: 25px;">
                <h1 > <img src="{{ asset('/asset/image/')}}/{{ Auth::user()->au_company_img }}" style="height: 50px; width:50px;">
                  {{ Auth::user()->au_company_name}} 
                </h1>
                <h3 style="margin-top: -10px;">
                  {{Auth::user()->au_address}}
                  <br>
                  Mobile: {{Auth::user()->au_mobile}}
                </h3>
               <hr >
               <center>
               <div  style="margin-top: -10px;">
                        <h2>
                          Accounts Statement
                        </h2>
                </div>
                <h3>
                 <table>
                          <tr>
                            <td>
                               <b>Name:</b> 
                              {{App\Inv_supplier::getSupplierNameById(request()->supplier_id)->inv_sup_person}}
                              <b> Mobile:</b> 
                              {{App\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_mobile}}
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <b>Address:</b>
                               {{App\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_address}}
                            </td>
                          </tr>
                        </table>
                   </div>
                 </h3>
              </center>
                     <div >
                          <h4><b>
                             {{ App\Inv_supplier::getSupplierCompany(request()->supplier_id)->inv_sup_person }}'s
                          </b> Total Discount is {{App\Inv_product_inventory::getTotalDiscountBySupplierID(request()->supplier_id)}} Tk.
                        </h4>
                      </div>

                       <table class="table">
                          <thead>
                          <tr>
                            <th style="text-align: center; font-weight: bolder;">SL</th>
                            <th style="text-align: center; font-weight: bolder;">Issue Date</th>
                            <th style="text-align: center; font-weight: bolder;">Invoice No</th>
                            <th style="text-align: center; font-weight: bolder;">Description</th>
                            
                            <th style="text-align: center; font-weight: bolder;">Debit</th>
                            <th style="text-align: center; font-weight: bolder;">Credit</th>
                            <th style="text-align: center; font-weight: bolder;">Balance</th>
                         </tr>
                      </thead>
                       <tbody>
               @endif
    @php
          --$countData;
          ++$slno;
          $total_credit+=App\Inv_product_inventory::getCreditByInvoiceNo($inv_pro->inv_pro_inv_invoice_no);
            $total_debit+=App\Inv_product_inventory::getDebitByInvoiceNo($inv_pro->inv_pro_inv_invoice_no);
    @endphp 


                        <tr>
                            <td style="text-align: center;">
                              {{ ++$sl }}
                            </td>
                            <td style="text-align: center;">
                              {{ $inv_pro->inv_pro_inv_issue_date }}
                            </td>
                            <td>
                              {{$inv_pro->inv_pro_inv_invoice_no}}
                            </td>
                            <td >
                              {{$inv_pro->inv_pro_inv_tran_desc}}
                            </td>
                            
                            <td style="text-align: right;">
                              {{ number_format(App\Inv_product_inventory::getDebitByInvoiceNo($inv_pro->inv_pro_inv_invoice_no),2)}}
                            </td>
                            <td style="text-align: right;">
                              {{ number_format(App\Inv_product_inventory::getCreditByInvoiceNo($inv_pro->inv_pro_inv_invoice_no),2)}}
                            </td>
                            <td style="text-align: right;">
                              {{ number_format((App\Inv_product_inventory::getCreditByInvoiceNo($inv_pro->inv_pro_inv_invoice_no)) - (App\Inv_product_inventory::getDebitByInvoiceNo($inv_pro->inv_pro_inv_invoice_no)),2) }}
                            </td>
                          </tr>



                @if($slno%10==0 && $countData != 0)
                         </tbody>
                         </table>
                       <div style="margin-top: 80px; ">
                           <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                             Powered By IGL Web Ltd | +880-1958-666999
                          </span>
                      </div>
                         <div class="page-break"></div>
                    <hr style="margin-top: 25px;">
                         <h1 > 
                          <img src="{{ asset('/asset/image/')}}/{{ Auth::user()->au_company_img }}" style="height: 50px; width:50px;">
                          {{ Auth::user()->au_company_name}} 
                        </h1>
                        <h3 style="margin-top: -10px;">
                          {{Auth::user()->au_address}}
                          <br>
                          Mobile: {{Auth::user()->au_mobile}}
                        </h3>
                        <hr >
                        <table class="table" style="margin-top: 100px;">
                          <thead>
                          <tr>
                            <th style="text-align: center; font-weight: bolder;">SL</th>
                            <th style="text-align: center; font-weight: bolder;">Issue Date</th>
                            <th style="text-align: center; font-weight: bolder;">Invoice No</th>
                            <th style="text-align: center; font-weight: bolder;">Description</th>
                            
                            <th style="text-align: center; font-weight: bolder;">Debit</th>
                            <th style="text-align: center; font-weight: bolder;">Credit</th>
                            <th style="text-align: center; font-weight: bolder;">Balance</th>
                         </tr>
                      </thead>
                    <tbody>
                  @endif

                  @if($countData==0 )
                           </tbody>
                          <tfoot>
                            <tr>
                              <td style="text-align: center;">#</td>
                              <td colspan="3" style="text-align:right; font-weight: bolder;">Total:</td>
                              
                              <td style="font-weight: bolder; text-align: right;">
                                {{number_format($total_debit,2)}}
                              </td>
                              <td style="font-weight: bolder; text-align: right;">
                                {{number_format($total_credit,2)}}
                              </td>
                              <td style="font-weight: bolder; text-align: right;">
                                {{number_format(($total_credit-$total_debit),2)}}
                              </td >
                              
                            </tr>
                          </tfoot>
                           </table>
                      <div style="margin-top: 80px; ">
                   <span style="font-weight: bolder; font-style:  'Comic Sans MS';  font-size: 16px;">
                     Powered By IGL Web Ltd | +880-1958-666999
                   </span>
                 </div>
                   @endif

  @endforeach
</center>

</body>
</html>