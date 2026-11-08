@extends('layouts.master')
@section('title', 'Product Type')
@section('inventory_class', 'active')
@section('product_class', 'active')
@section('inv_pro_type', 'active')
@section('add_type', 'active')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add Product Type</h4>
                </div>
            </div>
            @if(session()->has('pro_type'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('pro_type') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <div class="panel-body">
                {{ Form::open(['action' => 'Inventory\InvProductController@product_type_submit', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'customer_create_form']) }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="group" class="col-sm-2 control-label">Group
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <select name="group" class="form-control select2" id="group">
                                <option value="">Select</option>
                                @foreach ($pro_grp as $grp)
                                <option value="{{ $grp->inv_pro_grp_id }}">{{ $grp->inv_pro_grp_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($errors->has('group'))
                        <p class="text-danger">{{ $errors->first('group') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="type" class="col-sm-2 control-label">Type Name
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" name="type" autocomplete="off" value="{{ old('type') }}" class="form-control" id="type" placeholder="Enter Product Type Name">
                        </div>
                        @if($errors->has('type'))
                        <p class="text-danger">{{ $errors->first('type') }}</p>
                        @endif
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-info pull-right">Create</button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection