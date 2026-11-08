@foreach($cart_content as $content)

    <tr>
        <td >
            {{ $content->mex_curr_temp_curr_name }}, {{ $content->mex_curr_temp_type_name }}
        </td>
        <td align="center">
            {{ $content->mex_curr_temp_qty }}
        </td>
        <td class="text-right">{{ number_format($content->mex_curr_temp_unit_price, 2) }}</td>
        <td class="text-right temp_cart">{{ number_format(($content->mex_curr_temp_unit_price * $content->mex_curr_temp_qty), 2) }}</td>
        <td class="text-center">{{ number_format($content->mex_curr_temp_short_qty, 2) }}</td>
        <td>{{ $content->mex_curr_temp_short_remarks }}</td>
        <td class="text-center">
            <a href="javascript:void(0);" onclick="remove_cart({{ $content->mex_curr_temp_curr_id }})" class="btn btn-sm btn-danger">
                <i class="fa fa-minus"></i>
            </a>
        </td>
    </tr>
@endforeach