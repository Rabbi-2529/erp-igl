@php($sl=0)
@foreach ($buy_pro as $buy)
    <tr>
        <td class="text-center">{{ ++$sl }}</td>
        <td>{{ $buy->inv_pro_inv_invoice_no }}</td>
        <td>{{ $buy->getProductWarranty['inv_pro_det_pro_id'] }}</td>
        <td>{{ $buy->inv_pro_inv_tran_desc }}</td>
        <td class="text-center">{{ App\Inv_product_inventory::product_buy_return_available_qty($buy->inv_pro_inv_id) }}</td>
        <td class="text-right">{{ $buy->inv_pro_inv_unit_price }}</td>
        <td class="text-right">{{ $buy->inv_pro_inv_debit }}</td>
        <td class="text-center">
            @if($buy->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $buy->inv_pro_inv_id }}" placeholder="Qty">
            <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $buy->inv_pro_inv_id }}')">
                <i class="fa fa-plus"></i>
            </button>
            @else
            <input type="text" autocomplete="off" class="form-control" style="width: 50px;" placeholder="N/A" disabled>
            <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $buy->inv_pro_inv_id }}')">
                    <i class="fa fa-plus"></i>
            </button>
            @endif
        </td>
    </tr>
    <style>
            input {
                height: 27px !important;
            }
            .btn-sm {
                padding: 3px 8px;
            }
        </style>
@endforeach
