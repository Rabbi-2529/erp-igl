@extends('layouts.master')
@section('title', 'Update Rejected Balance')
@section('oms_class','active')
@section('conveyance_class','active')
@section('conveyance_reject_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Convence
            </h1>
        </section>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4>Update Rejected Balance</h4>
                        </div>
                    </div>

                    <div class="panel-body">
                        <!-- /.box-header -->
                        <!-- form start -->
                        {{ Form::open(['action' => ['OmsController@convence_reject_bill_update', $bill->sl_id], 'method' => 'post', 'class' => 'form-horizontal']) }}
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">What For</label>
                                <div class="col-sm-6">
                                    <select class="form-control select2" name="reason" id="qb-select">
                                        <option value="">Select</option>
                                        @foreach ($qb as $q)
                                            <option value="{{ $q->qb_id }}"
                                                {{ $q->qb_id == $bill->oms_what_for ? 'selected' : '' }}>
                                                {{ $q->qb_company_name }} ({{ $q->qb_mobile }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">From</label>
                                <div class="col-sm-6">
                                    <input type="text" name="from" class="form-control" id="inputEmail3"
                                        placeholder="Update Your Rejected Amount" value="{{ $bill->oms_dfrom }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Mode</label>
                                <div class="col-sm-6">
                                    <select class="form-control select2" name="via" required>
                                        <option value="">Select One</option>
                                        <option value="1" {{ $bill->oms_mode == '1' ? 'selected' : '' }}>Bus</option>
                                        <option value="2" {{ $bill->oms_mode == '2' ? 'selected' : '' }}>Rickshaw
                                        </option>
                                        <option value="3" {{ $bill->oms_mode == '3' ? 'selected' : '' }}>CNG</option>
                                        <option value="4" {{ $bill->oms_mode == '4' ? 'selected' : '' }}>Leguna
                                        </option>
                                        <option value="5" {{ $bill->oms_mode == '5' ? 'selected' : '' }}>Bike
                                        </option>
                                        <option value="6" {{ $bill->oms_mode == '6' ? 'selected' : '' }}>
                                            Boat</option>
                                        <option value="7" {{ $bill->oms_mode == '7' ? 'selected' : '' }}>
                                            Lonch
                                        </option>
                                        <option value="8" {{ $bill->oms_mode == '8' ? 'selected' : '' }}>
                                            Rent-A-Car
                                        </option>
                                        <option value="9" {{ $bill->oms_mode == '9' ? 'selected' : '' }}>Taxi-Cab
                                        </option>
                                        <option value="10" {{ $bill->oms_mode == '10' ? 'selected' : '' }}>Train
                                        </option>
                                        <option value="11" {{ $bill->oms_mode == '11' ? 'selected' : '' }}>Plane
                                        </option>
                                        <option value="12" {{ $bill->oms_mode == '12' ? 'selected' : '' }}>Auto
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">To</label>
                                <div class="col-sm-6">
                                    <input type="text" name="to" class="form-control" id="inputEmail3"
                                        placeholder="Update Your Rejected Amount" value="{{ $bill->oms_dto }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Amount</label>
                                <div class="col-sm-6">
                                    <input type="text" name="oms_debit" class="form-control" id="inputEmail3"
                                        placeholder="Update Your Rejected Amount" value="{{ $bill->oms_debit }}" required>
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
            $(this).find("button[type='submit']").prop('disabled', true);
        });

        $(document).ready(function() {
            $('#qb-select').select2({
                ajax: {
                    url: '{{ route('oms.fetchQbData') }}', // Fetch API route
                    dataType: 'json',
                    delay: 250, // Delay in milliseconds before the search is triggered
                    data: function(params) {
                        return {
                            term: params.term // The search term entered by the user
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.qb_company_name + ' (' + item.qb_mobile + ')',
                                    id: item.qb_id
                                };
                            })
                        };
                    },
                    cache: true
                },
                // minimumInputLength: 2, // Minimum characters required to start the search
                // placeholder: 'Search for a company',
            });
        });

    </script>
@endsection

