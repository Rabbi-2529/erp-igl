@extends('layouts.master')
@section('title','Fixed Asset List')
@section('inventory_class','active')
@section('asset_class','active')
@section('asset_list','active')
@section('content')
<section class="content">
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <section class="content-header">
        <h1>Fixed Assets</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Asset List</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('assets.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Asset Name</label>
                                    <input type="text" name="asset_name" class="form-control" 
                                           value="{{ request('asset_name') }}" placeholder="Search asset...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Asset Code</label>
                                    <input type="text" name="asset_code" class="form-control" 
                                           value="{{ request('asset_code') }}" placeholder="Search code...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->category_id }}" 
                                                {{ request('category_id') == $cat->category_id ? 'selected' : '' }}>
                                                {{ $cat->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Under Maintenance</option>
                                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Disposed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10px;">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                                <a href="{{ route('assets.index') }}" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
                                <a href="{{ route('assets.create') }}" class="btn btn-primary pull-right">
                                    <i class="fa fa-plus"></i> Add New Asset
                                </a>
                                <a href="{{ route('assets.categories') }}" class="btn btn-info pull-right" style="margin-right: 5px;">
                                    <i class="fa fa-tags"></i> Categories
                                </a>
                                <a href="{{ route('assets.download.pdf') }}" class="btn btn-warning pull-right" style="margin-right: 5px;" target="_blank">
                                    <i class="fa fa-download"></i> PDF
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Serial No</th>
                                    <th>Purchase Price</th>
                                    <th>Current Value</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sl = 0;
                                    $totalPurchase = 0;
                                    $totalCurrent = 0;
                                @endphp
                                @forelse($assets as $asset)
                                @php
                                    $sl++;
                                    $totalPurchase += $asset->asset_purchase_price;
                                    $totalCurrent += $asset->asset_current_value;
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
                                <tr>
                                    <td>{{ $sl }}</td>
                                    <td><strong>{{ $asset->asset_code }}</strong></td>
                                    <td>{{ $asset->asset_name }}</td>
                                    <td>{{ $asset->category->category_name ?? 'N/A' }}</td>
                                    <td>{{ $asset->asset_serial_no ?? 'N/A' }}</td>
                                    <td class="text-right">{{ number_format($asset->asset_purchase_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($asset->asset_current_value, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusBadge[$asset->asset_status] ?? 'secondary' }}">
                                            {{ $statusText[$asset->asset_status] ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td align="center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info btn-xs">Action</button>
                                            <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                                <li>
                                                    <a href="{{ route('assets.show', $asset->asset_id) }}">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="{{ route('assets.edit', $asset->asset_id) }}">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="{{ route('assets.status', [$asset->asset_id, 1]) }}">
                                                        <i class="fa fa-check-circle text-success"></i> Activate
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('assets.status', [$asset->asset_id, 0]) }}">
                                                        <i class="fa fa-times-circle text-danger"></i> Deactivate
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('assets.status', [$asset->asset_id, 2]) }}">
                                                        <i class="fa fa-wrench text-warning"></i> Maintenance
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('assets.status', [$asset->asset_id, 3]) }}">
                                                        <i class="fa fa-trash text-secondary"></i> Disposed
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="javascript:void(0);" onclick="if(confirm('Are you sure you want to delete this asset?')) { document.getElementById('delete-form-{{ $asset->asset_id }}').submit(); }">
                                                        <i class="fa fa-trash text-danger"></i> Delete
                                                    </a>
                                                    <form id="delete-form-{{ $asset->asset_id }}" action="{{ route('assets.destroy', $asset->asset_id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-danger">
                                        <strong>No assets found</strong>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f5f5f5; font-weight: bold;">
                                    <td colspan="5" class="text-right"><strong>Total Value:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalPurchase, 2) }}</strong></td>
                                    <td class="text-right"><strong>{{ number_format($totalCurrent, 2) }}</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            {{ $assets->appends(request()->all())->links() }}
                        </div>
                    </div>
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
    .table th, .table td { vertical-align: middle; }
    .dropdown-menu > li > a { padding: 5px 15px; }
    .btn-group { display: inline-flex; flex-wrap: wrap; gap: 2px; }
</style>
@endsection