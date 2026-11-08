<div class="load-details">
        <table class="table table-bordered table-striped">
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
            @php($balance=0)
            <tbody>
                @foreach ($detail_buy as $buy)
                <tr>
                    <td>{{ ++$sl }}</td>
                    <td>
                        {{ $buy->getProductWarranty['inv_pro_det_pro_id'] }}
                        ({{ \Illuminate\Support\Str::limit($buy->getProductWarranty->product->inv_pro_name ?? $buy->getProductWarranty['inv_pro_det_pro_description'], 40) }})
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
                @php($balance = $balance + $buy->inv_pro_inv_debit)
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">Total:</td>
                    <td class="text-right">{{ number_format($balance,2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>