@extends('layouts.master')
@section('title', 'Add New Bill')

@section('oms_class','active')
@section('conveyance_class','active')
@section('convence_add_bill_class','active')

@section('content')

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

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add New Bill</h4>
                </div>
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

            <div class="panel-body">

                <div class="box box-info">
                    
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
                
            </div>
        </div>
    </div>
</div>





@endsection
@section( 'custom_script' )
    <script src="{{asset('asset/plugins/jquery.bootstrap-duallistbox.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
    <script>
        $('.duallistbox').bootstrapDualListbox()
    </script>
<script>
    $(document).ready(function(){
      const customerUrl = "{{ route('convence_get_customer') }}"; // Ensure this matches the route name

$(".itemSearch").select2({
    tags: false,
    multiple: false,
    tokenSeparators: [',', ' '],
    minimumInputLength: 3,
    minimumResultsForSearch: 10,
    placeholder: "Select a Customer",
    ajax: {
        url: customerUrl,
        dataType: "json",
        delay: 250,
        data: function (params) {
            return {
                term: params.term
            };
        },
        processResults: function (data) {
            return {
                results: $.map(data, function (item) {
                    return {
                        text: item.qb_company_name + ' (' + item.qb_mobile + ')',
                        id: item.qb_id
                    };
                })
            };
        },
        cache: true
    }
});
        // Calculate the date 7 days ago
        var sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7); // Set to 7 days ago
        var formattedSevenDaysAgo = sevenDaysAgo.toISOString().split('T')[0]; // Format as YYYY-MM-DD

        // Initialize the datepicker for Bill Date
        $("#from4").datepicker({
            format: "yyyy-mm-dd",  // Ensure this format matches the expected format
            daysOfWeekHighlighted: "7",  // Highlight Sundays
            todayHighlight: true,  // Highlight today's date
            startDate: formattedSevenDaysAgo, // Disable dates older than 7 days
            endDate: "today", // End date is today
            autoclose: true,
            beforeShowDay: function(date) {
                var currentDate = new Date(date);
                // Disable dates older than 7 days ago
                if (currentDate < sevenDaysAgo) {
                    return [false]; // Disable the date
                }
                return [true]; // Enable the date
            }
        });

        // Initialize other datepicker if needed
        $("#to").datepicker({
            daysOfWeekHighlighted: "7",  // Highlight Sundays
            todayHighlight: true  // Highlight today's date
        });

        // Form submission handler
        $('form').submit(function () {
            // Convert date format from DD/MM/YYYY to YYYY-MM-DD
            var dateInput = $('#from4').val();
            if (dateInput) {
                var parts = dateInput.split('/');
                if (parts.length === 3) {
                    var day = parts[0];
                    var month = parts[1];
                    var year = parts[2];
                    var formattedDate = year + '-' + month + '-' + day;
                    $('#from4').val(formattedDate);
                }
            }

            $(this).find("button[type='submit']").prop('disabled', true); // Disable submit button after form submission
        });
    });
</script>
@endsection
