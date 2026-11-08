<table id="example1" class="table table-bordered table-striped">
    <thead>
      <th>Product Name</th>
      <th>Serial No</th>
      <th>Party(Sell/Buy)</th>
      <th>Date(Buy)</th>
      <th>Warranty</th>
      <th>Invoice</th>
    </thead>
    @foreach ($products as $item)
    <tbody>
      <td>{{ $item->getProductName->inv_pro_det_pro_id }}</td>
      <td>{{ $item->inv_pro_invdet_slno }}</td>
      <td>{{ empty($item->getCustomerName->inv_cus_name) ? '' : $item->getCustomerName->inv_cus_name}}/{{ $item->getSupplierName->inv_sup_com_name }}</td>
      <td>{{ App\Inv_product_inventory::buyDate($item->inv_pro_invdet_proinv_id) }}</td>
      <td>{{ $item->getProductName->inv_pro_det_pro_warranty }}</td>
      <td>
          {{--<button class="btn btn-xs btn-info" data-toggle="modal" data-target="#sellInvoice" onclick="sellInvoiceShow('{{ $item->getInvoiceNumber->inv_pro_inv_invoice_no }}')">
          Sell
        </button>--}}
        <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#buyInvoice" onclick="buyInvoiceShow('{{ $item->getInvoiceNumber->inv_pro_inv_invoice_no }}')">
          Buy
        </button>
      </td>
    </tbody>
    @endforeach
    
  </table>
  
  <div class="modal fade" id="sellInvoice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="buyInvoice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content" id="buyData">
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function sellInvoiceShow(invoice) {
      $.ajax({
        type: "GET",
        url: "{{ route('inventory.sell-invoice-show-ajax') }}",
        data: {invoice:invoice},
        success:function(result){
          console.log(result)
        }
      });
    }
    function buyInvoiceShow(invoice) {
      $.ajax({
        type: "GET",
        url: "{{ route('inventory.buy-invoice-show-ajax') }}",
        data: {invoice:invoice},
        success:function(result){
          $('#buyData').html(result);
          $('#buyInvoice').modal('show');
        }
      });
    }
  </script>

  