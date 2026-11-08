{{-- <input type="hidden" value="{{ $customer->id }}"> --}}
    <div class="form-group">
      <label for="exampleInputEmail1">Name</label>
      <input type="name" value="{{ $customer->name }}" class="form-control" id="" aria-describedby="emailHelp" placeholder="Enter email">
    </div>
    <div class="form-group">
      <label for="exampleInputPassword1">Company Name</label>
      <input type="text" value="{{ $customer->com_name }}" class="form-control" id="" placeholder="Company Name">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Mobile</label>
        <input type="text" value="{{ $customer->mobile }}" class="form-control" id="" placeholder="Company Name">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Email</label>
        <input type="text" value="{{ $customer->email }}" class="form-control" id="" placeholder="Company Name">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Address</label>
        <input type="text" value="{{ $customer->address }}" class="form-control" id="" placeholder="Company Name">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Type</label>
        <label class="radio-inline">
            <input type="radio" {{ ($customer->type==1)?'checked':'' }} name="type" value="1">Agency
        </label>
        <label class="radio-inline">
            <input type="radio" {{ ($customer->type==2)?'checked':'' }} name="type" value="2">Bank
        </label>
        <label class="radio-inline">
            <input type="radio" {{ ($customer->type==3)?'checked':'' }} name="type" value="3">Regular
        </label>
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Balance Type</label>
        <label class="radio-inline">
            <input type="radio" {{ ($customer->bal_type ==1)?'checked':'' }} name="bal_type" value="1">Debit
          </label>
          <label class="radio-inline">
              <input type="radio" {{ ($customer->bal_type == 2)?'checked':'' }} name="bal_type" value="2">Credit
          </label>
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Identity</label>
        <input type="text" value="{{ $customer->identity }}" class="form-control" id="" placeholder="Company Name">
    </div>