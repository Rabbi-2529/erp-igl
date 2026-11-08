@extends('layouts.master')
@section('title','LC Statement')
@section('lc_class','active')
@section('lcm_class','active')
@section('lc_statement_class','active')
@section('content')
<style>
    .small-box h3 { font-size: 22px; white-space: nowrap; }
    .lc-picker { margin-bottom: 15px; }
</style>
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>LC Statement</h1>
    </section>

    <form method="get" action="{{ route('lc.statement') }}" class="form-inline lc-picker">
        <label for="lc_pick">LC&nbsp;</label>
        <select name="lc" id="lc_pick" class="form-control" style="min-width: 380px;" onchange="this.form.submit()">
            <option value="">Select LC</option>
            @foreach($lcs as $item)
            <option value="{{ $item->lc_id }}" {{ $lc && $lc->lc_id == $item->lc_id ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
        <a href="{{ route('lc.opening') }}" class="btn btn-success"><i class="fa fa-plus"></i> Open LC</a>
    </form>

    @if($lc)
    @php
        $goodsPaid = $lc->goodsPaid();
        $importCosts = $lc->importCosts();
        $landedFactor = $lc->landedFactor();
        $marginDue = round($lc->lc_value_bdt * $lc->lc_margin_percent / 100, 2);
        $marginPaid = $lc->costs->where('lcx_head', 1)->sum('lcx_amount');
    @endphp

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-opening">
                <div class="inner">
                    <h3>৳ {{ number_format($lc->lc_value_bdt, 2) }}</h3>
                    <p>LC Value ({{ $lc->file->lcf_currency }} {{ number_format($lc->lc_value_fc, 2) }} &times; {{ $lc->lc_rate + 0 }})</p>
                </div>
                <div class="icon"><i class="ion ion-document"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>৳ {{ number_format($goodsPaid, 2) }}</h3>
                    <p>Paid for Goods (margin + retirement)</p>
                </div>
                <div class="icon"><i class="ion ion-checkmark"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>৳ {{ number_format($lc->payable(), 2) }}</h3>
                    <p>Payable to Supplier</p>
                </div>
                <div class="icon"><i class="ion ion-alert"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>৳ {{ number_format($lc->landedTotal(), 2) }}</h3>
                    <p>Landed Cost (incl. {{ number_format($importCosts, 2) }} import costs)</p>
                </div>
                <div class="icon"><i class="ion ion-cube"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            LC {{ $lc->lc_no }}
                            <small>{{ $lc->file->supplier->inv_sup_com_name ?? '' }} &middot; File {{ $lc->file->lcf_file_no }}</small>
                            <span class="pull-right">
                                <a href="{{ route('lc.files.show', $lc->lc_file_id) }}" class="btn btn-default btn-sm"><i class="fa fa-folder-open"></i> File</a>
                                <a href="{{ route('lc.documents', ['lc' => $lc->lc_id]) }}" class="btn btn-default btn-sm"><i class="fa fa-paperclip"></i> Documents</a>
                                <a href="{{ route('lc.shipments.create', ['lc' => $lc->lc_id]) }}" class="btn btn-default btn-sm"><i class="fa fa-ship"></i> Add Shipment</a>
                            </span>
                        </h3>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <th>LC Date</th><td>{{ \Carbon\Carbon::parse($lc->lc_date)->format('d M Y') }}</td>
                            <th>Issuing Bank</th><td>{{ $lc->bank->bank_info->bank_name ?? '' }} ({{ $lc->bank->inv_abi_account_no ?? '' }})</td>
                        </tr>
                        <tr>
                            <th>Expiry</th><td>{{ $lc->lc_expiry_date ? \Carbon\Carbon::parse($lc->lc_expiry_date)->format('d M Y') : '-' }}</td>
                            <th>Last Shipment</th><td>{{ $lc->lc_last_ship_date ? \Carbon\Carbon::parse($lc->lc_last_ship_date)->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Margin</th><td>{{ $lc->lc_margin_percent + 0 }}% = {{ number_format($marginDue, 2) }} (paid {{ number_format($marginPaid, 2) }})</td>
                            <th>Status</th><td>{{ $lc->status_label }}</td>
                        </tr>
                    </table>

                    <h4>Payments &amp; Costs</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Head</th>
                                <th>Description</th>
                                <th>Paid From</th>
                                <th class="text-right">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lc->costs->sortBy('lcx_date') as $cost)
                            <tr>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($cost->lcx_date)->format('d M Y') }}</td>
                                <td><small>{{ $cost->lcx_voucher_no }}</small></td>
                                <td><span class="label {{ $cost->is_goods_payment ? 'label-success' : 'label-warning' }}">{{ $cost->head_label }}</span></td>
                                <td>{{ $cost->lcx_description }}</td>
                                <td>{{ $cost->bank->bank_info->bank_name ?? ($cost->bank->inv_abi_account_name ?? '') }}</td>
                                <td class="text-right">{{ number_format($cost->lcx_amount, 2) }}</td>
                                <td class="text-center">
                                    <form action="{{ route('lc.costs.delete', $cost->lcx_id) }}" method="post" onsubmit="return confirm('Delete this payment? It will also be removed from the bank statement.');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr><th colspan="5" class="text-right">Paid for goods</th><th class="text-right">{{ number_format($goodsPaid, 2) }}</th><th></th></tr>
                            <tr><th colspan="5" class="text-right">Import costs</th><th class="text-right">{{ number_format($importCosts, 2) }}</th><th></th></tr>
                            <tr><th colspan="5" class="text-right">Total paid</th><th class="text-right">{{ number_format($goodsPaid + $importCosts, 2) }}</th><th></th></tr>
                        </tfoot>
                    </table>
                    <small class="text-muted">
                        <span class="label label-success">green</span> heads pay the supplier for the goods (they also show in the supplier's General Ledger);
                        <span class="label label-warning">orange</span> heads are import costs added to the landed cost.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title"><h3 class="box-title">Add Payment / Cost</h3></div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('lc.costs.store', $lc->lc_id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="head">Head <span class="text-danger">*</span></label>
                            <select name="head" id="head" class="form-control dont-select-me" required>
                                @foreach($heads as $id => $label)
                                <option value="{{ $id }}" {{ (string) old('head', $marginPaid < $marginDue ? 1 : 4) === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount (BDT) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control text-right" required
                                   value="{{ old('amount', $marginPaid < $marginDue ? $marginDue - $marginPaid : '') }}">
                            <small class="text-muted">Payable now: {{ number_format($lc->payable(), 2) }}</small>
                        </div>
                        <div class="form-group">
                            <label for="cost_date">Date <span class="text-danger">*</span></label>
                            <input type="text" name="cost_date" id="cost_date" class="form-control datepicker" autocomplete="off" value="{{ old('cost_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="pay_bank_id">Paid From (Bank / Cash) <span class="text-danger">*</span></label>
                            <select name="bank_id" id="pay_bank_id" class="form-control" style="width: 100%;" required>
                                <option value="">Select account</option>
                                @foreach($payBanks as $id => $label)
                                <option value="{{ $id }}" {{ (string) old('bank_id', $lc->lc_bank_id) === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" name="description" id="description" class="form-control" maxlength="150" value="{{ old('description') }}" placeholder="Optional">
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-check"></i> Save Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title"><h3 class="box-title">Landed Cost per Product</h3></div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price ({{ $lc->file->lcf_currency }})</th>
                        <th class="text-right">Goods Cost / Unit (BDT)</th>
                        <th class="text-right">Landed Cost / Unit (BDT)</th>
                        <th class="text-right">Landed Total (BDT)</th>
                        <th class="text-right">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lc->file->items as $item)
                    @php
                        $goodsUnit = round($item->lcfi_unit_price_fc * $lc->lc_rate, 2);
                        $landedUnit = round($goodsUnit * $landedFactor, 2);
                    @endphp
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td class="text-right">{{ $item->lcfi_qty + 0 }}</td>
                        <td class="text-right">{{ number_format($item->lcfi_unit_price_fc, 2) }}</td>
                        <td class="text-right">{{ number_format($goodsUnit, 2) }}</td>
                        <td class="text-right"><b>{{ number_format($landedUnit, 2) }}</b></td>
                        <td class="text-right">{{ number_format($landedUnit * $item->lcfi_qty, 2) }}</td>
                        <td class="text-right">{{ $item->lcfi_received_qty + 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <small class="text-muted">Import costs are shared over the products by value. Add every cost before receiving the shipment, so the landed cost is complete.</small>
        </div>
    </div>
    @endif
</section>
@endsection
