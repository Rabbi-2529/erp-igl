    @php($sl=0)
    @foreach ($sale_pro as $sell)
        <tr>
            <td class="text-center">{{ ++$sl }}</td>
            <td>{{ $sell->inv_pro_inv_invoice_no }}</td>
            <td>{{ $sell->getProductWarranty['inv_pro_det_pro_id'] }}</td>
            <td>{{ $sell->inv_pro_inv_tran_desc }}</td>
            <td class="text-center">{{ $sell->inv_pro_inv_qty }}</td>
            <td class="text-right">{{ $sell->inv_pro_inv_unit_price }}</td>
            <td class="text-right">{{ $sell->inv_pro_inv_debit }}</td>
            <td class="text-center">
                @if($sell->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
                    <input type="text" autocomplete="off" class="form-control" style="width: 50px;" id="pro_qty_{{ $sell->inv_pro_inv_id }}" placeholder="Qty">
                    <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->inv_pro_inv_id }}')">
                        <i class="fa fa-plus"></i>
                    </button>
                @else
                    <input type="text" autocomplete="off" class="form-control" style="width: 50px;" placeholder="N/A" disabled>
                    <button type="button" class="btn btn-success btn-sm" onclick="addtocart('{{ $sell->inv_pro_inv_id }}')">
                            <i class="fa fa-plus"></i>
                    </button>
                @endif
            </td>
        </tr>
    @endforeach
  