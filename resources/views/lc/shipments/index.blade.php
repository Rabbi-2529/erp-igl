@extends('layouts.master')
@section('title','LC Shipments')
@section('lc_class','active')
@section('lc_shipment_class','active')
@section('lc_shipment_list_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>Shipment Details</h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="box-title">
                    Shipments
                    <a href="{{ route('lc.shipments.create') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Add Shipment</a>
                </h3>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>LC No</th>
                        <th>No</th>
                        <th>Supplier</th>
                        <th>B/L No</th>
                        <th>Vessel</th>
                        <th>Ship Date</th>
                        <th>ETA</th>
                        <th>Arrival</th>
                        <th class="text-right">Quantity</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipments as $shipment)
                    <tr>
                        <td><b>{{ $shipment->lc->lc_no ?? '' }}</b></td>
                        <td>{{ $shipment->lcs_no }}</td>
                        <td>{{ $shipment->lc->file->supplier->inv_sup_com_name ?? '' }}</td>
                        <td>{{ $shipment->lcs_bl_no }}</td>
                        <td>{{ $shipment->lcs_vessel }}</td>
                        <td data-order="{{ $shipment->lcs_ship_date }}">{{ $shipment->lcs_ship_date ? \Carbon\Carbon::parse($shipment->lcs_ship_date)->format('d M Y') : '' }}</td>
                        <td data-order="{{ $shipment->lcs_eta }}">{{ $shipment->lcs_eta ? \Carbon\Carbon::parse($shipment->lcs_eta)->format('d M Y') : '' }}</td>
                        <td data-order="{{ $shipment->lcs_arrival_date }}">{{ $shipment->lcs_arrival_date ? \Carbon\Carbon::parse($shipment->lcs_arrival_date)->format('d M Y') : '' }}</td>
                        <td class="text-right">{{ $shipment->items->sum('lcsi_qty') + 0 }}</td>
                        <td><span class="label {{ $shipment->lcs_status == 2 ? 'label-success' : 'label-warning' }}">{{ $shipment->status_label }}</span></td>
                        <td><a href="{{ route('lc.shipments.show', $shipment->lcs_id) }}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> {{ $shipment->lcs_status == 1 ? 'View / Receive' : 'View' }}</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
