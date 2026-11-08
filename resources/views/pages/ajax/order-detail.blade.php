<form action="{{ route('update-order-submit') }}" method="post">
    @csrf
    {{-- <input type="hidden" name="orderId" value="{{ $order->id }}"> --}}
    <input type="hidden" name="client" value="{{ $order->order_client }}">
    <div class="form-group">
      <label for="exampleInputEmail1">Order Price</label>
      <input type="text" class="form-control" name="credit"  value="{{ $order->order_price_credit }}" readonly>
    </div>
    
    <div class="form-group">
      <label for="exampleInputPassword1">Amount Add/Subtract</label>
      <select name="addRemove" class="form-control" onchange="amountAddRemove(this.value)" id="">
          <option value="">Select</option>
          <option value="1">Add Amount</option>
          <option value="2">Decrease Amount</option>
      </select>
    </div>
    <div class="form-group" id="add" style="display: none;">
        <label for="exampleInputEmail1">Order Price</label>
        <input type="text" class="form-control" name="add"  placeholder="Enter Additional Amount">
    </div>
    <div class="form-group" id="remove" style="display: none;">
        <label for="exampleInputEmail1">Order Price</label>
        <input type="text" class="form-control" name="remove"  placeholder="Enter Decreased Amount">
    </div>
 <div class="form-group">
      <label for="exampleInputEmail1">For What?</label>
      <textarea name="desc" class="form-control" id="" cols="30" rows="10" required></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
  </form>

  <script>
      function amountAddRemove(value){
          if (value == 1) {
             $('#add').show();
             $('#remove').hide();
          }else if(value == 2){
            $('#add').hide();
            $('#remove').show();
          }else{
            $('#add').hide();
            $('#remove').hide();
          }
      }
  </script>

