@extends('layouts.master')
@section('title', 'Account Statement')
@section('inventory_class', 'active')
@section('customer_class', 'active')
@section('inv_customer_acc_class', 'active')
@section('customer_account_statement', 'active')

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

        <h1>Customer's Account Statements</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Account Statement</h4>
                    </div>
                </div>

                <div class="panel-body">
                    <!-- Download Button -->
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-12">
                            <a href="{{ route('customer.accounts.download-account-statement-pdf') }}" class="btn btn-warning pull-right" style="margin-left: 10px;">
                                <i class="fa fa-file-pdf-o"></i> Download PDF
                            </a>
                        </div>
                    </div>

                    <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Company</th>
                                <th>Name</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $sl = 0;
                            $total_credit = 0;
                            $total_debit = 0;
                            $total_balance = 0;
                            @endphp

                            @if(isset($inv_cus_invts) && count($inv_cus_invts) > 0)
                                @foreach($inv_cus_invts as $inv_cus_invt)
                                @php
                                $customerId = $inv_cus_invt->inv_cus_id;
                                $debit = App\Models\Inv_product_inventory::getCusDebitByID($customerId);
                                $credit = App\Models\Inv_product_inventory::getCusCreditByID($customerId);
                                $balance = $credit - $debit;
                                $total_credit += $credit;
                                $total_debit += $debit;
                                $total_balance += $balance;
                                @endphp

                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>{{ $inv_cus_invt->inv_cus_com_name }}</td>
                                    <td>{{ $inv_cus_invt->inv_cus_name }}</td>
                                    <td style="text-align: right;">{{ number_format($debit, 2) }}</td>
                                    <td style="text-align: right;">{{ number_format($credit, 2) }}</td>
                                    <td style="text-align: right;">
                                        @if($balance >= 0)
                                            <span style="color: green; font-weight: bold;">{{ number_format($balance, 2) }}</span>
                                        @else
                                            <span style="color: red; font-weight: bold;">{{ number_format($balance, 2) }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('customer.accounts.account-statement-details', $inv_cus_invt->inv_cus_id) }}" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" style="text-align: center; color: red;">
                                        <strong>No customers found.</strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>

                        <tfoot>
                            <tr style="font-weight: bold; background-color: #f5f5f5;">
                                <td colspan="3" style="text-align:right; font-weight: bolder;">Total:</td>
                                <td style="font-weight: bolder; text-align: right;">
                                    {{ number_format($total_debit, 2) }}
                                </td>
                                <td style="font-weight: bolder; text-align: right;">
                                    {{ number_format($total_credit, 2) }}
                                </td>
                                <td style="font-weight: bolder; text-align: right;">
                                    @if($total_balance >= 0)
                                        <span style="color: green;">{{ number_format($total_balance, 2) }}</span>
                                    @else
                                        <span style="color: red;">{{ number_format($total_balance, 2) }}</span>
                                    @endif
                                </td>
                                <td style="font-weight: bolder; text-align: center;">---</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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

    .btn-info {
        background-color: #17a2b8;
        color: white;
        border: 1px solid #17a2b8;
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
    }

    .btn-info:hover {
        background-color: #138496;
        color: white;
        text-decoration: none;
    }

    .btn-info.btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
</style>
@endsection