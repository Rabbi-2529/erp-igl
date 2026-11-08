@extends('layouts.master')
@section('content')
@section('title', 'Failed Submissions')

@section('customer_relation_class','active')
@section('customer_class','active')
@section('failed_submissions_class','active')

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

<style>
    .modal-lg {
        max-width: 90%;
    }
    .customer-details-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #e0e0e0;
    }
    .customer-details-card h6 {
        color: #37a000;
        margin-bottom: 15px;
        border-bottom: 2px solid #37a000;
        padding-bottom: 8px;
        font-weight: 600;
    }
    .detail-row {
        margin-bottom: 10px;
        display: flex;
        flex-wrap: wrap;
    }
    .detail-label {
        font-weight: 600;
        width: 140px;
        display: inline-block;
        color: #555;
        flex-shrink: 0;
    }
    .detail-value {
        color: #333;
        flex: 1;
    }
    .clickable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .clickable-row:hover {
        background-color: #f0f8ff !important;
    }
    .badge-failed {
        background-color: #dc3545;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    .customer-list-item {
        border-left: 3px solid #37a000;
        margin-bottom: 20px;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .customer-header {
        background: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
    }
    .badge-mobile-match {
        background-color: #28a745;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 10px;
    }
    .rating-stars {
        color: #ffc107;
        font-size: 16px;
    }
    .table th {
        background-color: #f4f4f4;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4><i class="fas fa-exclamation-triangle"></i> Failed Submissions</h4>
                </div>
                <div class="panel-tools">
                    <span class="badge badge-danger p-2">
                        Total: {{ $failedSubmissions->count() }}
                    </span>
                </div>
            </div>

            <div class="panel-body">
                @if($failedSubmissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered datatable table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th width="15%">Name</th>
                                    <th width="12%">Mobile</th>
                                    <th width="12%">Phone</th>
                                    <th width="15%">Email</th>
                                    <th width="15%">Company Name</th>
                                    <th width="15%">Attempted Date</th>
                                    <th width="10%">Attempted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failedSubmissions as $index => $failed)
                                    <tr class="clickable-row" 
                                        data-mobile="{{ $failed->fs_mobile }}"
                                        data-name="{{ $failed->fs_name ?: 'No Name' }}"
                                        data-failed-id="{{ $failed->fs_id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $failed->fs_name ?: 'No Name' }}</strong>
                                            @if($failed->fs_cus_type)
                                                <br><small class="text-muted">Type: {{ $failed->fs_cus_type }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $failed->fs_mobile ?: 'N/A' }}</td>
                                        <td>{{ $failed->fs_phone ?: 'N/A' }}</td>
                                        <td>{{ $failed->fs_email ?: 'N/A' }}</td>
                                        <td>{{ $failed->fs_company_name ?: 'N/A' }}</td>
                                        <td>
                                            @if($failed->fs_attempted_date)
                                                {{ date('d-m-Y h:i A', strtotime($failed->fs_attempted_date)) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($failed->attempted_by_user)
                                                {{ $failed->attempted_by_user->au_name }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No failed submissions found.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for Customer Details -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" role="dialog" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #37a000; color: white;">
                <h5 class="modal-title" id="customerDetailsModalLabel">
                    <i class="fas fa-user-circle"></i> Customer Records
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="customerDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading customer details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle click on table rows
    $('.clickable-row').on('click', function() {
        var mobile = $(this).data('mobile');
        var name = $(this).data('name');
        var failedId = $(this).data('failed-id');
        
        console.log('Clicked row data:', {
            mobile: mobile,
            name: name,
            failedId: failedId
        });
        
        if (!mobile || mobile == '' || mobile == 'N/A') {
            Swal.fire({
                icon: 'warning',
                title: 'No Mobile Number',
                text: 'This failed submission does not have a mobile number to search with.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        
        // Search for customers by mobile number
        searchCustomersByMobile(mobile, failedId, name);
    });
    
    function searchCustomersByMobile(mobile, failedId, name) {
        // Show modal with loading state
        $('#customerDetailsModal').modal('show');
        
        // Make AJAX request to search customers by mobile
        $.ajax({
            url: '{{ url("search-customers-by-mobile") }}',
            type: 'GET',
            data: {
                mobile: mobile
            },
            beforeSend: function() {
                $('#customerDetailsContent').html(`
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Searching for customers with mobile number: <strong>${mobile}</strong></p>
                        <p class="text-muted">Failed Submission: ${name} (ID: #${failedId})</p>
                    </div>
                `);
            },
            success: function(response) {
                console.log('Search response:', response);
                if (response.success) {
                    displayCustomerDetails(response, mobile, failedId);
                } else {
                    displayError(response.message, failedId);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                let errorMessage = 'An error occurred while fetching customer details.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                displayError(errorMessage, failedId);
            }
        });
    }
    
    function displayCustomerDetails(response, mobile, failedId) {
        var customers = response.data;
        var count = response.count;
        
        if (count == 0) {
            displayError('No customer records found with mobile number: ' + mobile, failedId);
            return;
        }
        
        var html = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Found <strong>${count}</strong> customer record(s) with mobile number: <strong>${mobile}</strong>
                <br><small>Failed Submission ID: #${failedId}</small>
            </div>
        `;
        
        customers.forEach(function(customer, index) {
            // Get rating stars
            var ratingStars = '';
            if (customer.qb_result) {
                for (var i = 1; i <= customer.qb_result; i++) {
                    ratingStars += '<i class="fas fa-star text-warning"></i>';
                }
            }
            
            // Determine which mobile field matched
            var matchedField = '';
            if (customer.qb_mobile == mobile) {
                matchedField = 'Mobile';
            } else if (customer.qb_mobile1 == mobile) {
                matchedField = 'Mobile 1';
            } else if (customer.qb_mobile2 == mobile) {
                matchedField = 'Mobile 2';
            }
            
            html += `
                <div class="customer-list-item">
                    <div class="customer-header">
                        <strong>Record #${index + 1}</strong>
                        <span class="badge-mobile-match">
                            <i class="fas fa-phone"></i> Match Found in ${matchedField}
                        </span>
                    </div>
                    <div class="customer-details-card" style="margin: 0;">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-user"></i> Personal Information</h6>
                                <div class="detail-row">
                                    <span class="detail-label">Customer ID:</span>
                                    <span class="detail-value"><strong>#${customer.qb_id}</strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value"><strong>${customer.qb_name || 'N/A'}</strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Mobile:</span>
                                    <span class="detail-value">${customer.qb_mobile || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Mobile 1:</span>
                                    <span class="detail-value">${customer.qb_mobile1 || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Mobile 2:</span>
                                    <span class="detail-value">${customer.qb_mobile2 || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone:</span>
                                    <span class="detail-value">${customer.qb_phone || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value">${customer.qb_email || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Birth Date:</span>
                                    <span class="detail-value">${customer.qb_birth_date || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Marriage Date:</span>
                                    <span class="detail-value">${customer.qb_marriage_date || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-building"></i> Company & Address</h6>
                                <div class="detail-row">
                                    <span class="detail-label">Company Name:</span>
                                    <span class="detail-value">${customer.qb_company_name || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Address:</span>
                                    <span class="detail-value">${customer.qb_address || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6><i class="fas fa-info-circle"></i> Submission Details</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Find Us:</span>
                                            <span class="detail-value">${customer.find_info ? customer.find_info.sf_howto : (customer.qb_find_us || 'N/A')}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Reason:</span>
                                            <span class="detail-value">${customer.reas_info ? customer.reas_info.sr_reason : (customer.qb_reason || 'N/A')}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Result:</span>
                                            <span class="detail-value rating-stars">${ratingStars || 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Submit Date:</span>
                                            <span class="detail-value">${customer.qb_submit_date || 'N/A'}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Customer Type:</span>
                                            <span class="detail-value">
                                                ${customer.qb_cus_type == 1 ? '<span class="text-success">Potential</span>' : 
                                                  (customer.qb_cus_type == 2 ? '<span class="text-warning">Pipeline</span>' : 
                                                  '<span class="text-primary">Regular</span>')}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="detail-value">
                                                <span class="badge ${customer.qb_status == 'Active' ? 'badge-success' : 'badge-secondary'}">
                                                    ${customer.qb_status || 'N/A'}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6><i class="fas fa-users"></i> Staff Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-row">
                                            <span class="detail-label">Entry By:</span>
                                            <span class="detail-value">${customer.entry_info ? customer.entry_info.au_name : (customer.qb_entry_by || 'N/A')}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-row">
                                            <span class="detail-label">Staff:</span>
                                            <span class="detail-value">${customer.entry_staff ? customer.entry_staff.au_name : (customer.qb_staff_id || 'N/A')}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Serial:</span>
                                    <span class="detail-value">${customer.qb_serial || 'N/A'}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Feedback:</span>
                                    <span class="detail-value">${customer.qb_feedback || 'N/A'}</span>
                                </div>
                                <div class="detail-row mt-2">
                                    <a href="/client_feedback/${customer.qb_id}" target="_blank" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View Full Details & Follow Up
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#customerDetailsContent').html(html);
    }
    
    function displayError(message, failedId) {
        var html = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <strong>Error:</strong> ${message}
            </div>
            <div class="text-center mt-3">
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh Page
                </button>
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        `;
        $('#customerDetailsContent').html(html);
    }
});
</script>

@endsection