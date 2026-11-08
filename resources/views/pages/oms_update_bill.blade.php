@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_reject_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Convence
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Update Rejected Balance</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => ['OmsController@convence_reject_bill_update', $bill->sl_id], 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">What For</label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="reason">
                      <option value="">Select</option>
                      @foreach($qb as $q)
                      <option value="{{ $q->qb_id }}" {{ ($q->qb_id==$bill->oms_what_for)?'selected':'' }}>
                      {{ $q->qb_company_name }}( {{ $q->qb_mobile }} )
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">From</label>
                  <div class="col-sm-6">
                    <input type="text" name="from" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $bill->oms_dfrom }}" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mode</label>
                  <div class="col-sm-6">
                    <select class="form-control select2" name="via" required>
                      <option value="">Select One</option>
                      <option value="Boat" {{ ('Boat'==$bill->oms_mode)?'selected':'' }}>Boat</option>
                      <option value="Bus" {{ ('Bus'==$bill->oms_mode)?'selected':'' }}>Bus</option>
                      <option value="CNG" {{ ('CNG'==$bill->oms_mode)?'selected':'' }}>CNG</option>
                      <option value="Leguna" {{ ('Leguna'==$bill->oms_mode)?'selected':'' }}>Leguna</option>
                      <option value="Lonch" {{ ('Lonch'==$bill->oms_mode)?'selected':'' }}>Lonch</option>
                      <option value="Rent-A-Car" {{ ('Rent-A-Car'==$bill->oms_mode)?'selected':'' }}>Rent-A-Car</option>
                      <option value="Rickshaw" {{ ('Rickshaw'==$bill->oms_mode)?'selected':'' }}>Rickshaw</option>
                      <option value="Taxi-Cab" {{ ('Taxi-Cab'==$bill->oms_mode)?'selected':'' }}>Taxi-Cab</option>
                      <option value="Train" {{ ('Train'==$bill->oms_mode)?'selected':'' }}>Train</option>
                      <option value="Bike" {{ ('Bike'==$bill->oms_mode)?'selected':'' }}>Bike</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">To</label>
                  <div class="col-sm-6">
                    <input type="text" name="to" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $bill->oms_dto }}" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Amount</label>
                  <div class="col-sm-6">
                    <input type="text" name="oms_debit" class="form-control" id="inputEmail3" placeholder="Update Your Rejected Amount" value="{{ $bill->oms_debit }}" required>
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
