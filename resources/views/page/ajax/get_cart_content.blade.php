@php($total = 0)
@php($sl = 0)
@foreach($cart_content as $content)



<tr>
    <td class="invoice_fields">{{ ++$sl }}</td>

    <td class="product_field">
        {{ ($content->inv_pro_temp_pro_id == 0) ? '' : $content->pro_warranty['inv_pro_det_pro_description'] }} {{ $content->inv_pro_temp_type_name }}
        <br>
        <b>{{ implode(', ', explode(',',$content->inv_pro_temp_slno)) }}</b>
    </td>

    <td class="product_field">
        @if ($content->inv_pro_temp_qty < 10)
          0{{ $content->inv_pro_temp_qty }}
        @else
        {{ $content->inv_pro_temp_qty }}
        @endif
    </td>

    <td class="product_field" id="unit_price">
        ৳ {{ number_format($content->inv_pro_temp_unit_price, 2) }}
    </td>

    <td class="invoice_fields" id="discount_field">
        <input type="number" class="form-control discount-input" placeholder="৳ 0.00">
    </td>
    
    <td class="invoice_fields" id="discount_totall">
        <input type="text" class="form-control discount-total" placeholder="৳ 0.00" disabled>
    </td>

    <td class="invoice_fields">
        <input type="number" autocomplete="off" class="form-control" placeholder="0%">
    </td>

    <td class="invoice_fields">
        <input type="text" autocomplete="off" class="form-control" placeholder="৳ 0.00" disabled>
    </td>

    @php($amount = $content->inv_pro_temp_unit_price * $content->inv_pro_temp_qty)

    <td class="invoice_fields temp_cart">৳ {{ number_format($amount,2) }}</td>

    <td>
        <button class='btn btn-danger text-right' type='button' value='Delete'
            onclick='remove_cart({{ $content->inv_pro_temp_pro_id }})'><i class='fa fa-close'></i></button>
    </td>
</tr>

@php($total += $amount)

@endforeach

{{-- ==============FOOTER=============== --}}
<tr>
    <td class="text-right" colspan="8"><b>Sale Discount:</b></td>
    <td class="text-right">
        <input type="text" onkeyup="total_sell_amount()" id="discount"
            class="form-control text-right temp_cart_discount" name="discount"
            placeholder="0.00" tabindex="8" />
        <input type="hidden" id="txfieldnum">
    </td>
    
</tr>
{{-- 
<tr>
    <td class="text-right" colspan="8"><b>Total Discount:</b></td>
    <td class="text-right">
        <input type="text" id="total_discount_ammount" class="form-control text-right"
            name="total_discount" value="0.00" readonly="readonly" />
    </td>
</tr> --}}

{{-- <tr>
    <td class="text-right" colspan="8"><b>Total VAT:</b></td>
    <td class="text-right">
        <input type="text" id="total_vat_amnt" class="form-control text-right"
            name="total_vat_amnt" value="0.00" readonly="readonly" />
    </td>
</tr> --}}

@if (Auth::user()->au_user_type == 4)
<tr>
    <td colspan="8" class="text-right"><b>Employee Comission:</b></td>
    <td class="text-right">
        <input type="text" id="fee" name="comission" placeholder="0.00" class="form-control text-right temp_cart_delivery">
    </td>
</tr>
@endif

<tr>
    <td class="text-right" colspan="8"><b>Shipping Cost:</b></td>
    <td class="text-right">
        <input type="text" id="delivery" class="form-control text-right temp_cart_delivery"
            name="delivery" onkeyup="total_sell_amount()" placeholder="0.00"
            tabindex="1" />
    </td>
</tr>

{{-- <tr>
    <td colspan="8" class="text-right"><b>Grand Total:</b></td>
    <td class="text-right">
        <input type="text" id="grandTotal" class="form-control text-right grandTotalamnt"
            name="grand_total_price" value="0.00" readonly="readonly" />
    </td>
</tr> --}}

<tr>
    <td colspan="8" class="text-right"><b>Net Total:</b></td>
    <td class="text-right">
        <input type="text" id="total" class="form-control text-right" name="total"
            value="৳ {{ number_format($total,2) }}" readonly="readonly" style="font-weight: bold;"/>
    </td>
</tr>

<tr>
    <td colspan="8" class="text-right"><b>Is Project?</b></td>
    <td>
        <div class="project-class">
            <input type="radio" class="radio" name="project" value="1" id="y" />
            <label for="y">Yes</label>
            
            <input type="radio" class="radio" name="project" value="0" id="z" checked/>
            <label for="z">No</label>
        </div>
    </td>
</tr>