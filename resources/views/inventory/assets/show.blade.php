@extends('layouts.master')
@section('inventory_class','menu-open')
@section('asset_class','active')
@section('content')
<section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-cube"></i> Asset Details
            </h3>
            <div class="box-tools pull-right">
                <a href="{{ route('assets.index') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('assets.edit', $asset->asset_id) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <div class="box-body">
            @php
                $statusText = [
                    0 => 'Inactive',
                    1 => 'Active',
                    2 => 'Under Maintenance',
                    3 => 'Disposed'
                ];
                $statusBadge = [
                    0 => 'danger',
                    1 => 'success',
                    2 => 'warning',
                    3 => 'secondary'
                ];
            @endphp
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        @if($asset->asset_image)
                            <img src="{{ asset($asset->asset_image) }}" alt="Asset Image" style="max-width: 100%; max-height: 250px; border: 1px solid #ddd; padding: 5px;">
                        @else
                            <div style="border: 1px solid #ddd; padding: 40px; background: #f9f9f9; text-align: center;">
                                <i class="fa fa-cube" style="font-size: 80px; color: #ccc;"></i>
                                <p>No Image Available</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Asset Code</th>
                            <td><strong>{{ $asset->asset_code }}</strong></td>
                        </tr>
                        <tr>
                            <th>Asset Name</th>
                            <td>{{ $asset->asset_name }}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{ $asset->category->category_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Serial Number</th>
                            <td>{{ $asset->asset_serial_no ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Model Number</th>
                            <td>{{ $asset->asset_model_no ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $asset->supplier->inv_sup_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Purchase Date</th>
                            <td>{{ $asset->asset_purchase_date ? \Carbon\Carbon::parse($asset->asset_purchase_date)->format('d M Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Purchase Price</th>
                            <td class="text-right"><strong>{{ number_format($asset->asset_purchase_price, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Current Value</th>
                            <td class="text-right"><strong>{{ number_format($asset->asset_current_value, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Depreciation Rate</th>
                            <td>{{ $asset->asset_depreciation_rate }}%</td>
                        </tr>
                        <tr>
                            <th>Depreciation Method</th>
                            <td>{{ ucwords(str_replace('_', ' ', $asset->asset_depreciation_method ?? 'N/A')) }}</td>
                        </tr>
                        <tr>
                            <th>Warranty Expiry</th>
                            <td>{{ $asset->asset_warranty_expiry ? \Carbon\Carbon::parse($asset->asset_warranty_expiry)->format('d M Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>{{ $asset->asset_location ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Assigned To</th>
                            <td>{{ $asset->assignedTo->au_name ?? 'Not Assigned' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-{{ $statusBadge[$asset->asset_status] ?? 'secondary' }}">
                                    {{ $statusText[$asset->asset_status] ?? 'Unknown' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $asset->asset_description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>{{ $asset->asset_notes ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td>{{ $asset->submitBy->au_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $asset->created_at ? $asset->created_at->format('d M Y h:i A') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $asset->updated_at ? $asset->updated_at->format('d M Y h:i A') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .badge-success { background-color: #28a745; color: #fff; }
    .badge-danger { background-color: #dc3545; color: #fff; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-secondary { background-color: #6c757d; color: #fff; }
    .table th { background-color: #f5f5f5; }
</style>
@endsection