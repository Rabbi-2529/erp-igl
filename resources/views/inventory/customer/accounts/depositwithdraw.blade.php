@extends('layouts.master')
@section('title','Customer Deposit')
@section('inventory_class','active')
@section('customer_class','active')
@section('inv_customer_acc_class','active')
@section('customer_deposit_withdraw','active')
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
        <h1>Customer's Deposit/Withdraw</h1>       
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Make Transaction</h4>
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

                    <form action="{{ route('customer.accounts.deposit-withdraw') }}" method="post" class="form-horizontal" id="transactionForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">
                        
                        <div class="box-body">
                            <!-- Customer Dropdown -->
                            <div class="form-group">
                                <label for="customer_id" class="col-sm-2 control-label">Customer:</label>
                                <div class="col-sm-6">
                                    <select name="customer_id" required value="{{ old('customer_id') }}" class="form-control select2" id="customer_id" onchange="loadAvailableBalanceOfCustomer()">
                                        <option value="">Select A Customer</option>
                                        @foreach($inv_customers as $inv_customer)
                                            <option value="{{ $inv_customer->inv_cus_id }}">
                                                {{ $inv_customer->inv_cus_name }} 
                                                ({{ $inv_customer->inv_cus_com_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3 customer_balance_div"></div>
                            </div>

                            <!-- Transaction Type -->
                            <div class="form-group">
                                <label for="trans_type" class="col-sm-2 control-label">Transaction Type:</label>
                                <div class="col-sm-6">
                                    <label class="radio-inline">
                                        <input type="radio" name="trans_type" value="2" required> Credit
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="trans_type" value="3" required> Debit
                                    </label>
                                </div>
                            </div>

                            <!-- Transaction Date -->
                            <div class="form-group">
                                <label for="opendate" class="col-sm-2 control-label">Transaction Date:</label>
                                <div class="col-sm-6">
                                    <input type="text" 
                                           name="trans_date" 
                                           data-date-format="dd-mm-yyyy" 
                                           autocomplete="off" 
                                           value="{{ old('trans_date') ? date('d/m/Y', strtotime(old('trans_date'))) : '' }}" 
                                           class="form-control" 
                                           id="opendate" 
                                           placeholder="DD/MM/YYYY" 
                                           required>
                                </div>
                            </div>

                            <!-- Reference -->
                            <div class="form-group">
                                <label for="reference" class="col-sm-2 control-label">Reference:</label>
                                <div class="col-sm-6">
                                    <input type="text" 
                                           name="reference" 
                                           autocomplete="off" 
                                           value="{{ old('reference') }}" 
                                           class="form-control" 
                                           id="reference" 
                                           placeholder="Write Reference Here">
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="form-group">
                                <label for="amount" class="col-sm-2 control-label">Amount:</label>
                                <div class="col-sm-6">
                                    <input type="number" 
                                           name="amount" 
                                           autocomplete="off" 
                                           value="{{ old('amount') }}" 
                                           class="form-control" 
                                           id="amount" 
                                           placeholder="Write Amount Here" 
                                           required 
                                           min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="box-footer">
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-info pull-right">Submit</button>
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
        // Initialize Datepicker with DD/MM/YYYY format for display
        $("#opendate").datepicker({
            format: 'dd/mm/yyyy',        // Display format: DD/MM/YYYY
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true
        });

        // Convert date format BEFORE form submission
        $('#transactionForm').on('submit', function(e) {
            var dateInput = $('#opendate');
            var dateValue = dateInput.val();
            
            if (dateValue) {
                // Convert from DD/MM/YYYY to YYYY-MM-DD
                var parts = dateValue.split('/');
                if (parts.length === 3) {
                    var day = parts[0];
                    var month = parts[1];
                    var year = parts[2];
                    var formattedDate = year + '-' + month + '-' + day;
                    dateInput.val(formattedDate);
                }
            }
            return true;
        });
    });

    // Function to load customer balance
    function loadAvailableBalanceOfCustomer() {
        let customer_id = $("#customer_id").val();
        var requestUrl = "{{ route('accounts.ajax-load-customer-balance') }}";
        var _token = $("#_token").val();
        
        $.ajax({  
            type: "GET",
            url: requestUrl,
            data: { customer_id: customer_id, _token: _token },
            success: function(result) {
                $(".customer_balance_div").html(result);
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
</style>
@endsection