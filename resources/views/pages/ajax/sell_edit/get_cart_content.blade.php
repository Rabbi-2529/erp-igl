@php($delivery_charge = '')
@php($discount_amount = '')
@foreach($cart_content as $content)
    @if(($content->inv_pro_temp_deal_type == 4) || ($content->inv_pro_temp_deal_type == 6))
    <tr>
        <td >
            {{ $content->inv_pro_temp_pro_name }}, {{ $content->inv_pro_temp_type_name }}
            <br>
            <b>{{ implode(', ', explode(',',$content->inv_pro_temp_slno)) }}</b>
        </td>
        <td align="center">
            {{ $content->inv_pro_temp_qty }}
        </td>
        
        <td class="text-center">{{ $content->inv_pro_temp_exp_date }}</td>
        <td class="text-right">{{ number_format($content->inv_pro_temp_unit_price, 2) }}</td>
        <td class="text-right">{{ number_format(($content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty), 2) }}</td>
        <td class="text-center">
            <a href="javascript:void(0);" onclick="remove_cart({{ $content->inv_pro_temp_pro_id }})" class="btn btn-sm btn-danger">
                <i class="fa fa-minus"></i>
            </a>
        </td>
    </tr>
    @elseif($content->inv_pro_temp_deal_type == 7)
        @php($delivery_charge = $content->inv_pro_temp_unit_price)
    @elseif($content->inv_pro_temp_deal_type == 8)
        @php($discount_amount = $content->inv_pro_temp_unit_price)
    @endif
@endforeach

<tr>
    <td colspan="4" class="text-right">Discount:</td>
    <td>
        <input type="number" onkeyup="" value="{{ $delivery_charge }}" placeholder="Discount" name="discount" class="form-control text-right temp_cart_discount" style="width: 140px; float:right;">
    </td>
    <td></td>
</tr>
<tr>
    <td colspan="4" class="text-right">Delivery Charges:</td>
    <td>
        <input type="number" onkeyup="" value="{{ $discount_amount }}" placeholder="Delivery Charges" name="delivery" class="form-control text-right temp_cart_delivery" style="width: 140px; float:right;">
    </td>
    <td></td>
</tr>