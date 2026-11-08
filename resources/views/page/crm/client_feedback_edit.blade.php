@extends('layouts.master')
@section('title', 'Edit Customer Details')

@section('customer_relation_class', 'active')
@section('customer_class', 'active')
@section('total_customer_class', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                  <h4>Edit Customer Details</h4>
                </div>
            </div>
            {{-- Success Message --}}
        {{-- Success Message --}}
        @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
<div class="invalid-feedback" id="mobile_error_message" style="color: red;"></div>

            <div class="panel-body">
                {{ Form::open(['action' => ['SdsController@client_feedback_edit_update',$customer_edit->qb_id] , 'method' => 'post' , 'class' => 'form-horizontal']) }}
                    @if(Auth::user()->au_user_type == 4)
                        <div class="form-group">
                            <label for="qb_entry_by" class="col-sm-2 control-label">Switch User:</label>
                            <div class="col-sm-3">
                            @php
                                // Sorting the collection by au_user_id in ascending order
                                $sorted_com_users = $com_users->sortBy('au_user_id');
                            @endphp
                            
                            <select class="form-control select2" name="qb_entry_by">
                                <option value="">Select</option>
                                @foreach($sorted_com_users as $com_user)
                                    <option value="{{ $com_user->au_id }}" 
                                        {{ ($customer_edit->qb_staff_id == 0 || $customer_edit->qb_staff_id == '') 
                                            ? ($customer_edit->qb_entry_by == $com_user->au_id ? 'selected' : '') 
                                            : ($customer_edit->qb_staff_id == $com_user->au_id ? 'selected' : '') }}>
                                        {{ $com_user->au_user_id }} ({{ $com_user->au_name }})
                                    </option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th>Serial</th>
                                    <td>{{ $customer_edit->qb_serial }}</td>
                                    <th>Address</th>
                                    <td>
                                        <input type="text" name="qb_address" class="form-control" value="{{ $customer_edit->qb_address }}" placeholder="Address">
                                    </td>
                                </tr>
                                <tr>
    <th>Mobile No.</th>
    <td class="col-sm-12 d-flex">
    @if($customer_edit->qb_mobile && $customer_edit->qb_mobile1 && $customer_edit->qb_mobile2)
        <input type="number" name="qb_mobile" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile }}" placeholder="Mobile Number 1" data-field="qb_mobile">
        <input type="number" name="qb_mobile1" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile1 }}" placeholder="Mobile Number 2" data-field="qb_mobile1">
        <input type="number" name="qb_mobile2" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile2 }}" placeholder="Mobile Number 3" data-field="qb_mobile2">
    @elseif ($customer_edit->qb_mobile && $customer_edit->qb_mobile1)
        <input type="number" name="qb_mobile" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile }}" placeholder="Mobile Number 1" data-field="qb_mobile">
        <input type="number" name="qb_mobile1" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile1 }}" placeholder="Mobile Number 2" data-field="qb_mobile1">
        <input type="number" name="qb_mobile2" class="form-control col-sm-4" value="{{ old('qb_mobile2') }}" placeholder="Enter Mobile Number 3" data-field="qb_mobile2">
    @elseif ($customer_edit->qb_mobile)
        <input type="number" name="qb_mobile" class="form-control col-sm-4" value="{{ $customer_edit->qb_mobile }}" placeholder="Mobile Number 1" data-field="qb_mobile">
        <input type="number" name="qb_mobile1" class="form-control col-sm-4" value="{{ old('qb_mobile1') }}" placeholder="Enter Mobile Number 2" data-field="qb_mobile1">
        <input type="number" name="qb_mobile2" class="form-control col-sm-4" value="{{ old('qb_mobile2') }}" placeholder="Enter Mobile Number 3" data-field="qb_mobile2">
    @else
        <input type="number" name="qb_mobile" class="form-control col-sm-4" value="{{ old('qb_mobile') }}" placeholder="Enter Mobile Number 1" data-field="qb_mobile">
        <input type="number" name="qb_mobile1" class="form-control col-sm-4" value="{{ old('qb_mobile1') }}" placeholder="Enter Mobile Number 2" data-field="qb_mobile1">
        <input type="number" name="qb_mobile2" class="form-control col-sm-4" value="{{ old('qb_mobile2') }}" placeholder="Enter Mobile Number 3" data-field="qb_mobile2">
    @endif

    <!-- Error Messages -->
    <div id="mobile_error_message" class="invalid-feedback" style="display: none;"></div>
