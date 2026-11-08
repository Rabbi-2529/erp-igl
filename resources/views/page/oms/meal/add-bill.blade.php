@extends('layouts.master')
@section('title', 'Monthly Sell Report')

@section('oms_class','active')
@section('meal_class','active')
@section('lunch_add_new_bill','active')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                  <h4>Add New Bill (Meal)</h4>
                </div>

                @if(session()->has('msg3'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('msg3') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                @endif
                @if(session()->has('lunch_error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('lunch_error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                @endif

            </div>
            <div class="panel-body">
                {{Form::open(['action' => 'OmsController@lunch_add_new_bill_submit' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                <div class="box-body">
                    <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">For whom <b class="text-danger">*</b></label>
                    <div class="col-sm-6">
                        <select class="form-control itemSearch" name="reason" id="customopt" required>

                        </select>
                    </div>
                    </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Reason <b class="text-danger">*</b></label>
                    <div class="col-sm-6">
                        <select class="form-control" name="oms_reason" id="customopt" required>
                            <option selected disabled value="">Select Reason</option>
                            @foreach($reasons as $reason)
                            <option value="{{$reason->id}}">{{$reason->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @if( $errors->has('oms_reason') )
                        <p class="text-danger">{{ $errors->first('oms_reason') }}</p>
                    @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">Menu <b class="text-danger">*</b></label>
                        <div class="col-sm-6">
                            <label class="radio-inline">
                                <input type="radio" value="1" name="menu" checked>Breakfast
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="2"  name="menu">Lunch
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="3"  name="menu">Dinner
                            </label>
                    </div>
                    </div>
                    <div class="form-group">
                          <label for="inputEmail3" class="col-sm-2 control-label">Bill Date <b class="text-danger">*</b></label>
                          <div class="col-sm-6">
                          <input type="text" name="oms_bill_date" id="from4" class="form-control" placeholder="Place Bill Date...." required>
                          </div>
                          @if( $errors->has('oms_bill_date') )
                            <p class="text-warning">{{ $errors->first('oms_bill_date') }}</p>
                          @endif
                        </div>
                    <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label"> Person</label>
                    <div class="col-sm-6">
                        <select id="custPerson" name="engineer[]" class="duallistbox" multiple="multiple"  >
                            @foreach ($users as $user)
                                <option value="{{ $user->au_user_id }}">{{ $user->au_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if( $errors->has('oms_person') )
                        <p class="text-danger">{{ $errors->first('oms_person') }}</p>
                    @endif
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label"> Guest </label>
                        <div class="col-sm-6">
                            <input type="number" name="guest" class="form-control" placeholder="Number of guests" id="guest" >
                        </div>
                        <small id="totalDiv" style="display: none">Total: <span id="total">6</span></small>

                    </div>
                    <div class="form-group">
                    <label for="inputPassword3" class="col-sm-2 control-label">Amount <b class="text-danger">*</b></label>
                    <div class="col-sm-6">
                        <input type="number" name="oms_debit" class="form-control" id="inputEmail3" placeholder="Place Your Amount.." required>
                    </div>
                    @if( $errors->has('oms_debit') )
                        <p class="text-danger">{{ $errors->first('oms_debit') }}</p>
                    @endif
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-info pull-right">Add</button>
                    </div>
                </div>
                {{Form::close()}}
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

        

        $("#to").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true
        });

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

        $(this).find("button[type='submit']").prop('disabled', true);
    });
    });
</script>
@endsection
