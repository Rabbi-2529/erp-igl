<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Invoice: {{ $invoice }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="modal-body">
    <table class="table table-bordered table-striped table-hover datatable">
        <thead>
            <tr>
                <th>SL</th>
                <th>Product Name</th>
                <th>Warranty</th>
                <th>Short Qty</th>
                <th>Total Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        @php($sl=0)
        @php($total=0)
        <tbody>
            @foreach ($detail_buy as $buy)
            <tr>
                <td>{{ ++$sl }}</td>
                <td>
                    {{ $buy->getProductWarranty['inv_pro_det_pro_id'] }}
                    ({{ str_limit($buy->getProductWarranty['inv_pro_det_pro_description'],40) }})
                </td>
                <td>
                    @if ($buy->getProductWarranty['inv_pro_det_pro_warranty'] == 0)
                        No Warranty
                    @else
                        {{ $buy->getProductWarranty['inv_pro_det_pro_warranty'] }} Days
                    @endif
                </td>
                <td class="text-right">{{ $buy->inv_pro_inv_short_qty }}</td>
                <td class="text-right">{{ $buy->inv_pro_inv_total_qty }}</td>
                <td class="text-right">{{ number_format($buy->inv_pro_inv_unit_price,2) }}</td>
                <td class="text-right">{{ number_format($buy->inv_pro_inv_debit,2) }}</td>
            </tr>
            @php($total = $total + $buy->inv_pro_inv_debit)
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($total,2) }}</td>
            </tr>
        </tfoot>
    </table>
  </div>

    
