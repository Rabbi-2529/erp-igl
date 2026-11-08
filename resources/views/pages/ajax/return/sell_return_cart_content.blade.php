@foreach($cart_content as $content)

    <tr>
        <td >
            {{ $content->inv_pro_temp_pro_name }}, {{ $content->inv_pro_temp_type_name }}
            <br>
            <b>{{ implode(', ', explode(',',$content->inv_pro_temp_slno)) }}</b>
        </td>
        <td align="center">
            {{ $content->inv_pro_temp_qty }}
        </td>
        
        <td class="text-right">{{ number_format($content->inv_pro_temp_unit_price, 2) }}</td>
        <td class="text-right">{{ number_format(($content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty), 2) }}</td>
        <td class="text-center">
            <a href="javascript:void(0);" onclick="remove_cart({{ $content->inv_pro_temp_pro_id }})" class="btn btn-sm btn-danger">
                <i class="fa fa-minus"></i>
            </a>
        </td>
    </tr>
@endforeach