@extends('layouts.master')
@section('title','LC File ' . $file->lcf_file_no)
@section('lc_class','active')
@section('lc_file_class','active')
@section('lc_file_list_class','active')
@section('content')
@php($lc = $file->lc)
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>File {{ $file->lcf_file_no }}
            <span class="label {{ $file->lcf_status == 2 ? 'label-success' : ($file->lcf_status == 1 ? 'label-info' : 'label-default') }}" style="font-size: 50%; vertical-align: middle;">{{ $file->status_label }}</span>
        </h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="box-title">
                    File Details
                    <span class="pull-right">
                        @if($file->lcf_status == 1)
                        <a href="{{ route('lc.files.edit', $file->lcf_id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> Edit</a>
                        <a href="{{ route('lc.opening', ['file' => $file->lcf_id]) }}" class="btn btn-success btn-sm"><i class="fa fa-university"></i> Open LC</a>
                        <form action="{{ route('lc.files.cancel', $file->lcf_id) }}" method="post" style="display:inline-block;" onsubmit="return confirm('Cancel this file?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-ban"></i> Cancel File</button>
                        </form>
                        @endif
                        <a href="{{ route('lc.files.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    </span>
                </h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr><th width="40%">Supplier</th><td>{{ $file->supplier->inv_sup_com_name ?? 'N/A' }} ({{ $file->supplier->inv_sup_person ?? '' }})</td></tr>
                        <tr><th>File Date</th><td>{{ \Carbon\Carbon::parse($file->lcf_file_date)->format('d M Y') }}</td></tr>
                        <tr><th>PI No / Date</th><td>{{ $file->lcf_pi_no ?: '-' }} {{ $file->lcf_pi_date ? '/ ' . \Carbon\Carbon::parse($file->lcf_pi_date)->format('d M Y') : '' }}</td></tr>
                        <tr><th>Remarks</th><td>{{ $file->lcf_remarks }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr><th width="40%">PI Value</th><td class="text-right">{{ $file->lcf_currency }} {{ number_format($file->lcf_total_fc, 2) }}</td></tr>
                        <tr><th>Rate to BDT</th><td class="text-right">{{ $file->lcf_rate + 0 }}</td></tr>
                        <tr><th>PI Value in BDT</th><td class="text-right"><b>{{ number_format($file->lcf_total_bdt, 2) }}</b></td></tr>
                        <tr>
                            <th>LC</th>
                            <td class="text-right">
                                @if($lc)
                                    <a href="{{ route('lc.statement', ['lc' => $lc->lc_id]) }}"><b>{{ $lc->lc_no }}</b></a>
                                    ({{ $lc->bank->bank_info->bank_name ?? '' }})
                                @else
                                    <span class="text-muted">Not opened yet</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <h4>Products</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Product</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price ({{ $file->lcf_currency }})</th>
                        <th class="text-right">Total ({{ $file->lcf_currency }})</th>
                        <th class="text-right">Shipped</th>
                        <th class="text-right">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($file->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product_name }} @if($item->is_warranty)<span class="label label-default">warranty</span>@endif</td>
                        <td class="text-right">{{ $item->lcfi_qty + 0 }}</td>
                        <td class="text-right">{{ number_format($item->lcfi_unit_price_fc, 2) }}</td>
                        <td class="text-right">{{ number_format($item->lcfi_total_fc, 2) }}</td>
                        <td class="text-right">{{ $item->lcfi_shipped_qty + 0 }}</td>
                        <td class="text-right">{{ $item->lcfi_received_qty + 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total</th>
                        <th class="text-right">{{ number_format($file->lcf_total_fc, 2) }}</th>
                        <th class="text-right">{{ $file->items->sum('lcfi_shipped_qty') + 0 }}</th>
                        <th class="text-right">{{ $file->items->sum('lcfi_received_qty') + 0 }}</th>
                    </tr>
                </tfoot>
            </table>

            @if($lc)
            <h4>
                Shipments
                <a href="{{ route('lc.shipments.create', ['lc' => $lc->lc_id]) }}" class="btn btn-success btn-xs"><i class="fa fa-plus"></i> Add Shipment</a>
            </h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>No</th><th>B/L No</th><th>Vessel</th><th>Ship Date</th><th>Arrival</th><th class="text-right">Quantity</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($lc->shipments as $shipment)
                    <tr>
                        <td>{{ $shipment->lcs_no }}</td>
                        <td>{{ $shipment->lcs_bl_no }}</td>
                        <td>{{ $shipment->lcs_vessel }}</td>
                        <td>{{ $shipment->lcs_ship_date ? \Carbon\Carbon::parse($shipment->lcs_ship_date)->format('d M Y') : '' }}</td>
                        <td>{{ $shipment->lcs_arrival_date ? \Carbon\Carbon::parse($shipment->lcs_arrival_date)->format('d M Y') : '' }}</td>
                        <td class="text-right">{{ $shipment->items->sum('lcsi_qty') + 0 }}</td>
                        <td><span class="label {{ $shipment->lcs_status == 2 ? 'label-success' : 'label-warning' }}">{{ $shipment->status_label }}</span></td>
                        <td><a href="{{ route('lc.shipments.show', $shipment->lcs_id) }}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">No shipments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @endif
        </div>
    </div>
</section>
@endsection
