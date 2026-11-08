@extends('layouts.master')
@section('title','LC Summary')
@section('lc_class','active')
@section('lcm_class','active')
@section('lc_summary_class','active')
@section('content')
<style>
    .small-box h3 { font-size: 22px; white-space: nowrap; }
</style>
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>LC Summary</h1>
    </section>

    @php
        $totalValue = $lcs->sum('lc_value_bdt');
        $totalPaid = $lcs->sum(function ($lc) { return $lc->goodsPaid(); });
        $totalCosts = $lcs->sum(function ($lc) { return $lc->importCosts(); });
        $totalPayable = $lcs->sum(function ($lc) { return $lc->payable(); });
    @endphp
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-opening">
                <div class="inner"><h3>৳ {{ number_format($totalValue, 2) }}</h3><p>Total LC Value ({{ $lcs->count() }} LCs)</p></div>
                <div class="icon"><i class="ion ion-document"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
                <div class="inner"><h3>৳ {{ number_format($totalPaid, 2) }}</h3><p>Paid for Goods</p></div>
                <div class="icon"><i class="ion ion-checkmark"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner"><h3>৳ {{ number_format($totalPayable, 2) }}</h3><p>Payable to Suppliers</p></div>
                <div class="icon"><i class="ion ion-alert"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner"><h3>৳ {{ number_format($totalCosts, 2) }}</h3><p>Import Costs</p></div>
                <div class="icon"><i class="ion ion-cash"></i></div>
            </div>
        </div>
    </div>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="box-title">
                    All LCs
                    <a href="{{ route('lc.opening') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Open LC</a>
                </h3>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>LC No</th>
                        <th>Date</th>
                        <th>File / Supplier</th>
                        <th>Bank</th>
                        <th class="text-right">LC Value</th>
                        <th class="text-right">BDT</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Payable</th>
                        <th class="text-right">Import Costs</th>
                        <th class="text-right">Landed</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lcs as $lc)
                    @php
                        $ordered = $lc->file->items->sum('lcfi_qty');
                        $received = $lc->file->items->sum('lcfi_received_qty');
                    @endphp
                    <tr>
                        <td><b>{{ $lc->lc_no }}</b></td>
                        <td data-order="{{ $lc->lc_date }}" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($lc->lc_date)->format('d M Y') }}</td>
                        <td>{{ $lc->file->lcf_file_no }}<br><small class="text-muted">{{ $lc->file->supplier->inv_sup_com_name ?? '' }}</small></td>
                        <td>{{ $lc->bank->bank_info->bank_name ?? '' }}</td>
                        <td class="text-right" style="white-space: nowrap;">{{ $lc->file->lcf_currency }} {{ number_format($lc->lc_value_fc, 2) }}</td>
                        <td class="text-right">{{ number_format($lc->lc_value_bdt, 2) }}</td>
                        <td class="text-right">{{ number_format($lc->goodsPaid(), 2) }}</td>
                        <td class="text-right {{ $lc->payable() > 0 ? 'text-danger' : '' }}">{{ number_format($lc->payable(), 2) }}</td>
                        <td class="text-right">{{ number_format($lc->importCosts(), 2) }}</td>
                        <td class="text-right">{{ number_format($lc->landedTotal(), 2) }}</td>
                        <td>
                            {{ $received + 0 }} / {{ $ordered + 0 }}
                            @if($ordered > 0 && $received >= $ordered)
                                <span class="label label-success">Done</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('lc.statement', ['lc' => $lc->lc_id]) }}" class="btn btn-info btn-xs" title="Statement"><i class="fa fa-list"></i></a>
                            <a href="{{ route('lc.documents', ['lc' => $lc->lc_id]) }}" class="btn btn-default btn-xs" title="Documents"><i class="fa fa-paperclip"></i></a>
                            <a href="{{ route('lc.shipments.create', ['lc' => $lc->lc_id]) }}" class="btn btn-success btn-xs" title="Add Shipment"><i class="fa fa-ship"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
