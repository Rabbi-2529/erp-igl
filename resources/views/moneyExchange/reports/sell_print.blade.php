<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}}</title>
    <style>
      .clearfix:after {
  content: "";
  display: table;
  clear: both;
}

a {
  color: #5D6975;
  text-decoration: underline;
}

body {
  position: relative;
  width: 21cm;  
  height: 29.7cm; 
  margin: 0 auto; 
  color: #001028;
  background: #FFFFFF; 
  font-family: Arial, sans-serif; 
  font-size: 12px; 
  font-family: Arial;
}

header {
  padding: 10px 0 0 0;
}

#logo {
  text-align: center;
  margin-bottom: 10px;
}

#logo img {
  width: 90px;
}

.header_company {
  /* border-top: 1px solid  #5D6975; */
  border-bottom: 1px solid  #5D6975;
  color: #5D6975;
  line-height: 1.4em;
  font-weight: normal;
  text-align: center;
  background: url(dimension.png);
}

#project {
  float: left;
}

#project span {
  color: #5D6975;
  text-align: right;
  width: 52px;
  margin-right: 10px;
  display: inline-block;
  font-size: 0.8em;
}

#company {
  /* float: right;
  text-align: right; */
}

#project div,
#company div {
  white-space: nowrap;        
}

table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
  margin-right: 500px;
}

table tr:nth-child(2n-1) td {
  background: #F5F5F5;
}

table th,
table td {
  text-align: center;
}

table th {
  padding: 5px 20px;
  color: #5D6975;
  border-bottom: 1px solid #C1CED9;
  white-space: nowrap;        
  font-weight: normal;
}
@page { 
  size: 20cm 30cm ; 
  }

table .service,
table .desc {
  text-align: left;
}

table td {
  padding: 5px;
  text-align: right;
}

table td.service,
table td.desc {
  vertical-align: top;
}

table td.unit,
table td.qty,
table td.total {
  font-size: 1.2em;
}

table td.grand {
  border-top: 1px solid #5D6975;;
}

#notices .notice {
  color: #5D6975;
  font-size: 1.2em;
}

