@extends('layouts.master')
@section('title','Add Shipment')
@section('lc_class','active')
@section('lc_shipment_class','active')
@section('lc_shipment_add_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>Add Shipment</h1>
    </section>

    <form method="get" action="{{ route('lc.shipments.create') }}" class="form-inline" style="margin-bottom: 15px;">
        <label for="lc_pick">LC&nbsp;</label>
        <select name="lc" id="lc_pick" class="form-control" style="min-width: 380px;" onchange="this.form.submit()">
            <option value="">Select LC</option>
            @foreach($lcs as $item)
            <option value="{{ $item->lc_id }}" {{ $lc && $lc->lc_id == $item->lc_id ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
    </form>

    @if($lc)
    @php($pending = $lc->file->items->sum('pending_ship_qty'))
    <form action="{{ route('lc.shipments.store') }}" method="post">
        @csrf
        <input type="hidden" name="lc_id" value="{{ $lc->lc_id }}">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3 class="box-title">Shipment under LC {{ $lc->lc_no }} <small>{{ $lc->file->supplier->inv_sup_com_name ?? '' }}</small></h3>
                </div>
            </div>
            <div class="panel-body">
                @if($pending <= 0)
                    <div class="alert alert-info"><i class="fa fa-info-circle"></i> Every product of this LC is already on a shipment.</div>
                @else
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bl_no">B/L No</label>
                            <input type="text" name="bl_no" id="bl_no" class="form-control" maxlength="100" value="{{ old('bl_no') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="vessel">Vessel / Flight</label>
                            <input type="text" name="vessel" id="vessel" class="form-control" maxlength="100" value="{{ old('vessel') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="invoice_no">Commercial Invoice No</label>
                            <input type="text" name="invoice_no" id="invoice_no" class="form-control" maxlength="100" value="{{ old('invoice_no') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control" maxlength="255" value="{{ old('remarks') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ship_date">Shipment Date</label>
                            <input type="text" name="ship_date" id="ship_date" class="form-control datepicker" autocomplete="off" value="{{ old('ship_date', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="eta">ETA</label>
                            <input type="text" name="eta" id="eta" class="form-control datepicker" autocomplete="off" value="{{ old('eta') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="arrival_date">Arrival Date</label>
                            <input type="text" name="arrival_date" id="arrival_date" class="form-control datepicker" autocomplete="off" value="{{ old('arrival_date') }}">
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">LC Quantity</th>
                            <th class="text-right">Already Shipped</th>
                            <th class="text-right">Left</th>
                            <th width="18%">In This Shipment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lc->file->items as $item)
                        <tr>
                            <td>{{ $item->product_name }} @if($item->is_warranty)<span class="label label-default">warranty</span>@endif</td>
                            <td class="text-right">{{ $item->lcfi_qty + 0 }}</td>
                            <td class="text-right">{{ $item->lcfi_shipped_qty + 0 }}</td>
                            <td class="text-right">{{ $item->pending_ship_qty + 0 }}</td>
                            <td>
                                <input type="number" name="qty[{{ $item->lcfi_id }}]" class="form-control text-right"
                                       min="0" max="{{ $item->pending_ship_qty + 0 }}" step="{{ $item->is_warranty ? 1 : 0.01 }}"
                                       value="{{ old('qty.' . $item->lcfi_id, $item->pending_ship_qty + 0) }}" {{ $item->pending_ship_qty <= 0 ? 'readonly' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-ship"></i> Save Shipment</button>
                </div>
                @endif
            </div>
        </div>
    </form>
    @endif
</section>
@endsection
