@extends('layouts.master')
@section('title','Shipment ' . $shipment->lcs_no . ' - LC ' . $lc->lc_no)
@section('lc_class','active')
@section('lc_shipment_class','active')
@section('lc_shipment_list_class','active')
@section('content')
@php
    $landedFactor = $lc->landedFactor();
@endphp
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>Shipment {{ $shipment->lcs_no }} <small>LC {{ $lc->lc_no }}</small>
            <span class="label {{ $shipment->lcs_status == 2 ? 'label-success' : 'label-warning' }}" style="font-size: 50%; vertical-align: middle;">{{ $shipment->status_label }}</span>
        </h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="box-title">
                    {{ $lc->file->supplier->inv_sup_com_name ?? '' }}
                    <span class="pull-right">
                        <a href="{{ route('lc.statement', ['lc' => $lc->lc_id]) }}" class="btn btn-default btn-sm"><i class="fa fa-list"></i> LC Statement</a>
                        @if($shipment->lcs_status == 1)
                        <form action="{{ route('lc.shipments.delete', $shipment->lcs_id) }}" method="post" style="display:inline-block;" onsubmit="return confirm('Delete this shipment?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                        </form>
                        @endif
                        <a href="{{ route('lc.shipments.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                    </span>
                </h3>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th>B/L No</th><td>{{ $shipment->lcs_bl_no ?: '-' }}</td>
                    <th>Vessel / Flight</th><td>{{ $shipment->lcs_vessel ?: '-' }}</td>
                    <th>Commercial Invoice</th><td>{{ $shipment->lcs_invoice_no ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Shipment Date</th><td>{{ $shipment->lcs_ship_date ? \Carbon\Carbon::parse($shipment->lcs_ship_date)->format('d M Y') : '-' }}</td>
                    <th>ETA</th><td>{{ $shipment->lcs_eta ? \Carbon\Carbon::parse($shipment->lcs_eta)->format('d M Y') : '-' }}</td>
                    <th>Arrival</th><td>{{ $shipment->lcs_arrival_date ? \Carbon\Carbon::parse($shipment->lcs_arrival_date)->format('d M Y') : '-' }}</td>
                </tr>
                @if($shipment->lcs_status == 2)
                <tr>
                    <th>Purchase Invoice</th><td>{{ $shipment->lcs_purchase_invoice_no }}</td>
                    <th>Received</th><td colspan="3">{{ \Carbon\Carbon::parse($shipment->lcs_received_at)->format('d M Y, h:i A') }}</td>
                </tr>
                @endif
                @if($shipment->lcs_remarks)
                <tr><th>Remarks</th><td colspan="5">{{ $shipment->lcs_remarks }}</td></tr>
                @endif
            </table>

            @if($shipment->lcs_status == 1)
            <form action="{{ route('lc.shipments.receive', $shipment->lcs_id) }}" method="post" id="receive_form">
                @csrf
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Goods Cost / Unit (BDT)</th>
                        <th class="text-right">Landed Cost / Unit (BDT)</th>
                        @if($shipment->lcs_status == 1)
                        <th width="32%">Serial Numbers (warranty products: one per line)</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipment->items as $shipItem)
                    @php
                        $fileItem = $shipItem->fileItem;
                        $goodsUnit = round($fileItem->lcfi_unit_price_fc * $lc->lc_rate, 2);
                    @endphp
                    <tr>
                        <td>{{ $fileItem->product_name }} @if($fileItem->is_warranty)<span class="label label-default">warranty</span>@endif</td>
                        <td class="text-right">{{ $shipItem->lcsi_qty + 0 }}</td>
                        <td class="text-right">{{ number_format($goodsUnit, 2) }}</td>
                        <td class="text-right"><b>{{ number_format(round($goodsUnit * $landedFactor, 2), 2) }}</b></td>
                        @if($shipment->lcs_status == 1)
                        <td>
                            @if($fileItem->is_warranty)
                            <textarea name="serials[{{ $shipItem->lcsi_id }}]" class="form-control serials" rows="3" data-qty="{{ (int) $shipItem->lcsi_qty }}"
                                      placeholder="{{ (int) $shipItem->lcsi_qty }} serial numbers">{{ old('serials.' . $shipItem->lcsi_id) }}</textarea>
                            <small class="serial-count text-muted"></small>
                            @else
                            <span class="text-muted">Not needed</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($shipment->lcs_status == 1)
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="receive_date">Receive Date <span class="text-danger">*</span></label>
                            <input type="text" name="receive_date" id="receive_date" class="form-control datepicker" autocomplete="off" value="{{ old('receive_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- Not wrapped in .checkbox: the theme hides checkboxes inside it --}}
                        <div style="margin-top: 30px;">
                            <label style="font-weight: normal;">
                                <input type="checkbox" name="update_buy_price" value="1" {{ old('_token') && !old('update_buy_price') ? '' : 'checked' }}>
                                Set each product's buy price to its landed cost per unit
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 text-right">
                        <button type="submit" class="btn btn-success" style="margin-top: 24px;" onclick="return confirm('Receive this shipment into stock?');">
                            <i class="fa fa-download"></i> Receive into Stock
                        </button>
                    </div>
                </div>
                <p class="text-muted">
                    Receiving adds the quantities to stock and records a purchase from the supplier at the LC goods price
                    (purchase invoice LC-{{ $lc->lc_no }}-S{{ $shipment->lcs_no }}). Add all LC costs first so the landed cost is complete.
                </p>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script type="text/javascript">
$(document).ready(function () {
    // Live count of the serial numbers typed for each warranty product
    function countSerials(box) {
        var serials = $(box).val().split(/[\r\n,]+/).map(function (s) { return s.trim(); }).filter(function (s) { return s !== ''; });
        var need = parseInt($(box).data('qty'), 10);
        $(box).siblings('.serial-count')
            .text(serials.length + ' of ' + need + ' entered')
            .toggleClass('text-danger', serials.length !== need)
            .toggleClass('text-success', serials.length === need);
    }
    $('.serials').on('input', function () { countSerials(this); }).each(function () { countSerials(this); });
});
</script>
@endsection
