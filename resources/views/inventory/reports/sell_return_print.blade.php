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
  padding: 10px 0;
  margin-bottom: 30px;
}

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
  float: right;
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
    margin-right: 70px;
    padding: 10px;
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
      <div id="logo">
        <img src="">
      </div>
      <div class="header_company">
          @php($company_info = App\Models\Admin_user::company_info(Auth::user()->au_company_id))
            <h1>{{ Auth::user()->au_company_name }}</h1>
            <p>Address: {{ $company_info->au_address }}</p>
            <p>Mobile: {{ $company_info->au_mobile }}</p>
            <p>Email: {{ $company_info->au_email }}</p>
      </div>
      <div class="text-center">
          <h3 class="invoice">Invoice</h3>
      </div>
      <div id="company" class="clearfix">
          <table class="table">
              <tr>
                  <td id="text">Supplier Name</td>
                  <td id="desc">{{ $invoice_detail->getSupplierInfo['inv_sup_person'] }}</td>
                  <td id="text">Invoice No:</td>
                  <td id="desc">{{ $invoice_detail->inv_pro_inv_invoice_no }}</td>
              </tr>
              <tr>
                  <td id="text">Address</td>
                  <td id="desc"></td>
                  <td id="text">Mobile</td>
                  <td id="desc"></td>
              </tr>
              <tr>
                  <td id="text">Bought By</td>
                  <td id="desc">{{ Auth::user()->au_company_name }}</td>
                  <td id="text">Issue Date</td>
                  <td id="desc">{{ $invoice_detail->inv_pro_inv_issue_date }}</td>
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
        <table>
            <thead>
              <tr>
                <th class="service">SL</th>
                <th class="desc">PRODUCT NAME</th>
                <th>DESCRIPTION</th>
                <th>WARRANTY</th>
                <th>QUANTITY</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
              </tr>
            </thead>
            <tbody>
              @php($sl=0)
              @php($balance=0)
              @foreach ($invoice as $buy)
              <tr>
                  <td class="service">{{ ++$sl }}</td>
                  <td class="desc">
                    @if ($buy->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
                    {{ $buy->getProductWarranty['inv_pro_det_pro_id'] }}
                    @else
                    {{ $buy->getProductWarranty['inv_pro_det_pro_id'] }}<br>
                    <b>{{ implode(', ', App\Models\Inv_product_inventory::ProductSerial($buy->inv_pro_inv_id)) }}</b>
                    @endif
                  </td>
                  <td class="qty">{{ $buy->inv_pro_inv_tran_desc }}</td>
                  <td class="unit">
                      @if ($buy->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
                      No Warranty
                      @else
                      {{ $buy->getProductWarranty['inv_pro_det_pro_warranty'] }} Days
                      @endif
                  </td>
                  <td>{{ $buy->inv_pro_inv_qty }}</td>
                  <td>{{ $buy->inv_pro_inv_unit_price }}</td>
                  <td class="total">{{ $buy->inv_pro_inv_credit }}</td>
              </tr>
              @php($balance = $balance + $buy->inv_pro_inv_credit)
              @endforeach
              <tr>
                <td colspan="6">SUBTOTAL :</td>
              <td class="total">{{ number_format($balance,2) }}</td>
              </tr>
              <tr>
                <td colspan="6">DISCOUNT :</td>
                <td class="total">0000.00</td>
              </tr>
              <tr>
                <td colspan="6" class="grand total">NET REFUNDABLE AMOUNT :</td>
                <td class="grand total">{{ number_format($balance,2) }}</td>
              </tr>
            </tbody>
          </table>
          <div id="notices" align="center" style="margin-top: 70px;">
              <table>
                <tr>
                  <td>------------------------------</td>
                  <td>------------------------------</td>
                </tr>
                <tr>
                  <td>Customer Signature</td>
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
