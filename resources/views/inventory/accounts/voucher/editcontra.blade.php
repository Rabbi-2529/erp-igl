@extends('layouts.master')
@section('title','Edit Contra Voucher')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
@section('contra_create','active')
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
        <h1>Edit Contra Voucher</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">Edit Contra Voucher</h3>
                    </div>
                </div>

                <div class="panel-body">
                    <form action="{{ route('accounts.update-contra', $contra->inv_abs_id) }}" method="post" class="form-horizontal" id="form-id">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">
                        
                        <div class="box-body">
                            @if($payType == 3)
                            {{-- Bank To Bank: the banks stay as they are; amount, date and description can change --}}
                            @php
                                $fromBankId = $contra->inv_abs_debit > 0 ? $contra->inv_abs_bank_id : $pairedTransaction->inv_abs_bank_id;
                                $toBankId   = $contra->inv_abs_debit > 0 ? $pairedTransaction->inv_abs_bank_id : $contra->inv_abs_bank_id;
                                $fromBank   = $banks->firstWhere('inv_abi_id', $fromBankId);
                                $toBank     = $banks->firstWhere('inv_abi_id', $toBankId);
                            @endphp
                            <input type="hidden" name="bank_id" value="{{ $fromBankId }}">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">From Bank:</label>
                                <div class="col-sm-6">
                                    <p class="form-control-static">{{ $fromBank->bank_info['bank_name'] ?? 'N/A' }} ({{ $fromBank->inv_abi_account_no ?? '' }})</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">To Bank:</label>
                                <div class="col-sm-6">
                                    <p class="form-control-static">{{ $toBank->bank_info['bank_name'] ?? 'N/A' }} ({{ $toBank->inv_abi_account_no ?? '' }})</p>
                                </div>
                            </div>
                            @else
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Bank:</label>
                                <div class="col-sm-6">
                                    <select name="bank_id" required class="form-control select2" id="bank_id" onchange="loadAvailableBalanceOfBank()">
                                        <option value="">Select A Bank</option>
                                        @foreach($banks as $bank)
                                        <option value="{{ $bank->inv_abi_id }}" 
                                            {{ ($contra->inv_abs_bank_id == $bank->inv_abi_id || $pairedTransaction->inv_abs_bank_id == $bank->inv_abi_id) ? 'selected' : '' }}>
                                            {{ $bank->bank_info['bank_name'] }} ({{ $bank->inv_abi_account_no }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    @if($cash <= 500)
                                    <span style="color:red; font-weight: bold;">Available Balance in Cash is {{ $cash }} Tk</span>
                                    @else
                                    <span style="color:green; font-weight: bold;">Available Balance in Cash is {{ $cash }} Tk</span>
                                    @endif
                                </div>
                                <br>
                                <div class="col-sm-3 bank_balance_div"></div>
                            </div>
                            @endif
                            
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Descriptions:</label>
                                <div class="col-sm-6">
                                    <input type="text" name="description" autocomplete="off" value="{{ $contra->inv_abs_description }}" class="form-control" id="inputEmail3" placeholder="Write Description Here">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Payment Type:</label>
                                <div class="col-sm-6">
                                    @if($payType == 3)
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="customRadio3" name="pay_type" value="3" checked>
                                        <label for="customRadio3" class="custom-control-label">Bank To Bank</label>
                                    </div>
                                    @else
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="customRadio1" name="pay_type" value="1" {{ $payType == 1 ? 'checked' : '' }}>
                                        <label for="customRadio1" class="custom-control-label">Cash To Bank</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="customRadio2" name="pay_type" value="2" {{ $payType == 2 ? 'checked' : '' }}>
                                        <label for="customRadio2" class="custom-control-label">Bank To Cash</label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                           <div class="form-group">
                                        <label for="inputEmail3" class="col-sm-2 control-label">Transaction Date:</label>
                                        <div class="col-sm-6">
                                            <input type="text" name="trans_date" 
                                                value="{{ \Carbon\Carbon::parse($contra->inv_abs_transaction_date)->format('Y-m-d') }}" 
                                                class="form-control datepicker" 
                                                id="opendate" 
                                                placeholder="Select A Date" 
                                                readonly>
                                        </div>
                                    </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Amount:</label>
                                <div class="col-sm-6">
                                    <input type="number" name="amount" autocomplete="off" value="{{ $contra->inv_abs_debit > 0 ? $contra->inv_abs_debit : $contra->inv_abs_credit }}" class="form-control" id="inputEmail3" placeholder="Write Amount Here" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="box-footer">
                            <div class="col-sm-3">
                                <button type="submit" id="submit-button-id" class="btn btn-info pull-right">Update</button>
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
        $("#opendate").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            endDate: "today",
        });
        
        $("#to").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
        });
    });

    function loadAvailableBalanceOfBank() {
        let bank_id = $("#bank_id").val();
        var requestUrl = "{{ route('accounts.ajax-load-bank-balance') }}";
        var _token = $("#_token").val();
        
        $.ajax({  
            type: "GET",
            url: requestUrl,
            data: { bank_id: bank_id, _token: _token },
            success: function (result) {
                $(".bank_balance_div").html(result);
            }
        });
    }

    $("#form-id").submit(function (event) {
        let btn = $("#submit-button-id");
        btn.prop('disabled', true);
        setTimeout(function(){
            btn.prop('disabled', false);
        }, 5000);
        return true;
    });
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