@extends('layouts.master')
@section('title','Supplier Payment')
@section('inventory_class','active')
@section('supplier_class','active')
@section('inv_supplier_acc_class','active')
@section('supplier_payment','active')
@section('content')
<section class="content">
    <section class="content-header">
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
        <h1>Supplier's Payment</h1>       
    </section>
    
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Payment</h4>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                   
                    <form action="{{route('inventory.supplier.accounts.payment')}}" method="post" class="form-horizontal">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">
                        
                        <div class="box-body">

                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Supplier:</label>
                                <div class="col-sm-6">
                                    <select name="supplier_id" required value="{{old('supplier_id')}}" class="form-control select2" id="supplier_id" onchange="loadAvailableBalanceOfSupplier()">
                                        <option value="">Select A Supplier</option>
                                        @foreach($inv_suppliers as $inv_supplier)
                                        <option value="{{$inv_supplier->inv_sup_id}}">
                                            {{$inv_supplier->inv_sup_person }} ({{$inv_supplier->inv_sup_com_name}})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3 supplier_balance_div"></div>
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Payment Method:</label>
                                <div class="col-sm-6">
                                    <select name="bank_id" required value="{{old('bank_id')}}" class="form-control select2" id="bank_id" onchange="loadAvailableBalanceOfBank()">
                                        <option value="">Select A Method</option>
                                        @foreach($banks as $bank)
                                        <option value="{{$bank->inv_abi_id}}">
                                            {{$bank->bank_info['bank_name'] }} ({{$bank->inv_abi_account_no}})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- THIS DIV WILL SHOW THE BANK BALANCE -->
                                <div class="col-sm-3 bank_balance_div"></div>
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="trans_date" autocomplete="off" value="{{ old('trans_date', date('d/m/Y')) }}" class="form-control datepicker" id="opendate" placeholder="dd/mm/yyyy" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Reference:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="reference" autocomplete="off" value="{{ old('reference') }}" class="form-control" id="inputEmail3" placeholder="Write Reference Here">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                                <div class="col-sm-6">
                                    <input type="number" step="0.01" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="inputEmail3" placeholder="Write Amount Here" required min="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Discount:</label>
                                <div class="col-sm-6">
                                    <input type="number" step="0.01" name="discount" autocomplete="off" value="{{ old('discount') }}" class="form-control" id="inputEmail3" placeholder="Write Discount Amount Here" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="box-footer">
                            <div class="col-sm-3">
                                <button type="submit" id="" class="btn btn-info pull-right">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
    $(document).ready(function(){
        // Datepicker setup
        $("#opendate").datepicker({
            dateFormat: 'dd/mm/yy',
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            changeMonth: true,
            changeYear: true
        });
    });

    // Load Supplier Balance
    function loadAvailableBalanceOfSupplier() {
        let supplier_id = $("#supplier_id").val();
        if(!supplier_id) {
            $(".supplier_balance_div").html('');
            return;
        }
        
        // ========== FIXED ROUTE NAME (Added 'inventory.' prefix) ==========
        var requestUrl = "{{route('inventory.supplier.ajax-load-supplier-balance')}}";
        var _token = $("#_token").val();

        $.ajax({  
            type: "GET",
            url: requestUrl,
            data: { supplier_id: supplier_id, _token: _token },
            success: function (result) {
                $(".supplier_balance_div").html(result);
            },
            error: function() {
                $(".supplier_balance_div").html('<span style="color:red;">Route Error</span>');
            }
        });
    }
    
    // Load Bank Balance
    function loadAvailableBalanceOfBank() {
        let bank_id = $("#bank_id").val();
        if(!bank_id) {
            $(".bank_balance_div").html('');
            return;
        }

        // ========== FIXED ROUTE NAME (Added 'inventory.' prefix) ==========
        var requestUrl = "{{route('inventory.supplier.ajax-load-bank-balance')}}";
        var _token = $("#_token").val();

        // Show loading indicator
        $(".bank_balance_div").html('<span class="text-muted">Loading balance...</span>');

        $.ajax({  
            type: "GET",
            url: requestUrl,
            data: { bank_id: bank_id, _token: _token },
            success: function (result) {
                $(".bank_balance_div").html(result);
            },
            error: function() {
                // Shows a clear error if the route or backend function is missing
                $(".bank_balance_div").html('<span style="color:red;">Balance not found.</span>');
            }
        });
    }
</script>
@endsection

@section('custom_style')
<style type="text/css">
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
    
    .ui-datepicker {
        z-index: 9999 !important;
    }
</style>
@endsection