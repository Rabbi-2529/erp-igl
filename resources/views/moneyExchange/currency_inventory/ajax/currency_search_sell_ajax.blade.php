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
@foreach ($currencies as $currency)
    <tr>
        <td class="text-center">{{ ++$sl }}</td>
        <td>{{ $currency->currency_note['currency_note'] }}</td>
        <td></td>
        <td align="center">{{$currency->mex_curr_det_available_qty}}</td>
        <td class="text-center"><input type="text" autocomplete="off" class="form-control" id="pro_price_" style="width: 100px;" value="{{ $currency->mex_curr_det_buy_price }}"></td>
        <td class="text-center">
            {{-- @if($sell->inv_pro_det_pro_warranty == 0)
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $sell->inv_pro_det_id }}" placeholder="Qty">
            <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->inv_pro_det_id }}')">
                <i class="fa fa-plus"></i>
            </button>
            @else
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" placeholder="N/A" disabled>
            <button type="button" class="btn btn-success btn-sm" onclick="addWarrentyProduct('{{ $sell->inv_pro_det_id }}')">
                    <i class="fa fa-plus"></i>
            </button>
            @endif --}}
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