footer {
  color: #5D6975;
  width: 100%;
  height: 30px;
  position: absolute;
  bottom: 0;
  border-top: 1px solid #C1CED9;
  padding: 8px 0;
  text-align: center;
}
.invoice{
    border-radius: 10px;
    border: 1px solid;
    background-color: lightblue;
    display: inline-block;
    /* margin-right: 70px; */
    padding: 6px 10px;
    
}
.qty{
  text-align: left;
}
.text-center{
    text-align: center;
}
#text {
  text-align: right;
  font-weight: bold;
}
#desc{
  text-align: left;
}
    </style>
  </head>
  <body>
    <header class="clearfix">
      <div class="header_company">
          <div class="col-md-4">
              @php($company_logo = App\Admin_user::company_logo(Auth::user()->au_company_id))
              <img src="{{ asset('/asset/image/')}}/{{ $company_logo->au_company_logo }}" style="float:left;height:115px; width:190px;">
          </div>
          <div class="col-md-8" style="margin-right:180px;">
            @php($company_info = App\Admin_user::company_info(Auth::user()->au_company_id))
            <h1>{{ Auth::user()->au_company_name }}</h1>
            <p>Address: {{ $company_info->au_address }}</p>
            <p>Mobile: {{ $company_info->au_mobile }}</p>
            <p>Email: {{ $company_info->au_email }}</p>
          </div>
      </div>
      <div class="text-center">
          <h3 class="invoice" style="margin: 3px;">Invoice/Bill</h3>
      </div>
      <div id="company" class="clearfix">
          <table class="table">
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Customer Name</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc" style="float:left; width: 280px;">{{ $invoice_detail->getCustomerInfo['inv_cus_name'] }}</td>
                  <td id="text" style="width:200px;">Invoice No</td>
                  <td>:</td>
                  <td id="desc" style="text-align:left;">{{ $invoice_detail->inv_pro_inv_invoice_no }}</td>
              </tr>
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Address</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc" style="float:left; width: 280px;">{{ $invoice_detail->getCustomerInfo['inv_cus_address'] }}</td>
                  <td id="text" style="width:200px;">Sold By</td>
                  <td>:</td>
                  <td id="desc" style="text-align:left;">{{ $invoice_detail->getSoldByInfo['au_name'] }}</td>
                  
              </tr>
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Mobile</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc" style="float:left; width: 280px;">{{ $invoice_detail->getCustomerInfo['inv_cus_mobile'] }}</td>
                  <td id="text" style="width:200px;">Date & Time</td>
                  <td>:</td>
                  <td id="desc" style="text-align:left;">{{ $invoice_detail->inv_pro_inv_submit_at }}</td>
              </tr>
          </table>
        {{-- <div>Invoice</div>
        <div>455 Foggy Heights,<br /> AZ 85004, US</div>
        <div>(602) 519-0450</div>
        <div><a href="mailto:company@example.com">company@example.com</a></div> --}}
      </div>
      {{-- <div id="project">
        <div><span>PROJECT</span> Website development</div>
        <div><span>CLIENT</span> John Doe</div>
        <div><span>ADDRESS</span> 796 Silver Harbour, TX 79273, US</div>
        <div><span>EMAIL</span> <a href="mailto:john@example.com">john@example.com</a></div>
        <div><span>DATE</span> August 17, 2015</div>
        <div><span>DUE DATE</span> September 17, 2015</div>
      </div> --}}
    </header>
    <main>
        <table border="1">
            <thead style="background-color:#aabbcc; font-size: 15px; ">
              <tr>
                <th class="service" style="text-align:center; width:5px; color:black; font-weight:bold;">Sl</th>
                <th style="color:black; font-weight:bold;">Description</th>
                <th style="width:5px; color:black; font-weight:bold;">Qty</th>
                <th style="width:5px; color:black; font-weight:bold;">Unit Price</th>
                <th style="width:5px; color:black; font-weight:bold;">Amount</th>
              </tr>
            </thead>
            <tbody>
              @php($sl=0)
              @php($balance=0)
              @php($total = 0)
              @php($discount=0)
              @php($delivery=0)
              @foreach ($invoice as $sell)
              @php($slno = App\Inv_product_inventory::ProductSerialSell($sell->inv_pro_inv_id))
              <tr>
                  <td class="service" style="text-align:center;">
                    {{ ($sl<10)?'0':'' }}{{ ++$sl }}
                  </td>
                  <td class="qty">
                    @if ($sell->inv_pro_inv_tran_type == 10)
                    {{ $sell->inv_pro_inv_tran_desc }} 
                    @elseif($sell->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
                    {{ $sell->getProductWarranty['inv_pro_det_pro_description'] }}
                    ({{ $sell->getProductWarranty['inv_pro_det_pro_id'] }})<br>
                    @else
                    {{ $sell->getProductWarranty['inv_pro_det_pro_description'] }}
                    ({{ $sell->getProductWarranty['inv_pro_det_pro_id'] }})<br>
                    {{ implode(', ', $slno) }}<br>
                    <b>Warranty: </b>{{ $sell->getProductWarranty['inv_pro_det_pro_warranty'] }} Days
                    @endif
                    
                  </td>
                  <td style="text-align:center;">{{ $sell->inv_pro_inv_qty }}</td>
                  <td class="total">{{ number_format($sell->inv_pro_inv_unit_price,2) }}</td>
                  <td class="total">{{ number_format($sell->inv_pro_inv_debit,2) }}</td>
              </tr>
              @php($balance = $balance + $sell->inv_pro_inv_debit)
              @endforeach
              <tr>
                <td colspan="4">Sub Total :</td>
              <td class="total">{{ number_format($balance,2) }}</td>
              </tr>
              @foreach ($services_delivery as $charge)
                  <tr>
                    @if($charge->inv_pro_inv_tran_type == 11)
                    @php($delivery = $charge->inv_pro_inv_debit)

                    <td colspan="4">Delivery Charges :</td>
                    <td class="total">
                      {{ number_format($delivery,2) }}
                    </td>

                    @php($balance = $balance + $charge->inv_pro_inv_debit)
                    @elseif($charge->inv_pro_inv_tran_type == 12)

                    <td colspan="4">Discount :</td>
                    @php($discount = $charge->inv_pro_inv_credit)
                    <td class="total">
                      {{ number_format($discount,2) }}
                    </td>
                    @php($balance = $balance - $discount)
                    @endif
                  </tr>
              @endforeach
              <tr>
                <td colspan="4" class="grand total">Net Payable Amount :</td>
                <td class="grand total">{{ number_format($balance,2) }}</td>
              </tr>
              
              <tr>
                  <td class="grand total" style="text-align: left;" colspan="5">In Word: {{ App\NumberConverter::number_to_text($balance) }}</td>
              </tr>
            </tbody>
          </table>
      <div id="notices" align="center" style="margin-top: 70px;">
        <table>
          <tr>
            <td style="text-align:left;">------------------------------</td>
            <td style="text-align:center;">------------------------------</td>
            <td>------------------------------</td>
          </tr>
          <tr>
            <td style="text-align:left;">Customer Signature</td>
            <td style="text-align:center;">Sales Person</td>
            <td>Authorized Signature</td>
          </tr>
        </table>
      </div>
    </main>
    <footer>
      Powered By: IGL Web Ltd. | +880-1958-666999
    </footer>
    <script>
       (function(){
        @if(session()->has('print_invoice'))
          window.print();
        @endif
        })();
     </script>
  </body>
</html>