<table id="sell_product_list_table" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>SL</th>
            <th>Name</th>
            <th>Type</th>
            <th>A. Stock</th>
            <th>Price</th>
            <th style="width: 110px; text-align: center;">Sell</th>
        </tr>
        </thead>
        <tbody id="product_table_body">
@php($sl=0)
@foreach ($sell_product as $sell)
    <tr>
        <td class="text-center">{{ ++$sl }}</td>
      <td>{{ optional($sell->product)->inv_pro_name }}</td>
        <td>{{ $sell->type_info['inv_pro_type_name'] }}</td>
        <td align="center">{{ $sell->inv_pro_det_available_qty }}</td>
        <td class="text-center"><input type="text" autocomplete="off" class="form-control" id="pro_price_{{ $sell->inv_pro_det_id }}" style="width: 100px;" value="{{ $sell->inv_pro_det_sell_price }}"></td>
        <td class="text-center">
            @if($sell->inv_pro_det_pro_warranty == 0)
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $sell->inv_pro_det_id }}" placeholder="Qty">
            <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->inv_pro_det_id }}')">
                <i class="fa fa-plus"></i>
            </button>
            @else
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" placeholder="N/A" disabled>
            <button type="button" class="btn btn-success btn-sm" onclick="addWarrentyProduct('{{ $sell->inv_pro_det_id }}')">
                    <i class="fa fa-plus"></i>
            </button>
            @endif
        </td>
    </tr>
@endforeach

</tbody>
</table>


<script>
$('#sell_product_list_table').DataTable( {
    pageLength : 5,
    lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
} );
</script>