@extends('layouts.master')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Top Selling Product List</h4>
                </div>
            </div>
            <div class="panel-body">
                <h4>Top Selling Products</h4>
                <table class="datatable table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $key => $product)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $product->inv_pro_det_pro_id }}</td>
                                <td>{{ $product->total_sold }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                
            </div>
        </div>
    </div>
</div>
@endsection
