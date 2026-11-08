@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_add_new_bill','active')
@section('custom_style')
    <link rel="stylesheet" href="{{asset('asset/plugins/bootstrap-duallistbox.min.css')}}">
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
        .select2-selection__choice{
            color: white;
            background-color: dodgerblue;
        }
    </style>

@endsection
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Convence
      </h1>
    </section>

          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Add Conveyance Bill</h3>
            </div>
            @if(session()->has('msg4'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg4') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif

            @if(session()->has('convence_error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('convence_error') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'OmsController@convence_add_new_bill_submit' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">

              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">For whom<b class="text-danger"> *</b></label>
                  <div class="col-sm-6">
                    <select class="form-control  itemSearch " id="customopt" name="oms_what_for" required>


                    </select>
                  </div>
                  @if( $errors->has('oms_what_for') )
                    <p class="text-warning">{{ $errors->first('oms_what_for') }}</p>
                  @endif
                </div>
                  <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Reason <b class="text-danger">*</b></label>
                      <div class="col-sm-6">
                          <select class="form-control" name="oms_reason"  required>
                              <option selected disabled value="">Select Reason</option>
                              @foreach($reasons as $reason)
                                  @if($reason->name !== 'Other')
                                  <option value="{{$reason->id}}">{{$reason->name}}</option>
                                  @endif
                              @endforeach
                              <option value="5">Other</option>
                          </select>
                      </div>
                      @if( $errors->has('oms_reason') )
                          <p class="text-danger">{{ $errors->first('oms_reason') }}</p>
                      @endif
                  </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Bill Date <b class="text-danger">*</b></label>
                  <div class="col-sm-6">
                    <input type="text" data-date-format="yyyy-mm-dd" name="oms_bill_date" autocomplete="off" class="form-control" id="from4" placeholder="Place Bill Date...." required>
                  </div>
                  @if( $errors->has('oms_bill_date') )
                    <p class="text-warning">{{ $errors->first('oms_bill_date') }}</p>
                  @endif
                </div>
                  <div class="form-group">
                      <label for="" class="col-sm-2 control-label"> Person

                      </label>
                      <div class="col-sm-6" id="deptUser">
                          <select name="engineer[]" class="duallistbox" multiple="multiple" >
                              @foreach ($users as $user)
                                  <option value="{{ $user->au_user_id }}">{{ $user->au_name }}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">From <b class="text-danger">*</b></label>
                  <div class="col-sm-6">
                    <input type="text" name="oms_dfrom" required class="form-control" id="inputEmail3" placeholder="Place Boarding point...">
                  </div>
                  @if( $errors->has('oms_dfrom') )
                    <p class="text-warning">{{ $errors->first('oms_dfrom') }}</p>
                  @endif
                </div>

                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">To/Destination  <b class="text-danger">*</b></label>
                  <div class="col-sm-6">
                    <input type="text" name="oms_dto" required class="form-control" id="inputEmail3" placeholder="Place Destinition point...">
                  </div>
                  @if( $errors->has('oms_dto') )
                    <p class="text-warning">{{ $errors->first('oms_dto') }}</p>
                  @endif
                </div>
                  <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Mode  <b class="text-danger">*</b></label>
                      <div class="col-sm-6">
                          <select class="form-control select2" name="oms_mode" required>
                              <option value="">Select One</option>
                              @foreach($modes as $mode)
                                  <option value="{{$mode->id}}">{{$mode->name}}</option>
                              @endforeach
                          </select>
                      </div>
                      @if( $errors->has('oms_mode') )
                          <p class="text-warning">{{ $errors->first('oms_mode') }}</p>
                      @endif
                  </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Amount  <b class="text-danger">*</b></label>
                  <div class="col-sm-6">
                    <input type="number" name="oms_debit" required class="form-control" id="inputEmail3" placeholder="Place Your Amount..">
                  </div>
                  @if( $errors->has('oms_debit') )
                    <p class="text-warning">{{ $errors->first('oms_debit') }}</p>
                  @endif
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Create</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
         </section>
@endsection
@section( 'custom_script' )
    <script src="{{asset('asset/plugins/jquery.bootstrap-duallistbox.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
    <script>
        $('.duallistbox').bootstrapDualListbox()
    </script>

    <script>
      $(document).ready(function(){
          $(".itemSearch").select2({
              tags: false,
              multiple: false,
              tokenSeparators: [',', ' '],
              minimumInputLength: 3,
              minimumResultsForSearch: 10,
              placeholder: "Select a Customer",
              ajax: {
                  url: '/convence_get_customer',
                  dataType: "json",
                  type: "GET",
                  data: function (params) {

                      var queryParameters = {
                          term: params.term
                      }
                      return queryParameters;
                  },
                  processResults: function (data) {
                      return {
                          results: $.map(data, function (item) {
                              // $('.itemSearch').html('<option value=' + item.qb_id + '>' + item.qb_company_name  + '</option>')


                              // alert(item.qb_company_name)
                              return {

                                  text: item.qb_company_name+' ('+item.qb_mobile+')',
                                  id: item.qb_id,
                              }
                          })
                      };
                  }
              }
          });

      $( "#from4" ).datepicker({
            daysOfWeekHighlighted: "7",
              todayHighlight: true,
              startDate: "-6d",
              endDate: "today",
              autoclose: true,
          });
      $( "#to" ).datepicker({
            daysOfWeekHighlighted: "7",
              todayHighlight: true,
          });
      });
      $(document).ready(function() {
          $('form').submit(function () {
              $(this).find("button[type='submit']").prop('disabled', true);
          });
      })
    </script>
@endsection
