@extends('layouts.master')

@section('title', 'Quotation Print View')

@section('quotations_class', 'active')
@section('quotations_create', 'active')

<style>
    .form-control::-webkit-inner-spin-button,
    .form-control::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }
    .shopping_cart {
        min-height: 300px;
        border: 1px solid grey;
        padding: 10px;
    }
    input {
        height: 27px !important;
    }
    .btn-sm {
        padding: 3px 8px;
    }
    .action-column {
        width: 80px;
    }
    .quotation-print-container {
        max-width: 800px;
        margin: 0 auto;
    }

    /* Print Media Query */
    @media print {
        /* Hide elements when printing */
        .btn-primary {
            display: none; /* Hide Print button */
        }

        .panel-heading, .panel-title, .form-group {
            display: none; /* Hide the heading and the form group */
        }

        /* Hide all the navigation links and unwanted content */
        .navbar, .footer {
            display: none !important;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* Ensure Customer and Entry By info remain side by side during printing */
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col-sm-6 {
            flex: 1 1 50%; /* Each column takes up 50% of the width */
            padding: 10px;
        }
    }

    .entry-by-info {
        text-align: right;
        margin-top: 10px;
    }
</style>

@section('content')
<div class="quotation-print-container">
    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h4>Quotation #{{ $quotation->quotation_id }} - Print View</h4>
            </div>
        </div>

        <div class="panel-body">
            <!-- Entry By Information and Customer Information side by side -->
            <div class="row">
                <div class="col-sm-6">
                    <strong>Customer:</strong> {{ $quotation->customer->qb_name ?? 'N/A' }}<br>
                    <strong>Quotation Date:</strong> {{ $quotation->qt_date }}<br>
                    <strong>Expiry Date:</strong> {{ $quotation->qt_expiry_date }}<br>
                    <strong>Quotation ID:</strong> #{{ $quotation->quotation_id }}<br>
                    <strong>Discount:</strong> {{ number_format($quotation->qt_discount ?? 0.00, 2) }}<br>
                    <strong>Grand Total:</strong> {{ number_format($quotation->qt_final_amount ?? 0.00, 2) }}
                </div>
                <div class="col-sm-6">
                        <strong>Entry By:</strong> {{ \App\Models\Admin_user::userDetail($quotation->qt_entry_by) }}<br>
                        <strong>Company Name:</strong> {{ ($entryByUser->au_company_name) }}<br>
                        <strong>Email:</strong> {{ ($entryByUser->au_email) }}<br>
                        <strong>Phone Number:</strong> {{ ($entryByUser->au_mobile) }}<br>
                </div>
            </div>

            <!-- Product Table -->
            <div class="col-sm-12">
                <br>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Description</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productDetails as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                               <td>{{ $product['product_name'] ?? 'N/A' }}</td>
                                <td>{{ $product['quantity'] }}</td>
                                <td>{{ number_format($product['rate'], 2) }}</td>
                                <td>
                                <strong>Warranty:</strong> 
                                    @if(isset($product['warranty']))
                                        @php
                                            $years = floor($product['warranty'] / 365); // Calculate number of years
                                            $remainingDays = $product['warranty'] % 365; // Remaining days after dividing by 365
                                        @endphp

                                        @if($years > 0)
                                            {{ $years }} Year{{ $years > 1 ? 's' : '' }} 
                                            @if($remainingDays > 0)
                                                and {{ $remainingDays }} Day{{ $remainingDays > 1 ? 's' : '' }}
                                            @endif
                                        @else
                                            {{ $remainingDays }} Day{{ $remainingDays > 1 ? 's' : '' }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                    <br>
                                    <strong>Description:</strong> {{ $product['description'] ?? 'No description available' }}
                                </td>
                                <td>{{ number_format($product['quantity'] * $product['rate'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                            <td>{{ number_format($quotation->qt_total_amount ?? 0.00, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Discount:</strong></td>
                            <td>{{ number_format($quotation->qt_discount ?? 0.00, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                            <td>{{ number_format($quotation->qt_final_amount ?? 0.00, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Print Button -->
            <div class="form-group" style="float: right;">
                <button class="btn btn-primary" onclick="printPage()">Print</button>
            </div>
        </div>
    </div>
</div>

<script>
    function printPage() {
        // Hide the print button and other elements you don't want to appear in the print view
        document.querySelector('.btn-primary').style.display = 'none';
        document.querySelector('.panel-heading').style.display = 'none';
        document.querySelector('.panel-title').style.display = 'none';
        document.querySelector('.form-group').style.display = 'none';

        // Call the print dialog
        window.print();

        // After printing, make sure the elements are visible again
        setTimeout(function() {
            document.querySelector('.btn-primary').style.display = 'block';
            document.querySelector('.panel-heading').style.display = 'block';
            document.querySelector('.panel-title').style.display = 'block';
            document.querySelector('.form-group').style.display = 'block';
        }, 100);
    }
</script>
@endsection
