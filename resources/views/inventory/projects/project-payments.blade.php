@extends('layouts.master')
@section('title', 'Project Payment')

@section('projects_class', 'active')
@section('pay_projects', 'active')
@section('content')
<section class="content">
        <section class="content-header">
          @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('err') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
              <h1>
                Project Payments
              </h1>
            </section>
                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Add Project Payments</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    {{Form::open(['route' => 'projects.payments-project-store', 'method' => 'post' , 'class' => 'form-horizontal'])}}
                      <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"> Projects</label>
                            <div class="col-sm-6">
                                <select name="project" class="form-control select2" id="" onchange="project_name(this.value)" required>
                                    <option value="">Select</option>
                                    @foreach ($projects as $pro)
                                        <option value="{{ $pro->id }}" {{ ($pro->id == old('project') ? 'selected' : '') }}>{{ $pro->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p id="amount"></p>
                            @if($errors->has('project'))
                                <p class="text-danger">{{ $messages }}</p>
                            @endif
                        </div>



                        <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Transaction Purpose
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-6">
                            <select name="purpose" id="" class="form-control" onchange="show_target(this.value)" required>
                                <option value="">Select Transaction Purpose</option>
                                <option value="1">Conveyance</option>
                                <option value="2">Cash Collection</option>
                                <option value="3">Bank Collection</option>
                                <option value="4">Mobile Banking Collection</option>
                                <option value="5">Others Collection</option>
                            </select>
                          </div>
                          @if($errors->has('purpose'))
                                <p class="text-danger">{{ $messages }}</p>
                            @endif
                        </div>

                       <div class="form-group" id="userSection" style="display: none">
                          <label for="inputEmail3" class="col-sm-2 control-label"> Employee
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-6">
                              <select name="employee_id" id="" class="form-control" >
                                  <option value="">Select Employee</option>
                                  @foreach($users as $user)
                                  <option value="{{$user->au_id}}">{{$user->au_name}}</option>
                                  @endforeach


                              </select>
                          </div>
                        </div>

                        <div class="form-group" id="bank" style="display: none;">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Bank Collection
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-2">
                              <select name="bank" id="" class="form-control" >
                                  <option value="">Select Bank</option>
                                  @foreach ($banks as $bank)
                                      <option value="{{ $bank->bank_name }}">{{ $bank->bank_name }}</option>
                                  @endforeach
                              </select>
                            </div>
                            <div class="col-sm-2">
                                <input type="text" name="acc" autocomplete="off" value="{{ old('acc') }}" class="form-control" id="" placeholder="Acc No">
                              </div>
                              <div class="col-sm-2">
                                <input type="text" name="bank_date" data-date-format="yyyy-mm-dd" id="to" autocomplete="off" value="{{ old('bank_date') }}" class="form-control" id="" placeholder="Date">
                              </div>
                              {{-- @if($errors->has('bank'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif
                              @if($errors->has('acc'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif
                              @if($errors->has('bank_date'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif --}}
                        </div>

                        <div class="form-group" id="mobileBank" style="display: none;">
                            <label for="inputEmail3" class="col-sm-2 control-label">Mobile Banking Collection
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-3">
                                <select name="mbank" id="" class="form-control">
                                    <option value="">Select Mobile Banking</option>
                                    <option value="Bkash">Bkash</option>
                                    <option value="Rocket">Rocket</option>
                                    <option value="Nagad">Nagar</option>
                                    <option value="UCash">UCash</option>
                                </select>
                              </div>
                            <div class="col-sm-3">
                                <input type="text" name="trx" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="" placeholder="Transaction ID">
                            </div>
                            {{-- @if($errors->has('trx'))
                                <p class="text-danger">{{ $messages }}</p>
                            @endif --}}
                        </div>

                        <div class="form-group" id="othersCollection" style="display: none;">

                          <label for="inputEmail3" class="col-sm-2 control-label">Others Pupose
                              <b class="text-danger">*</b>
                          </label>
                          <div class="col-sm-2" style="width: 140px;">
                            <input type="text" name="cable_qty" autocomplete="off" value="" v-model="cableQty" class="form-control text-right" placeholder="Cable/Cam">
                            <span class="text-info">Cable(Mtr)/Cam</span>
                          </div>
                          <div class="col-sm-2" style="width: 120px;">
                              <input type="text" name="mtr_price" autocomplete="off" value="" v-model="cablePrice" class="form-control text-right" placeholder="Price">
                              <span class="text-info">Price</span>
                          </div>
                          <div class="col-sm-2" style="width: 120px;">
                            <input type="text" name="total_price" v-model="total" autocomplete="off" value="" class="form-control text-right" readonly >
                            <span class="text-info">Total Price</span>
                          </div>
                          <div class="col-sm-2" style="width: 120px;">
                            <input type="text" name="collection_desc" autocomplete="off" value="" class="form-control" id="" >
                            <span class="text-info">Description</span>
                          </div>
                          {{-- @if($errors->has('trx'))
                              <p class="text-danger">{{ $messages }}</p>
                          @endif --}}
                      </div>

                          <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label"> Pay Amount
                                <b class="text-danger">*</b>
                            </label>
                            <div class="col-sm-6">
                              <input type="text" name="amount" autocomplete="off" value="{{ old('amount') }}" class="form-control" id="proAmount" placeholder="Enter Project Amount" required>
                            </div>
                            <p class="text-danger" id="amount_exceed"></p>
                              @if($errors->has('amount'))
                                  <p class="text-danger">{{ $messages }}</p>
                              @endif
                          </div>


                        <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Payments Date
                                  <b class="text-danger">*</b>
                                </label>
                                <div class="col-sm-6">
                                  <input type="text" name="project_da" data-date-format="yyyy-mm-dd" id="from4" maxlength="50" autocomplete="off" value="{{ old('pro_desc') }}" class="form-control" placeholder="Enter Project Date">
                                </div>
                                @if($errors->has('project_da'))
                                  <p class="text-danger">{{ $messages }}</p>
                                @endif
                        </div>

                      </div>
                      <!-- /.box-body -->
                      <div class="box-footer">
                        <div class="col-sm-3">
                            <button type="submit" id="payment_button" class="btn btn-info pull-right">Add</button>
                         </div>
                      </div>
                      <!-- /.box-footer -->
                    {{ Form::close() }}
                  </div>
                 </section>



@endsection
@section('custom_style')
  <style type="text/css">

#rating > span {
  display: inline-block;
  position: relative;
  width: 1.1em;

}
#rating > span:hover:before,
#rating > span:hover ~ span:before {
   content: "\2605";
   position: absolute;
   color: orange;
}
.form-control::-webkit-inner-spin-button,
.form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
</style>

@endsection
@section('custom_script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script>
  $(document).ready(function(){

$( "#from4" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
        daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
});
    $(document).ready(function(){
    $('#product_category').on("change",function(){
      let grp_id = $("#product_category").val();
      let link = "{{ route('inventory.product_submit_ajax') }}";
      $.ajax({
        type: "GET",
        url: link,
        data: { grp_id: grp_id},
        success: function (result) {
          $(".product_category_wrapper").html(result);
        }
      });
    });


  });


function project_name(project_id)
    {


      var _token=$("#_token").val();
      let url = "{{ route('projects.rest-amount') }}";
      $.ajax({
          type: "GET",
          url: url,
          data: { project_id: project_id,_token:_token},
          success: function (result) {
            console.log(result);
            $("#amount").html(result);
            $('#amount,#proAmount').on('keyup', function() {

                let proAmount = parseFloat($('#proAmount').val());
                console.log(proAmount);
                var btn = $('button.payment_button');
                if(result < proAmount){
                  $('#amount_exceed').html('Amount is Exceeded from due amount');
                  $('#payment_button').prop('disabled', true);
                }
                else{
                  $('#amount_exceed').html('');
                  $('#payment_button').prop('disabled', false);
                }
            });
          }
      });

    }




</script>
<script type="text/javascript">
    function show_target(value) {
        if (value == '3') {
            $("#userSection").css('display','none');
            $('#mobileBank').hide();
            $('#othersCollection').hide();
            $('#bank').show();
        }
        else if (value == '4') {
            $("#userSection").css('display','none');
            $('#mobileBank').show();
            $('#bank').hide();
             $('#othersCollection').hide();

        }else if (value == '1') {
            //  show user div
            $("#userSection").css('display','');
            $('#mobileBank').hide();
            $('#bank').hide();
            $('#othersCollection').hide();

        }else if (value == '2') {
            $("#userSection").css('display','none');
            $('#mobileBank').hide();
            $('#bank').hide();
            $('#othersCollection').hide();

        }else if (value == '5') {
            $("#userSection").css('display','none');
            $('#mobileBank').hide();
            $('#bank').hide();
            $('#othersCollection').show();

        }
    }

    $("#checkAll").click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });

</script>
@endsection