</td>

</tr>


                                
                                
                                <tr>
                                    <th>Customer Name</th>
                                    <td>
                                        <input type="text" name="qb_name" class="form-control" value="{{ $customer_edit->qb_name }}" placeholder="Customer Name">
                                    </td>

                                    <th>Email</th>
                                    <td>
                                        <input type="text" name="qb_email" class="form-control" value="{{ $customer_edit->qb_email }}" placeholder="Enter Customer Email">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Company Name</th>
                                    <td>
                                        <input type="text" name="company" class="form-control" value="{{ $customer_edit->qb_company_name }}" placeholder="Enter Company Name">
                                    </td>
                                    <th>Entry Date</th>
                                    <td>{{ $customer_edit->qb_submit_date }}</td>
                                </tr>
                                <tr>
                                    <th>Birth Date</th>
                                    <td>
                                        @if ($customer_edit->qb_birth_date)
                                            <input type="text" name="birth" id="birth_date" class="form-control" value="{{ \Carbon\Carbon::parse($customer_edit->qb_birth_date)->format('d/m/Y') }}" placeholder="Enter Marriage Date">
                                        @else
                                            <input type="text" name="birth" id="birth_date" class="form-control" placeholder="Enter Birth Date" autocomplete="off">
                                        @endif
                                    </td>
                                    
                                    <th>Marriage Date</th>

                                    <td>
                                        @if ($customer_edit->qb_marriage_date)
                                            <input type="text" name="marriage" id="marriage_date" class="form-control" value="{{ \Carbon\Carbon::parse($customer_edit->qb_marriage_date)->format('m/d/Y') }}" placeholder="Enter Marriage Date">
                                        @else
                                            <input type="text" name="marriage" id="marriage_date" class="form-control" placeholder="Enter Marriage Date" autocomplete="off">
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer Type</th>
                                    <td colspan="3">
                                        <label class="radio-inline">
                                            <input type="radio" value="1" name="cus_type" {{ ($customer_edit->qb_cus_type == 1 || $customer_edit->qb_cus_type == 0)? 'checked' : '' }}>Potential
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" value="2" name="cus_type" {{ ($customer_edit->qb_cus_type == 2)? 'checked' : '' }}>Pipeline
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" value="3" name="cus_type" {{ ($customer_edit->qb_cus_type == 3)? 'checked' : '' }}>Regular
                                        </label>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-3">
                            <button type="submit" class="btn btn-info">Update</button>
                        </div>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>




<script>
 $(document).ready(function(){
    // Initialize datepickers for birth and marriage dates
    $( "#birth_date" ).datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    $( "#marriage_date" ).datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // Mobile number validation for all fields
    $('input[name="qb_mobile"], input[name="qb_mobile1"], input[name="qb_mobile2"]').on('input', function () {
        let mobileNumber = $(this).val();
        let fieldName = $(this).attr('name');
        let recordId = "{{ $customer_edit->qb_id ?? '' }}";

        // Clear previous error messages and remove invalid class
        $('input[name="qb_mobile"], input[name="qb_mobile1"], input[name="qb_mobile2"]').removeClass('is-invalid');
        $('#mobile_error_message').text('');

        // If the mobile number field is empty, don't check it and clear any existing error message
        if (mobileNumber === "") {
            return; // Exit early if the field is empty
        }

        // Make the AJAX request to check if the mobile number is unique
        $.ajax({
            url: '{{ route("check_mobile_unique") }}',
            type: 'POST',
            data: {
                mobile_number: mobileNumber,
                field_name: fieldName,
                qb_mobile: $('input[name="qb_mobile"]').val(),
                qb_mobile1: $('input[name="qb_mobile1"]').val(),
                qb_mobile2: $('input[name="qb_mobile2"]').val(),
                id: recordId,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (response.exists) {
                    $('#mobile_error_message').text(response.message);
                    $('input[name="'+fieldName+'"]').addClass('is-invalid');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseJSON);
                alert('An error occurred while checking the mobile number.');
            },
        });
    });

    // Prevent form submission if any mobile field has an error
    $('form').on('submit', function(e) {
        // Check if any of the mobile fields have the 'is-invalid' class
        if ($('input[name="qb_mobile"], input[name="qb_mobile1"], input[name="qb_mobile2"]').hasClass('is-invalid')) {
            e.preventDefault(); // Prevent form submission
            alert('Please fix the errors before submitting the form.');
        }
    });
});



</script>


@endsection
