@extends('layouts.master')
@section('title', 'Purchase Product')

@section('inventory_class','active')
@section('pro_inv_class','active')

@section('inv_buy_class','active')

@section('purchase_list','active')
@section('content')
<section class="content">
    <section class="content-header">
        @if(Session::has('errmsg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('errmsg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        @if(Session::has('msg'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('msg') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
  <div class="btn-group" style="float:right;">
                            <a href="{{ route('buy.buy-product-new') }}" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add New Purchase
                            </a>
                        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3 class="box-title">Product Purchases List</h3>
                        </div>
                      
                    </div>

                    <div class="panel-body">
                        <div class="table-responsive">
                              <table id="example1" class="table table-bordered table-striped table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Memo No</th>
                                        <th>Supplier</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $key => $purchase)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ date('d-m-Y', strtotime($purchase->inv_pro_inv_issue_date)) }}</td>
                                        <td>{{ $purchase->inv_pro_inv_invoice_no }}</td>
                                        <td>{{ $purchase->getSupplierInfo->inv_sup_com_name ?? 'N/A' }}</td>
                                        <td>{{ $purchase->product_detail->product->inv_pro_name ?? 'N/A' }}</td>
                                        <td>{{ $purchase->inv_pro_inv_qty }}</td>
                                        <td class="text-right">{{ number_format($purchase->inv_pro_inv_unit_price ?? 0, 2) }}</td>
                                              <td class="text-right">
    {{ number_format($purchase->inv_pro_inv_debit ?? 0, 2) }}
</td>
                                        <td>
                                            @if($purchase->inv_pro_inv_status == 1)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('buy.purchase.view', $purchase->inv_pro_inv_id) }}" 
                                                   class="btn btn-info btn-xs" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('reports.buy-pdf', ['invoice' => $purchase->inv_pro_inv_invoice_no]) }}?print=1" target="_blank"
                                                   class="btn btn-default btn-xs" title="Print Invoice">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('buy.purchase.edit', $purchase->inv_pro_inv_id) }}" 
                                                   class="btn btn-primary btn-xs" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('buy.purchase.delete', $purchase->inv_pro_inv_id) }}" 
                                                      method="POST" style="display:inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs" 
                                                            onclick="return confirm('Are you sure to delete this purchase?')"
                                                            title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Total:</th>
                                        <th>{{ $purchases->sum('inv_pro_inv_qty') }}</th>
                                        <th></th>
                                        <th class="text-right">{{ number_format($purchases->sum('inv_pro_inv_debit'), 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

@section('scripts')
<script>
    $(document).ready(function() {
        $('#purchaseTable').DataTable({
            "order": [[1, "desc"]],
            "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            "language": {
                "lengthMenu": "Show _MENU_ entries per page",
                "zeroRecords": "No purchases found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "search": "_INPUT_",
                "searchPlaceholder": "Search purchases...",
                "paginate": {
                    "previous": "<i class='fa fa-angle-left'></i>",
                    "next": "<i class='fa fa-angle-right'></i>"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [9] },
                { "className": "text-center", "targets": [0,5,8,9] },
                { "className": "text-right", "targets": [6,7] }
            ]
        });
    });
</script>
@endsection
@endsection