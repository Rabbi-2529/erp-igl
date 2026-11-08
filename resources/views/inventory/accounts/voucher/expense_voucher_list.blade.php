@extends('layouts.master')
@section('title','Expense Voucher List')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
@section('expense_class','active')
@section('expenses-voucher-list','active')
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
                        <h3 class="box-title">
                            Expense Voucher List
                            <a href="{{ route('accounts.cost-add') }}" class="btn btn-success btn-sm pull-right">
                                <i class="fa fa-plus"></i> Add Expense
                            </a>
                        </h3>
                    </div>
                </div>
                <div class="panel-body">
                    
                    <table class="table table-bordered table-striped table-hover" id="voucherTable">
                        <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Category</th>
                                 <th>Expense</th>
                                <th>Description</th>
                                <th>Paid From</th>
                                <th>Date</th>
                                <th>Created At</th>
                                <th class="text-right">Amount (Tk)</th>
                                <!--<th>Action</th>-->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                            @php($bank = $banks[$voucher->inv_abs_bank_id] ?? null)
                            <tr>
                                <td>{{ $voucher->inv_abs_voucher_no }}</td>
                    <td>{{ $voucher->category_name ?? 'N/A' }}</td>
<td>{{ $voucher->expense_name ?? 'N/A' }}</td>
                                <td>{{ $voucher->inv_abs_description }}</td>
                                <td>{{ $bank ? ($bank->bank_info->bank_name ?? $bank->inv_abi_account_name) . ' (' . $bank->inv_abi_account_no . ')' : 'N/A' }}</td>
                               <td data-order="{{ $voucher->inv_abs_transaction_date }}">{{ \Carbon\Carbon::parse($voucher->inv_abs_transaction_date)->format('d F Y') }}</td>
                                <td data-order="{{ $voucher->inv_abs_submit_at }}">{{ $voucher->inv_abs_submit_at ? \Carbon\Carbon::parse($voucher->inv_abs_submit_at)->format('d M Y, h:i A') : 'N/A' }}</td>

                                <td class="text-right">{{ number_format($voucher->inv_abs_debit,2) }}</td>
<!--                                <td>-->
<!--                                 <a href="{{ route('accounts.expenses-voucher-edit', $voucher->id) }}" class="btn btn-sm btn-info">-->
<!--    <i class="fa fa-edit"></i> Edit-->
<!--</a>-->
<!--                                </td>-->
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">Total</th>
                                <th class="text-right">{{ number_format($vouchers->sum('inv_abs_debit'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function(){
    $('#voucherTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "order": [], // keep the newest-first order from the server
        "searching": true,
        "language": { "emptyTable": "No Expense Vouchers Found" }
    });
});
</script>
@endsection

@section('custom_style')
<style type="text/css">
.table th, .table td {
    vertical-align: middle !important;
}
</style>
@endsection
