              <div class="load-details">
                  <table class="table table-bordered table-striped">
                      <thead>
                          <tr>
                              <th>SL</th>
                              <th>Product Name</th>
                              <th>Description</th>
                              <th>Sold Qty</th>
                              <th>Unit Price</th>
                              <th>Amount</th>
                          </tr>
                      </thead>
                      @php($sl=0)
                      @php($balance=0)
                      @php($total = 0)
                      <tbody>
                          @foreach ($detail_ajax as $detail)
                          <tr>
                                <td>{{ ++$sl }}</td>
                                <td>
                                    @if ($detail->inv_pro_inv_tran_type == 1)
                                    {{ $detail->getProductWarranty['inv_pro_det_pro_id'] }}<br>
                                    @if ($detail->getProductWarranty['inv_pro_det_pro_warranty'] != 0)
                                        {{ $detail->getProductWarranty['inv_pro_det_pro_warranty'] }} Days
                                    @endif
                                    ({{ str_limit($detail->getProductWarranty['inv_pro_det_pro_description'],40) }})
                                    @endif
                                </td>
                                @if ($detail->inv_pro_inv_tran_type == 1)
                                <td class="text-right">{{ $detail->inv_pro_inv_tran_desc }}</td> 
                                @elseif($detail->inv_pro_inv_tran_type == 10)
                                <td class="text-right">{{ $detail->inv_pro_inv_tran_desc }}</td>  
                                @elseif($detail->inv_pro_inv_tran_type == 11)
                                <td class="text-right">{{ $detail->inv_pro_inv_tran_desc }}</td>
                                @endif

                                @if ($detail->inv_pro_inv_tran_type == 1)
                                <td class="text-right">{{ $detail->inv_pro_inv_qty }}</td>
                                <td class="text-right">{{ number_format($detail->inv_pro_inv_unit_price,2) }}</td>
                                @elseif($detail->inv_pro_inv_tran_type == 10)
                                <td class="text-right">{{ $detail->inv_pro_inv_qty }}</td>
                                <td class="text-right">{{ number_format($detail->inv_pro_inv_unit_price,2) }}</td>
                                @elseif($detail->inv_pro_inv_tran_type == 11)
                                <td class="text-right"></td>
                                <td class="text-right"></td>  
                                @endif                         

                                @if ($detail->inv_pro_inv_tran_type == 1)
                                <td class="text-right">{{ number_format($detail->inv_pro_inv_debit,2) }}</td> 
                                @elseif($detail->inv_pro_inv_tran_type == 10)
                                <td class="text-right">{{ number_format($detail->inv_pro_inv_debit,2) }}</td>  
                                @elseif($detail->inv_pro_inv_tran_type == 11)
                                <td class="text-right">{{ number_format($detail->inv_pro_inv_debit,2) }}</td>
                                @endif
                                
                            </tr>
                            @php($total = $total + $detail->inv_pro_inv_debit)
                          @endforeach
                      </tbody>
                      <tfoot>
                        @if ($discount_amount > 0)
                          <tr>
                              <td colspan="5" class="text-right">Discount:</td>
                              <td class="text-right">{{ $discount_amount }}</td>
                          </tr>
                          @endif
                          @php($balance = $total - $discount_amount)
                          <tr>
                              <td colspan="5" class="text-right">Total:</td>
                              <td class="text-right">{{ number_format($balance,2) }}</td>
                          </tr>
                      </tfoot>
                  </table>
              </div>