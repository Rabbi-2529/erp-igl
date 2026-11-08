<table id="sell_product_list_table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Notes</th>
                <th>A. S.</th>
                
                <th>Sell Price</th>
                <th>Short</th>
                <th>Remarks</th>
                <th>Sell</th>
            </tr>
        </thead>
        <tbody id="product_table_body">
@php($sl=0)
@foreach ($currencies as $currency)
    <tr>
        <td class="text-center">{{ ++$sl }}</td>
        <td align="center">{{ $currency->country_name['currency_code'] }}</td>
        <td class="text-center">
            @if ($currency->country_name['currency_symbol'] != '')
            ({{ $currency->country_name['currency_symbol'] }})
            @endif
            {{ $currency->currency_note }}
        </td>
        <td align="center"></td>
        
        <td class="text-center">
            <input type="text" class="form-control" id="pro_price_{{ $currency->id }}" style="width: 100px;" value="">
        </td>
        <td>
            
        </td>
        <td class="text-center">
            <input type="text" name="remarks" class="form-control" id="short_remarks_{{ $currency->id }}" placeholder="Reason of short quatity..">
        </td>
        <td class="text-center">
            
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $currency->id }}" placeholder="Qty">
            <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $currency->id }}')">
                <i class="fa fa-plus"></i>
            </button>
            
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