@extends('layouts.master')
@section('title','Add Fixed Asset')
@section('inventory_class','active')
@section('asset_class','active')
@section('asset_add','active')
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
        <h1>Add New Fixed Asset</h1>
    </section>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4><i class="fa fa-plus"></i> Asset Information</h4>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Name <span class="text-danger">*</span></label>
                                    <input type="text" name="asset_name" class="form-control @error('asset_name') is-invalid @enderror" 
                                           value="{{ old('asset_name') }}" placeholder="Enter asset name" required>
                                    @error('asset_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Code <span class="text-danger">*</span></label>
                                    <input type="text" name="asset_code" class="form-control" 
                                           value="{{ $assetCode }}" readonly>
                                    <small class="text-muted">Auto-generated</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category <span class="text-danger">*</span></label>
                                    <select name="asset_category_id" class="form-control @error('asset_category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}" {{ old('asset_category_id') == $category->category_id ? 'selected' : '' }}>
                                                {{ $category->category_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('asset_category_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" name="asset_serial_no" class="form-control" 
                                           value="{{ old('asset_serial_no') }}" placeholder="Enter serial number">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Model Number</label>
                                    <input type="text" name="asset_model_no" class="form-control" 
                                           value="{{ old('asset_model_no') }}" placeholder="Enter model number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select name="asset_supplier_id" class="form-control">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->inv_sup_id }}" {{ old('asset_supplier_id') == $supplier->inv_sup_id ? 'selected' : '' }}>
                                                {{ $supplier->inv_sup_com_name ?? $supplier->inv_sup_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Purchase Date</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" name="asset_purchase_date" class="form-control datepicker" 
                                               value="{{ old('asset_purchase_date') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Purchase Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="asset_purchase_price" class="form-control @error('asset_purchase_price') is-invalid @enderror" 
                                           value="{{ old('asset_purchase_price', 0.00) }}" required>
                                    @error('asset_purchase_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Current Value</label>
                                    <input type="number" step="0.01" name="asset_current_value" class="form-control" 
                                           value="{{ old('asset_current_value') }}" placeholder="0.00">
                                    <small class="text-muted">Leave blank to use purchase price</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Depreciation Rate (%)</label>
                                    <input type="number" step="0.01" name="asset_depreciation_rate" class="form-control" 
                                           value="{{ old('asset_depreciation_rate', 0) }}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Depreciation Method</label>
                                    <select name="asset_depreciation_method" class="form-control">
                                        <option value="straight_line" {{ old('asset_depreciation_method') == 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                                        <option value="declining_balance" {{ old('asset_depreciation_method') == 'declining_balance' ? 'selected' : '' }}>Declining Balance</option>
                                        <option value="units_of_production" {{ old('asset_depreciation_method') == 'units_of_production' ? 'selected' : '' }}>Units of Production</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Warranty Expiry</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" name="asset_warranty_expiry" class="form-control datepicker" 
                                               value="{{ old('asset_warranty_expiry') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="asset_location" class="form-control" 
                                           value="{{ old('asset_location') }}" placeholder="Enter location">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assigned To</label>
                                    <select name="asset_assigned_to" class="form-control">
                                        <option value="">Not Assigned</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->au_id }}" {{ old('asset_assigned_to') == $user->au_id ? 'selected' : '' }}>
                                                {{ $user->au_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="asset_status" class="form-control">
                                        <option value="1" {{ old('asset_status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('asset_status') == 0 ? 'selected' : '' }}>Inactive</option>
                                        <option value="2" {{ old('asset_status') == 2 ? 'selected' : '' }}>Under Maintenance</option>
                                        <option value="3" {{ old('asset_status') == 3 ? 'selected' : '' }}>Disposed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asset Image</label>
                                    <input type="file" name="asset_image" class="form-control" accept="image/*">
                                    <small class="text-muted">Supported formats: JPG, PNG, GIF (Max: 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="asset_description" class="form-control" rows="3" placeholder="Enter asset description">{{ old('asset_description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="asset_notes" class="form-control" rows="2" placeholder="Additional notes">{{ old('asset_notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Asset
                                </button>
                                <a href="{{ route('assets.index') }}" class="btn btn-default">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('custom_style')
<style>
    .is-invalid { border-color: #dc3545; }
    .text-danger { color: #dc3545; font-size: 12px; }
    .panel-bd { border: 1px solid #ddd; border-radius: 4px; }
    .panel-heading { background: #f5f5f5; padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
    .panel-title h4 { margin: 0; font-size: 16px; }
    .panel-body { padding: 15px; }
    .input-group-addon { background: #f5f5f5; border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px 0 0 4px; }
    .input-group.date { display: table; width: 100%; }
    .input-group.date .form-control { display: table-cell; width: 100%; border-radius: 0 4px 4px 0; }
    .input-group.date .input-group-addon { display: table-cell; width: 1%; white-space: nowrap; vertical-align: middle; }
    .datepicker { cursor: pointer; }
</style>
@endsection

@section('custom_script')
<script>
$(document).ready(function() {
    // Initialize datepickers
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        todayBtn: true,
        clearBtn: true
    });
});
</script>
@endsection