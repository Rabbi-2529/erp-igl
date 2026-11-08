@extends('layouts.master')
@section('title', 'Update Product Type ')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_type','active')
@section('type_list','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('pro_type'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('pro_type') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Product type
              </h1>       
            </section>
            <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                <h3 class="box-title">Update Product type</h3>
                </div>
            </div>

            <div class="panel-body">
                
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['action' => ['Inventory\InvProductController@pro_type_edit',$mod_edit->inv_pro_type_id], 'method' => 'post' , 'class' => 'form-horizontal', 'id' => 'customer_create_form'])}}
                      <div class="box-body">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Cateogry
                              <span class="text-danger">*</span>
                            </label>
                            
                            <div class="col-sm-6">
                                <select name="group" class="form-control select2" id="">
                                    <option value="">Select</option>
                                    @foreach ($pro_grp as $grp)
                                     <option value="{{ $grp->inv_pro_grp_id }}"
                                        {{ $mod_edit->inv_pro_type_grp_id==$grp->inv_pro_grp_id?'selected':'' }}>
                                        {{ $grp->inv_pro_grp_name }}
                                    </option>   
                                    @endforeach
                                </select>
                            </div>
                                @if($errors->has('group'))
                                    <p class="text-danger">{{ $errors->first('group') }}</p>
                                @endif
                        </div>
                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> type Name
                            <span class="text-danger">*</span>
                          </label>
                          <div class="col-sm-6">
                            <input type="text" name="type" autocomplete="off" value="{{ $mod_edit->inv_pro_type_name }}" class="form-control" id="inputEmail3" placeholder="Enter Product type Name" >
                          </div>
                            @if($errors->has('type'))
                                <p class="text-danger">{{ $errors->first('type') }}</p>
                            @endif
                        </div>
                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="" class="btn btn-info pull-right">Create</button>
                         </div>
                      </div>
                      <!-- /.box-footer -->
                    {{ Form::close() }}
                  </div>
                  </div>
                  </div>
                  </div>
@endsection