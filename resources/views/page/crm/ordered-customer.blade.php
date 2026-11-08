@extends('layouts.master')

@section('title', 'Ordered Customer')

@section('customer_relation_class', 'active')
@section('customer_class', 'active')
@section('ordered_customer_class', 'active')

@section('content')
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
        .big-font {
        font-size: 18px; /* Adjust the font size as needed */
    }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Ordered Customers</h4>
                    </div>
                </div>

                <div class="panel-body">




                    <table class="datatable table table-striped table-bordered table-hover">
                        
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reason</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Entry By</th>
                                <th>Address</th>
                                <th>Company Name</th>
                                <th>Find Us</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">

                            @foreach($orderCustomers as $key => $customer)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $customer->orderInfo->reas_info->sr_reason ?? 'N/A' }}</td>
                                <td>{{ $customer->orderInfo->qb_name ?? 'N/A' }}</td>

                                
                          <td>
  <a href="{{ url('/client_feedback', ['id' => optional($customer->cus_mobile)->qb_id]) }}">
    {{ maskNumber(optional($customer->cus_mobile)->qb_mobile) }}
</a>
</td>
                                <td>{{ $customer->feedback_info->au_name ?? 'N/A' }} ({{ $customer->feedback_info->au_user_id ?? 'N/A' }})</td>

                                <td>{{ $customer->orderInfo->qb_address ?? 'N/A' }}</td>
                                <td>{{ $customer->orderInfo->qb_company_name ?? 'N/A' }}</td>
                                <td>{{ $customer->orderInfo->find_info->sf_howto ?? 'N/A' }}</td>
                                <td>
                                    @if($customer->status == 0)
                                        <button class="btn btn-danger btn-sm update-order" 
                                                data-id="{{ $customer->cf_id }}"
                                                data-price="{{ $customer->cf_price }}"
                                                data-next-date="{{ $customer->cf_next_date }}"
                                                data-duration="{{ $customer->cf_call_duration }}">
                                            Pending
                                        </button>
                                    @elseif($customer->status == 1)
                                        <span class="badge badge-success">Confirm</span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Modal -->
                    <div id="updateOrderModal" class="modal fade" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Order Details</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="updateOrderForm">
                                        @csrf
                                        <input type="hidden" id="customer_id" name="customer_id">

                                        <div class="form-group">
                                            <label for="cf_next_date">Next Date</label>
                                            <input type="text" name="cf_next_date" class="form-control" placeholder="Next Date..." id="cf_next_date" autocomplete="off">
                                        </div>

                                        <div class="form-group">
    <label for="cf_price">Amount</label>
    <input type="text" class="form-control big-font" id="cf_price" name="cf_price" required>
</div>





                                        <div class="form-group">
                                            <label for="cf_call_duration">Duration</label>
                                            <input type="text" class="form-control" id="cf_call_duration" name="cf_call_duration" maxlength="5" required placeholder="MM:SS">
                                        </div>

                                        <button type="submit" class="btn btn-success">Confirm Order</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
$(document).ready(function () {
    // Filter the customer rows based on the selected "Entry By" user
    $('#entryBySearch').on('change', function () {
        var entryBy = $(this).val().toLowerCase(); // Get the selected "Entry By" ID

        $('#customerTableBody tr').each(function () {
            var rowEntryBy = $(this).data('entry-by').toLowerCase(); // Get the "Entry By" value from the row
            if (entryBy === "" || rowEntryBy.indexOf(entryBy) !== -1) {
                $(this).show(); // Show the row if it matches the selected "Entry By"
            } else {
                $(this).hide(); // Hide the row if it doesn't match
            }
        });
    });
});

    </script>
    <script>
    $(document).ready(function () {
        // Function to format number with commas
        function formatAmount(amount) {
            return amount.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Handle input change for amount with commas
        $('#cf_price').on('input', function () {
            let value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-numeric characters
            let formattedValue = formatAmount(value); // Format with commas
            $(this).val(formattedValue); // Update the input field
        });

        // Handle form submission
        $('#updateOrderForm').on('submit', function (e) {
            // Before submitting, remove commas for the actual value
            let price = $('#cf_price').val().replace(/,/g, ''); // Remove commas for submission
            $('#cf_price').val(price); // Update the input value with raw number
        });
    });
</script>

    <script>
        $(document).ready(function () {
            // Initialize Datepicker
            $("#cf_next_date").datepicker({
                dateFormat: 'yy-mm-dd',
                todayHighlight: true,
                autoclose: true,
                minDate: 0, // Prevent past dates
                changeMonth: true,
                changeYear: true
            });

            // Input validation for call duration (MM:SS format)
            $('#cf_call_duration').on('input', function () {
                let value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-numeric characters

                // Limit to 4 digits (MM:SS)
                if (value.length > 4) {
                    value = value.substring(0, 4);
                }

                // Format as MM:SS
                if (value.length >= 3) {
                    value = value.substring(0, 2) + ':' + value.substring(2, 4);
                } else if (value.length >= 2) {
                    value = value.substring(0, 2) + ':' + value.substring(2, 2);
                }

                // Update input value
                $(this).val(value);

                // Validate minutes and seconds (0-59)
                let parts = value.split(':');
                if (parts.length === 2) {
                    let minutes = parseInt(parts[0], 10);
                    let seconds = parseInt(parts[1], 10);

                    // Ensure minutes and seconds are within the range 00-59
                    if (minutes > 59) {
                        $(this).val('59:' + parts[1].substring(0, 2));
                    }

                    if (seconds > 59) {
                        $(this).val(parts[0] + ':59');
                    }
                }
            });

            // Open modal and populate fields
            $('.update-order').on('click', function () {
                let customerId = $(this).data('id');
                let price = $(this).data('price');
                let nextDate = $(this).data('next-date');
                let duration = $(this).data('duration');

                $('#customer_id').val(customerId);
                $('#cf_price').val(price);
                $('#cf_next_date').val(nextDate);
                $('#cf_call_duration').val(duration);

                $('#updateOrderModal').modal('show');
            });

            // Handle form submission
            $('#updateOrderForm').on('submit', function (e) {
                e.preventDefault(); // Prevent default form submission

                let formData = $(this).serialize(); // Get form data

                // Send AJAX request
                $.ajax({
                    url: "{{ url('/update_order') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            alert('Order updated successfully!');
                            location.reload(); // Reload the page to reflect the update
                        } else {
                            alert('Failed to update order.');
                        }
                    },
                    error: function () {
                        alert('Error updating order. Please check your input.');
                    }
                });
            });
        });
    </script>

@endsection
