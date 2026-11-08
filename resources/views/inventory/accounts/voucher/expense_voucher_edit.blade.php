@extends('layouts.master')
@section('title','Edit Expense Voucher')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
@section('expenses-voucher','active')
@section('content')
<section class="content">
 
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

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">Edit Expense Voucher</h3>
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

                 <form action="{{ route('accounts.expenses-voucher-update', $voucher->inv_abs_id) }}" method="post" class="form-horizontal" id="form-id">
    @csrf
    <div class="box-body">

        <div class="form-group">
            <label class="col-sm-2 control-label">Expenses:</label>
            <div class="col-sm-6">
                <select name="expense_id" required class="form-control select2" id="expense">
                    <option value="">Select An Expense</option>
                    @foreach($expenses as $expense)
                        <option value="{{ $expense->inv_acc_exp_id }}"
                            {{ $voucher->inv_abs_reference_id == $expense->inv_acc_exp_id ? 'selected' : '' }}>
                            {{ $expense->inv_acc_exp_expense_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Date:</label>
            <div class="col-sm-6">
                <input type="text" name="trans_date" data-date-format="yyyy-mm-dd"
                       value="{{ \Carbon\Carbon::parse($voucher->inv_abs_transaction_date)->format('d/m/Y') }}"
                       autocomplete="off" class="form-control" id="opendate" placeholder="Select A Date">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Reference:</label>
            <div class="col-sm-6">
                <input type="text" name="reference" autocomplete="off"
                       value="{{ old('reference', $voucher->inv_abs_description) }}"
                       class="form-control" placeholder="Enter a Reference" required>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Amount:</label>
            <div class="col-sm-6">
                <input type="number" name="paid_amount" autocomplete="off"
                       value="{{ old('paid_amount', $voucher->inv_abs_debit) }}"
                       class="form-control" placeholder="Enter Amount" min="0">
            </div>
        </div>

    </div>

    <div class="box-footer">
        <div class="col-sm-3">
            <button type="submit" id="submit-button-id" class="btn btn-success pull-right">Update</button>
            <a href="{{ route('accounts.expenses-voucher-list') }}" class="btn btn-default">Back</a>
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
});

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
