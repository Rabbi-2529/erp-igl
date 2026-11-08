@extends('layout.master')
@section('money_class','menu-open')
@section('curr_inv_class','menu-open')
@section('curr_buy_class','menu-open')
@section('curr_add','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(session()->has('msg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('msg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if(session()->has('sub_success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('sub_success') }}
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
          <h1>
            Purchase Product
          </h1>
        </section>
        {{ Form::open(['action' => 'MoneyExchange\CurrencyPurchaseCartController@invTemporaryBuy' , 'method' => 'get' , 'class' => ' form-inline']) }}
               <div class="box" id="total">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Product</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
            <div class="box-body">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label" style="width: 101px;">Supplier
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-sm-6">
                            <select name="supplier" style="width: 170px;" id="" class="form-control select2" required>
                                <option value="">Select One</option>
                                @foreach ($customers as $cus)
                                <option value="{{ $cus->id }}">
                                    {{ $cus->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center">
                            @if($errors->has('supplier'))
                                <p class="text-danger">{{ $errors->first('supplier') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-2 control-label" style="width: 115px;">Issue Date
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-sm-6">
                            <input type="text" autocomplete="off" data-date-format="yyyy-mm-dd" class="form-control" placeholder="Enter Issue Date" id="issue" name="issue_date" required>
                        </div>
                        <div class="text-center">
                            @if($errors->has('supplier'))
                                <p class="text-danger">{{ $errors->first('supplier') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label" style="width: 142px;">Memo Number
                                <span class="text-danger">*</span>:
                        </label>
                        <div class="col-sm-6">
                        <input type="text" name="memo" required autocomplete="off" value="{{ old('memo') }}" class="form-control" id="" placeholder="Enter Memo Number">
                        </div>
                        <div class="text-center">
                            @if($errors->has('memo'))
                            <p class="text-danger">{{ $errors->first('memo') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
            <div class="col-sm-12">
                    <table id="purchase_product_list_table" class="table table-bordered table-striped" >
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Currency</th>
                                    <th>A. S.</th>
                                    
                                    <th>Buy Price</th>
                                    <th>Short</th>
                                    <th>Remarks</th>
                                    <th>Purchase</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php($sl=0)
                            @foreach ($sell_pro as $sell)
                                <tr>
                                    <td class="text-center">{{ ++$sl }}</td>
                                    <td>{{ $sell->country_name['currency_code'] }}</td>
                                    <td>
                                        @if ($sell->country_name['currency_symbol'] == '')
                                        {{ $sell->currency_note }}
                                        @else
                                        ({{ $sell->country_name['currency_symbol'] }}){{ $sell->currency_note }}
                                        @endif
                                    </td>
                                    <td align="center"></td>
                                    {{-- <td class="text-center">
                                        <input type="text" autocomplete="off" data-date-format="yyyy-mm-dd" class="form-control from" id="exp_date_{{ $sell->id }}" style="width: 100px;" placeholder="Expire Date">
                                    </td> --}}
                                    <td class="text-center">
                                        <input type="text" class="form-control" id="pro_price_{{ $sell->id }}" style="width: 100px;" value="">
                                    </td>
                                    <td>
                                        
                                        <input type="text" autocomplete="off" class="form-control" name="short_qty" id="short_qty_{{ $sell->id }}" style="width: 70px;" placeholder="Qty">
                                        
                                    </td>
                                    <td class="text-center">
                                        <input type="text" name="remarks" class="form-control" id="short_remarks_{{ $sell->id }}" placeholder="Reason of short quatity..">
                                    </td>
                                    <td class="text-center">
                                       
                                        <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $sell->id }}" placeholder="Qty">
                                        <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->id }}')">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>


                  </div>
                  <div class="box-body">
                        <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-shopping-cart"></i>
                                    Buy Invoice
                                </h3>
                                </div>  
                        <div class="col-sm-12">                
                                    <div class="box shopping_cart"> 
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Buy Qty</th>
                                                    <th>Unit Price</th>
                                                    <th>Amount</th>
                                                    <th>Short</th>
                                                    <th>Remarks</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="show-cart-conten">
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-group" style="float: right;">
                                            <label for="inputEmail3" class="col-sm-2 control-label">Total: </label>
                                            <div class="col-sm-6">
                                                <input type="text" class="text-right" disabled name="address" autocomplete="off" value="" class="form-control" id="total_buy">
                                            </div>
                                        <button class="btn btn-success btn-sm pull-right">Continue</button>
                                    </div>
                            </div>
                            
                  </div>
              </div>
              {{ Form::close() }}
             </section>

             @include('pages.ajax.warrenty_product_get_sl_no');

             
@endsection

@section('custom_style')
<style>
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
    .warranty{
        background-color: black;
    }
    .all-product-sl-no-main-wrapper {
        border: 2px solid #ddd;
        padding: 7px;
        height: 350px;
        /* overflow: scroll; */
        position: absolute;
    }
    .product-warrenty-sl {
        margin-top: 5px;
    }
    .table>tbody>tr>td{
        padding: 2px;
    }
    .shopping_cart{
        min-height: 300px;
        border: 1px solid grey;
        /* overflow: scroll; */
        padding: 10px;
    }

    input {
        height: 27px !important;
    }

    .btn-sm {
        padding: 3px 8px;
    }
</style>
@endsection
@section('custom_script')
{{-- <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script> --}}
<script>
    // var price = new Vue({
    //     el: '#total',
    //     methods:{
    //         total_amount:function(){
    //             console.log('adssdf');
    //         },
    //     },

    // });
    function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

            const negativeSign = amount < 0 ? "-" : "";

            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;

            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
        } catch (e) {
            console.log(e)
        }
        }

    function total_buy_amount(){
        let amount = 0;
        $('.temp_cart').each(function(i, obj) {
            amount = amount + parseFloat(obj.innerText.split(',').join(''));
        });
        if (isNaN(amount)) {
            amount = 0;
        }

        $('#total_buy').val(formatMoney(amount));
    }

    $(document).ready(function(){

    @if(session()->has('print_buy_invoice'))
        let route = "{!! route('money-exchange.reports.buy-print', session()->get('print_buy_invoice')) !!}";
        window.open(route, '_blank');
    @endif

    var date = new Date();
    date.setDate(date.getDate());
    $( "#issue" ).datepicker({
        daysOfWeekHighlighted: "7",
            todayHighlight: true,
            endDate: date,
            autoclose: true,
        });
    $( ".from" ).datepicker({
        daysOfWeekHighlighted: "7",
        startDate: date,
        todayHighlight: true,
        autoclose: true,
        });
    });

    $(document).ready(function(){
        getCartProduct();
        $( "#expire" ).datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
        $( "#to" ).datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
        });
        
        var table = $('#purchase_product_list_table').DataTable( {
            pageLength : 5,
            lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
        } );
                
    });

    function addtocart(pro_det_id) {
        let pro_qty = parseFloat($("#pro_qty_"+pro_det_id).val());
        let short_qty = parseFloat($("#short_qty_"+pro_det_id).val());
        let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());
        let remarks = $("#short_remarks_"+pro_det_id).val();
        let exp_date = $("#exp_date_"+pro_det_id).val();
        if(isNaN(pro_qty) || isNaN(pro_price)) {
            alert("quantity field can\'t be empty");
        } else if(pro_qty < 1) {
            alert("minimum quantity at least 1");
        } else {

            let route_url = "{{ route('money-exchange.currency-inventory.buy-add-to-cart') }}";
            $.ajax({
                type: "GET",
                url: route_url,
                data: { pro_id: pro_det_id, pro_qty: pro_qty, pro_price: pro_price, short_qty:short_qty , remarks:remarks},
                success: function (result) {
                    // console.log(result);
                    if(result.status == 400) {
                        alert("Stock has been cross it's limit");
                    } else {
                        getCartProduct();
                    }
                }
            });
        }
    }

    function getCartProduct() {
        let route_url = "{{ route('money-exchange.currency-inventory.buy-get-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: {},
            success: function (result) {
                $("#show-cart-conten").html(result);
                total_buy_amount();
            }
        });
    }

    function update_cart_quantity(content_id) {
        let pro_quantity = parseFloat($("#update_qty_"+content_id).val());
        if(pro_quantity < 1) {
            alert("Minimum Quantity is 1");
            return false;
        }
        let route_url = "{{ route('buy.buy-update-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: {content_id:content_id, pro_qty:pro_quantity},
            success: function (result) {
                getCartProduct();
            }
        });
    }

    function remove_cart(content_id) {
        let clickDel = confirm("Are you sure want to delete this?");
        if (clickDel == true) {
            
        }else{
            return false;
        }
        
        let route_url = "{{ route('money-exchange.currency-inventory.buy-remove-cart') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: {content_id:content_id},
            success: function (result) {
                getCartProduct();
            }
        });
    }

    function addWarrentyProduct (pro_det_id) {
        let pro_price = parseFloat($("#pro_price_"+pro_det_id).val());
        let exp_date = $("#exp_date_"+pro_det_id).val();
        
        let route_url = "{{ route('buy.buy-add-to-cart-warrenty-product') }}";
        
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: pro_det_id, pro_price: pro_price, exp_date: exp_date},
            success: function (result) {
                if(result.status == 404) {
                    alert("Invalid Warrenty Product");
                } else if(result.status == 406) {
                    alert("Invalid Warrenty Product");
                } else {
                    $("#warrenty_product_get_sl_no .modal-content").html(result);
                    $("#warrenty_product_get_sl_no").modal('show');
                }
            }
        });
        
    }

    function add_warrenty_pro_sl_no() {
        
    }


    function check_sl_no(value, pro_det_id) {
        
        let sl_no = value;
        if(sl_no == '') {
            alert("sl no is required");
            return false;
        }
        let route_url = "{{ route('buy.buy-add-warrenty-product-sl-no') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: pro_det_id, sl_no: sl_no},
            success: function (result) {
                if(result.status == 404) {
                    alert("Invalid Warrenty Product");
                } else if(result.status == 402) {
                    alert("Already Added");
                } else {
                    $("#all-added-warrenty-product-id .show-added-list").html(result);
                    $("#pur_warrenty_pro_sl_no").val("");
                    getCartProduct();
                }
                
            }
        });
    }


    function remove_product_sl(product_id, sl_no) {

        let route_url = "{{ route('buy.buy-remove-warrenty-product-sl') }}";
        $.ajax({
            type: "GET",
            url: route_url,
            data: { pro_id: product_id, sl_no: sl_no},
            success: function (result) {
                if(result.status == 404) {
                    alert("Invalid Warrenty Product");
                } else if(result.status == 406) {
                    alert("Invalid Warrenty Product");
                } else {
                    $("#all-added-warrenty-product-id .show-added-list").html(result);
                    getCartProduct();
                }
            }
        });
    }
    
</script>
@endsection