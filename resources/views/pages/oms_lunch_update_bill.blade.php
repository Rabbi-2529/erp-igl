@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_reject_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Lunch
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Update Rejected Balance</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => ['OmsController@lunch_reject_update', $lunch_up_bills->sl_id], 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">For whom</label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="lunch_customer">
                      <option value="">Select</option>
                      @foreach($lunch_c as $q)
                      <option value="{{ $q->qb_id }}" {{ ($q->qb_id==$lunch_up_bills->oms_what_for)?'selected':'' }}>
                      {{ $q->qb_company_name }}( {{ $q->qb_mobile }} )
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Reason</label>
                  <div class="col-sm-6">
{{--                    <input type="text" name="lunch_reason" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $lunch_up_bills->oms_reason }}" required>--}}
                      <select class="form-control" name="lunch_reason" id="customopt" required>
                          <option selected disabled value="">Select Reason</option>
                          <option @if ($lunch_up_bills->oms_reason === '1') selected  @endif value="1">CCTV Setup</option>
                          <option @if ($lunch_up_bills->oms_reason === '2') selected  @endif value="2">CCTV Service</option>
                          <option @if ($lunch_up_bills->oms_reason === '3') selected  @endif value="3">IT Support</option>
                          <option @if ($lunch_up_bills->oms_reason === '4') selected  @endif value="4">Visit</option>
                          <option @if ($lunch_up_bills->oms_reason === '5') selected  @endif value="5">Other</option>

                      </select>
                  </div>
                </div>
                  <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Menu <b class="text-danger">*</b></label>
                      <div class="col-sm-6">
                          <label class="radio-inline">
                              <input type="radio" value="1" name="menu" @if ($lunch_up_bills->menu === 1) checked  @endif >Breakfast
                          </label>
                          <label class="radio-inline">
                              <input type="radio" value="2"  name="menu" @if ($lunch_up_bills->menu === 2) checked  @endif >Lunch
                          </label>
                          <label class="radio-inline">
                              <input type="radio" value="3"  name="menu" @if ($lunch_up_bills->menu === 3) checked  @endif >Dinner
                          </label>
                      </div>
                  </div>
{{--                <div class="form-group">--}}
{{--                  <label for="inputEmail3" class="col-sm-2 control-label">Person</label>--}}
{{--                  <div class="col-sm-6">--}}
{{--                    <input type="text" name="lunch_person" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $lunch_up_bills->oms_person }}" required>--}}
{{--                  </div>--}}
{{--                </div>--}}
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Amount</label>
                  <div class="col-sm-6">
                    <input type="text" name="lunch_cost" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $lunch_up_bills->oms_debit }}" required>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Update</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{ Form::close() }}
          </div>
         </section>
@endsection
@section('custom_script')
<script>
$('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
</script>
@endsection
