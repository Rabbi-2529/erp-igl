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

/* header {
  padding: 10px 0;
  margin-bottom: 30px;
} */


#logo {
  text-align: center;
  margin-bottom: 10px;
}

#logo img {
  width: 90px;
}

.header_company {
  border-top: 1px solid  #5D6975;
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
  /* float: right; */
  text-align: right;
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
    background-color: lightblue;
    display: inline-block;
    /* margin-right: 70px; */
    padding: 10px;
}
.text-center{
    text-align: center;
}
#text {
  text-align: right;
  font-weight: bold;
}
.qty{
  text-align: left;
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
              <img src="{{ asset('/asset/image/')}}/{{ $company_logo->au_company_logo }}" style="float:left;height:132px; width:190px;">
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
          <h3 class="invoice">Invoice</h3>
      </div>
      <div id="company" class="clearfix" >
          <table class="table">
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Supplier Name</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc">{{ $invoice_detail->customer_info->name }}</td>
                  <td id="text" style="width:320px;">Invoice No</td>
                  <td>:</td>
                  <td id="desc">{{ $invoice_detail->mex_curr_inv_invoice_no }}</td>
              </tr>
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Address</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc" style="float:left;">{{ $invoice_detail->customer_info->address }}</td>
                  <td id="text" style="width:320px;">Bought By</td>
                  <td>:</td>
                  <td id="desc" style="text-align:left;">{{ $invoice_detail->getSoldByInfo['au_name'] }}</td>
              </tr>
              <tr>
                  <td id="text" style="text-align: left; width:100px;">Mobile</td>
                  <td style="text-align:center;">:</td>
                  <td id="desc" style="float:left;">{{ $invoice_detail->customer_info->mobile }}</td>
                  <td id="text" style="width:320px;">Issue Date</td>
                  <td>:</td>
                  <td id="desc" style="text-align:left;">{{ $invoice_detail->mex_pro_inv_issue_date }}</td>
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
            <thead style="background-color:#ddd;">
              <tr>
                <th class="service">Sl</th>
                <th>Description</th>
                <th>Short Quantity</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              @php($sl=0)
              @php($balance=0)
              @foreach ($invoice as $buy)
              <tr>
                  <td class="service">{{ ++$sl }}</td>
                  <td class="qty">              
                    {{ $buy->country_currency->currency_code }}<br>
                    {{ $buy->currency_type->currency_note->currency_note }}  
                  </td>
                  <td style="text-align:center;">{{ $buy->mex_curr_inv_short_qty }}</td>
                  <td style="text-align:center;">{{ $buy->mex_curr_inv_total_qty }}</td>
                  <td>{{ number_format($buy->mex_curr_inv_unit_price,2) }}</td>
                  <td class="total">{{ number_format($buy->mex_curr_inv_debit,2) }}</td>
              </tr>
              @php($balance = $balance + $buy->mex_curr_inv_debit)
              @endforeach
              <tr>
                <td colspan="5">Sub Total :</td>
              <td class="total">{{ number_format($balance,2) }}</td>
              </tr>
              <tr>
                  <td colspan="5">Discount :</td>
                  <td class="total">0000.00</td>
              </tr>
              <tr>
                <td colspan="5" class="grand total">Net Payable Amount :</td>
                <td class="grand total">{{ number_format($balance,2) }}</td>
              </tr>
              <tr>
                
                <td colspan="6" class="grand total" style="text-align:left;">In Word: {{ App\NumberConverter::number_to_text($balance) }}</td>
              </tr>
            </tbody>
          </table>
          <div id="notices" align="center" style="margin-top: 70px;">
              <table>
                <tr>
                  <td style="text-align:left;">------------------------------</td>
                  <td>------------------------------</td>
                </tr>
                <tr>
                  <td style="text-align:left;">Customer Signature</td>
                  <td>Supplier Signature</td>
                </tr>
              </table>
            </div>
    </main>
    <footer>
      Powered By: IGL Web Ltd.
    </footer>
    <script>
        $(document).ready(function(){
         window.print();
        });
     </script>
  </body>
</html>