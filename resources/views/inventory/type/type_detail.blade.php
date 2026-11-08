@extends('layouts.master')
@section('title', 'Category List')
@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_type','active')
@section('type_list','active')
@section('content')
<section class="content">
<section class="content-header">
  <div class="row">
    <div class="pull-left">
      <h3 style="margin: 4px;">
        Type
      </h3>
    </div>
  </div>

    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Type Details</h3>
              <div class="box-tools">
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            <h2 class="text-center">Type Name: {{ $type_info->inv_pro_type_name }}</h2>
              <table class="table table-bordered table-striped table-hover datatable">
                <tr>
                  	<th>Company ID</th>
                  	<td>{{ $type_info->inv_pro_type_com_id }}</td>
                </tr>
                <tr>
                  	<th>Submitted By</th>
                  	<td>{{ $type_info->type_submit_by['au_name'] }}</td>
                </tr>
                <tr>
                    <th>Group Name</th>
                    <td>{{ $type_info->type_category['inv_pro_grp_name'] }}</td>
                </tr>
                <tr>
                	<th>Status</th>
                	<td>
                    @if($type_info->inv_pro_type_status == 1)
                    Activate
                    @elseif($type_info->inv_pro_type_status == 0)
                    Deactivate
                    @endif
                  </td>
                </tr>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
