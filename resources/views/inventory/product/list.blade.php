@extends('layouts.master')
@section('title', 'Product List')

@section('inventory_class','active')
@section('product_class','active')
@section('inv_pro_class','active')
@section('pro_list','active')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h1>Product List</h1>
                </div>
            </div>

            @if(session()->has('det_up'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('det_up') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(session()->has('pro_del'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('pro_del') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <div class="panel-body">
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Type Name</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Warranty</th>
                                <th>Available Stock</th>
                                <th>Buy Price</th>
                                <th>Sell Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($sl = 0)
                            @foreach ($pro_det as $product)
                                @php($detail = $product->details->first())
                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>{{ $product->type_info->inv_pro_type_name ?? 'N/A' }}</td>
                                    <td>{{ $product->inv_pro_name ?? 'N/A' }}</td>
                                    <td>{{ $product->inv_pro_description ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if (($detail->inv_pro_det_pro_warranty ?? 0) == 0)
                                            No Warranty
                                        @else
                                            {{ $detail->inv_pro_det_pro_warranty }} days
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $detail->inv_pro_det_available_qty ?? 0 }}</td>
                                    <td class="text-right">{{ $detail->inv_pro_det_buy_price ?? 0 }}</td>
                                    <td class="text-right">{{ $detail->inv_pro_det_sell_price ?? 0 }}</td>
                                    <td align="center">
                                        @if($detail)
                                            <a href="{{ route('inventory.product_show', $detail->inv_pro_det_id) }}" class="action_btn btn_show">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory.product_delete', $detail->inv_pro_det_id) }}" onclick="return deleteprocat()" class="action_btn btn_delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <a href="{{ route('inventory.product_edit_page', $detail->inv_pro_det_id) }}" class="action_btn btn_edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('custom_style')
<style>
    .action_btn {
        border: 1px solid;
        padding: 5px;
    }

    .btn_show {
        background-color: green;
        color: white;
    }

    .btn_delete {
        background-color: red;
        color: white;
    }

    .btn_edit {
        background-color: cornflowerblue;
        color: white;
    }
</style>
@endsection

@section('custom_script')
<script type="text/javascript">
    function deleteprocat() {
        let clickDel = confirm("Are you sure want to delete this?");
        if (clickDel == true) {
            return true;
        } else {
            return false;
        }
    }
</script>
@endsection