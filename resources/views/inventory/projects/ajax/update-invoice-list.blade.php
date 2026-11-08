<select class="" name="invoice" required>
    <option value="">Select</option>
    @foreach($invoices as $invoice)
      <option value="{{ $invoice->inv_pro_inv_invoice_no }}" {{ ($invoice->inv_pro_inv_invoice_no == $invoiceupdate->inv_pro_inv_invoice_no)?'selected' : '' }}>{{ $invoice->inv_pro_inv_invoice_no }}</option>
    @endforeach
</select>
  