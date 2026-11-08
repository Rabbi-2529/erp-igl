@php($total = 0)
@php($sl = 0)
@foreach($cart_content as $content)

    <tr>
        <td>{{ ++$sl }}</td>
        <td>
            {{ ($content->inv_pro_temp_pro_id == 0) ? '' : $content->pro_warranty['inv_pro_det_pro_description'] }} {{ $content->inv_pro_temp_type_name }}
            <br>
            <b>{{ implode(', ', explode(',',$content->inv_pro_temp_slno)) }}</b>
        </td>
        <td align="center">
            @if ($content->inv_pro_temp_qty < 10)
              0{{ $content->inv_pro_temp_qty }}
            @else
            {{ $content->inv_pro_temp_qty }}
            @endif
        </td>
        <td class="text-right">{{ number_format($content->inv_pro_temp_unit_price, 2) }}</td>
        @php($amount = $content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty)
        <td class="text-right temp_cart">{{ number_format($amount,2) }}</td>
        <td class="text-center">
            <a href="javascript:void(0);" onclick="remove_cart({{ $content->inv_pro_temp_pro_id }})" class="btn btn-sm btn-danger">
                <i class="fa fa-minus"></i>
            </a>
        </td>
    </tr>
    @php($total += $amount)
@endforeach
<tr>
    <td colspan="4" class="text-right">Discount:</td>
    <td>
        <input type="number" onkeyup="total_sell_amount()" placeholder="Discount" name="discount" class="form-control text-right temp_cart_discount" style="width: 140px; float:right;">
    </td>
    <td></td>
</tr>
<tr>
    <td colspan="4" class="text-right">Delivery Charges:</td>
    <td>
        <input type="number" onkeyup="total_sell_amount()" placeholder="Delivery Charges" name="delivery" class="form-control text-right temp_cart_delivery" style="width: 140px; float:right;">
    </td>
    <td></td>
</tr>
@if (Auth::user()->au_user_type == 4)
<tr>
    <td colspan="4" class="text-right">Employee Comission:</td>
    <td>
        <input type="text" placeholder="Employee Comission" id="fee" name="comission" class="form-control text-right temp_cart_delivery" style="width: 140px; float:right;">
    </td>
    <td></td>
</tr>
@endif
<tr>
    <td colspan="4" class="text-right">Is Project?:</td>
    <td>
        <div class="some-class">
            <input type="radio" class="radio" name="project" value="1" id="y" />
            <label for="y">Yes</label>
            <input type="radio" class="radio" name="project" value="0" id="z" checked/>
            <label for="z">No</label>
        </div>
    </td>
    <td></td>
</tr>

<script type="text/javascript">
document.getElementById('fee').value = '0';
</script>
<style>
    .some-class {
      float: left;
      clear: none;
    }
    
    label {
      float: left;
      clear: none;
      display: block;
      padding: 0px 1em 0px 8px;
    }
    
    input[type=radio],
    input.radio {
      float: left;
      clear: none;
      margin: 2px 0 0 2px;
    }
</style>