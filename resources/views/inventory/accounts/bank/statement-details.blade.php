@extends('layouts.master')
@section('title','Bank Details')
@section('inventory_class','active')
@section('accounts_class','active')
@section('bank_class','active')
@section('bank_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        <div class="box">
            <div class="box-header">
                <form action="" method="get">
                    <input type="hidden" name="_token" value="" id="_token">
                    <div class="col-xs-3">
                        <input type="text" name="start_date" value="{{ $start_date ?? '' }}" data-date-format="yyyy-mm-dd" autocomplete="off" class="form-control" id="start_date" placeholder="Enter Start Date">
                    </div>
                    <div class="col-xs-3">
                        <input type="text" name="end_date" value="{{ $end_date ?? '' }}" data-date-format="yyyy-mm-dd" autocomplete="off" class="form-control" id="end_date" placeholder="Enter End Date">
                    </div>
                    <div class="col-xs-3">
                        <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
                    </div>
                </form>
            </div>
            <div class="box-body"></div>
        </div>

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

        <h1>
            {{ $bank_info->bank_info->bank_name ?? 'N/A' }} ({{ $bank_info->inv_abi_account_no ?? 'N/A' }})
        </h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">Bank Statement</h3>
                    </div>
                </div>

                <table id="example1" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Transaction Details</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th class="text-center">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($sl = 0)
                        @php($total_balance = 0)
                        @php($total_credit = 0)
                        @php($total_debit = 0)
                        @php($reference = '')

                        @foreach ($statements as $statement)
                        @php($person = '')
                        @php($debit = $statement->inv_abs_debit)
                        @php($credit = $statement->inv_abs_credit)
                        @php($total_debit = $total_debit + $debit)
                        @php($total_credit = $total_credit + $credit)
                        {{-- Balance = Credit (money in) - Debit (money out), same as the bank balance everywhere else --}}
                        @php($total_balance = $total_balance + $credit - $debit)

                        @php($reference = App\Models\Inv_acc_bank_statement::getReferenceTypeLabel($statement) . '.')
                        @if($statement->inv_abs_reference_type == 1)
                            @php($person = '('.App\Models\Inv_customer::getCustomerNameById($statement->inv_abs_reference_id).')')
                        @elseif($statement->inv_abs_reference_type == 2)
                            @php($person = '('.App\Models\Inv_supplier::getSupplierNameByID($statement->inv_abs_reference_id)->inv_sup_person.')')
                        @endif

                        <tr>
                            <td class="text-center">{{ ++$sl }}</td>
                            <td style="width: 100px;">{{ \Carbon\Carbon::parse($statement->inv_abs_transaction_date)->format('d-m-Y') }}</td>
                            <td>
                                {{ $statement->inv_abs_description }} | {{ $reference }} {{ $person }}
                            </td>
                            <td style="text-align: right;">{{ number_format($debit, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($credit, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($total_balance, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_debit, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_credit, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_balance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .action_btn {
        border: 1px solid;
        padding: 5px;
    }
    .btn_show {
        background-color: green;
        color: white;
    }
    .btn_delete {
        background-color: red;
        color: white;
    }
    .btn_edit {
        background-color: cornflowerblue;
        color: white;
    }
</style>
@endsection

@section('custom_script')
<script>
    $(document).ready(function() {
        $("#start_date").datepicker({
            dateFormat: 'yy-mm-dd',
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });

        $("#end_date").datepicker({
            dateFormat: 'yy-mm-dd',
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
    });
</script>
@endsection