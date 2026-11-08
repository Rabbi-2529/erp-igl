@extends('layouts.master')
@section('title','Asset Categories')
@section('inventory_class','active')
@section('asset_class','active')
@section('asset_categories','active')
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
        <h1>Asset Categories</h1>
    </section>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-plus"></i> Add Category
                    </h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('assets.categories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="category_name" class="form-control @error('category_name') is-invalid @enderror" 
                                   value="{{ old('category_name') }}" placeholder="Enter category name" required>
                            @error('category_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Category Code</label>
                            <input type="text" name="category_code" class="form-control" 
                                   value="{{ old('category_code') }}" placeholder="Enter category code">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="category_description" class="form-control" rows="2" placeholder="Enter description">{{ old('category_description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="category_status" class="form-control">
                                <option value="1" {{ old('category_status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('category_status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-tags"></i> Categories List
                    </h3>
                    <div class="box-tools pull-right">
                        <span class="label label-primary">Total: {{ $categories->total() }}</span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="categoryTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Category Name</th>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 30%;">Description</th>
                                    <th style="width: 10%;">Assets</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($sl = 0)
                                @forelse($categories as $category)
                                @php($sl++)
                                <tr>
                                    <td class="text-center">{{ $sl }}</td>
                                    <td>{{ $category->category_name }}</td>
                                    <td>{{ $category->category_code ?? 'N/A' }}</td>
                                    <td>{{ $category->category_description ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $category->assets()->count() }}</td>
                                    <td>
                                        <span class="badge badge-{{ $category->category_status == 1 ? 'success' : 'danger' }}">
                                            {{ $category->category_status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#editModal{{ $category->category_id }}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <form action="{{ route('assets.categories.destroy', $category->category_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                            @if($category->category_status == 1)
                                                <a href="{{ route('assets.categories.status', [$category->category_id, 0]) }}" class="btn btn-warning btn-xs" title="Deactivate">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('assets.categories.status', [$category->category_id, 1]) }}" class="btn btn-success btn-xs" title="Activate">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{ $category->category_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Edit Category</h4>
                                            </div>
                                            <form action="{{ route('assets.categories.update', $category->category_id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Category Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="category_name" class="form-control" 
                                                               value="{{ $category->category_name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Category Code</label>
                                                        <input type="text" name="category_code" class="form-control" 
                                                               value="{{ $category->category_code }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <textarea name="category_description" class="form-control" rows="2">{{ $category->category_description }}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="category_status" class="form-control">
                                                            <option value="1" {{ $category->category_status == 1 ? 'selected' : '' }}>Active</option>
                                                            <option value="0" {{ $category->category_status == 0 ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        <strong>No categories found</strong>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            {{ $categories->appends(request()->all())->links() }}
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
    .badge-info { background-color: #17a2b8; color: #fff; }
    .modal-header { background-color: #f5f5f5; border-bottom: 1px solid #ddd; }
    .box-primary { border-top-color: #3c8dbc; }
    .table th, .table td { vertical-align: middle; }
    .btn-group { display: inline-flex; gap: 3px; flex-wrap: wrap; }
    .text-danger { color: #dc3545; }
    .is-invalid { border-color: #dc3545; }
</style>
@endsection

@section('custom_script')
<script>
$(document).ready(function() {
    // Initialize DataTable if needed
    // $('#categoryTable').DataTable();
});
</script>
@endsection