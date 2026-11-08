@extends('layouts.master')

@section('title', 'Sell Product')

@section('inventory_class','active')
@section('product_inv_class','active')
@section('sell_product_class','active')

@section('content')
<br>

<style>
  .box-header {
      color: #fff;
      text-align: center;
      border-radius: 8px;
  }

  .invoice {
      background-color: #3c8dbc;
      width: 10%;
      color: #fff;
      border-radius: 5px;
      padding: 10px;
      margin: 0 auto;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }

  .table {
      width: 100%;
      max-width: 100%;
      margin-bottom: 20px;
      border-collapse: collapse;
      background-color: white;
      border-spacing: 0;
      box-shadow: 0 1px 20px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      overflow: hidden;
  }

  .table th, .table td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
  }

  .table th {
      background-color: #f2f2f2;
  }
  
  .product-name {
      font-weight: bold;
      color: #333;
  }
  
  .product-detail {
      font-size: 0.9em;
      color: #666;
  }
  
  .serial-numbers {
      color: #555;
      font-size: 0.85em;
  }
  
  .warranty-badge {
      display: inline-block;
      padding: 2px 8px;
      background-color: #ffc107;
      color: #000;
      border-radius: 12px;
      font-size: 0.75em;
      font-weight: bold;
  }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Sell Product</h4>
                </div>
            </div>

            @if(session()->has('det_up'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('det_up') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
        
            @if(session()->has('sub_err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('sub_err') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <div class="panel-body">
                <div class="box-header text-center">
                    <h3 class="box-title invoice"><b>Invoice</b></h3>
                </div>
                <br>
                <div class="col-sm-12">
                    <table class="table table-bordered table-striped table-hover datatable">
                        <tr>
                            <th>Customer Name</th>
                            <td>
                                {{ $pro_cus->inv_cus_name ?? 'N/A' }} 
                                @if(!empty($pro_cus->inv_cus_com_name))
                                    ({{ $pro_cus->inv_cus_com_name }})
                                @endif
                            </td>
                            
                            @if (Auth::user()->au_user_type == 4)
                            <th>Sales Person:</th>
                            <td>
                                {{ $emp->au_name ?? 'N/A' }}
                                @if(request()->has('comission') && !empty(request()->comission))
                                    <b> (Commission: {{ request()->comission }})</b>
                                @endif
                            </td>
                            @endif
                        </tr>
                        <tr>
                            <th>Customer Address</th>
                            <td>{{ $pro_cus->inv_cus_address ?? 'N/A' }}</td>
                            <th>Invoice No:</th>
                            <td>{{ date('Ymd') }}{{ Auth::user()->au_id }}</td>
                        </tr>
                        <tr>
                            <th>Customer Mobile</th>
                            <td>{{ $pro_cus->inv_cus_mobile ?? 'N/A' }}</td>
                            <th>Sold By</th>
                            <td>
                                @if($pro_temps->isNotEmpty() && isset($pro_temps->first()->sold_by))
                                    {{ $pro_temps->first()->sold_by->au_name ?? Auth::user()->au_name }}
                                @else
                                    {{ Auth::user()->au_name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Invoice Date</th>
                            <td colspan="3">
                                <strong>{{ $invoiceDate ?? date('Y-m-d') }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
      
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">SL</th>
                            <th class="text-left">Description</th>
                            <th class="text-center">Sold Qty</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @php($balance=0)
                    @php($total = 0)
                    @foreach ($pro_temps as $temp)
                    <tr>
                        <td class="text-center">{{ ++$sl }}</td>
                        <td class="text-left">
                            <?php
                                // Get product name with multiple fallbacks
                                $productName = 'Unknown Product';
                                
                                // Try 1: From temporary table
                                if (!empty($temp->inv_pro_temp_pro_name)) {
                                    $productName = $temp->inv_pro_temp_pro_name;
                                }
                                // Try 2: Through productDetail relationship
                                elseif ($temp->productDetail && $temp->productDetail->product) {
                                    $productName = $temp->productDetail->product->inv_pro_name ?? 'Unknown Product';
                                }
                                // Try 3: Through pro_warranty relationship (older relationship)
                                elseif ($temp->pro_warranty && $temp->pro_warranty->product) {
                                    $productName = $temp->pro_warranty->product->inv_pro_name ?? 'Unknown Product';
                                }
                                
                                // Get description
                                $description = '';
                                if ($temp->productDetail) {
                                    $description = $temp->productDetail->inv_pro_det_pro_description ?? '';
                                } elseif ($temp->pro_warranty) {
                                    $description = $temp->pro_warranty->inv_pro_det_pro_description ?? '';
                                }
                                
                                // Get warranty
                                $warranty = 0;
                                if ($temp->productDetail) {
                                    $warranty = $temp->productDetail->inv_pro_det_pro_warranty ?? 0;
                                } elseif ($temp->pro_warranty) {
                                    $warranty = $temp->pro_warranty->inv_pro_det_pro_warranty ?? 0;
                                }
                                
                                // Get serial numbers
                                $serialNumbers = [];
                                if (!empty($temp->inv_pro_temp_slno)) {
                                    $serialNumbers = explode(',', $temp->inv_pro_temp_slno);
                                }
                            ?>
                            
                            <!-- Product Name -->
                            <span class="product-name">{{ $productName }}</span>
                            
                            <!-- Description -->
                            @if(!empty($description))
                                <br>
                                <span class="product-detail">{{ $description }}</span>
                            @endif
                            
                            <!-- Serial Numbers -->
                            @if(!empty($serialNumbers) && isset($serialNumbers[0]) && !empty($serialNumbers[0]))
                                <br>
                                <span class="serial-numbers">
                                    <b>SN: </b>{{ implode(', ', array_filter($serialNumbers)) }}
                                </span>
                            @endif
                            
                            <!-- Warranty -->
                            @if($warranty > 0)
                                <br>
                                <span class="warranty-badge">Warranty: {{ $warranty }} Days</span>
                            @endif
                        </td>

                        <td class="text-center">{{ $temp->inv_pro_temp_qty ?? 0 }}</td>
                        <td class="text-right">{{ number_format($temp->inv_pro_temp_unit_price ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format(($temp->inv_pro_temp_unit_price ?? 0) * ($temp->inv_pro_temp_qty ?? 0), 2) }}</td>
                    </tr>
                    @php($balance = $balance + (($temp->inv_pro_temp_unit_price ?? 0) * ($temp->inv_pro_temp_qty ?? 0)))
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total Amount</strong></td>
                            <td class="text-right"><strong>{{ number_format($balance, 2) }}</strong></td>
                        </tr>
                        @if(isset($discount) && $discount > 0)
                        <tr>
                            <td colspan="4" class="text-right"><strong>Sale Discount</strong></td>
                            <td class="text-right"><strong>{{ number_format($discount, 2) }}</strong></td>
                        </tr>
                        @endif
                        @if(isset($delivery) && $delivery > 0)
                        <tr>
                            <td colspan="4" class="text-right"><strong>Shipping Cost</strong></td>
                            <td class="text-right"><strong>{{ number_format($delivery, 2) }}</strong></td>
                        </tr>
                        @endif
                        @php($total = ($balance + ($delivery ?? 0)) - ($discount ?? 0))
                        <tr style="background-color: #f9f9f9; font-size: 1.1em;">
                            <td colspan="4" class="text-right"><strong>Net Payable Amount</strong></td>
                            <td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                  
                {{ Form::open(['action' => 'Inventory\InventoryCartController@cartSubmit', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'submitForm']) }}
                    
                    <!-- IMPORTANT: Add hidden field for the date -->
                    <input type="hidden" name="inv_pro_temp_created_at" value="{{ $invoiceDate ?? date('Y-m-d') }}">
                    
                    <input type="hidden" name="customer" value="{{ $pro_cus->inv_cus_id ?? '' }}">
                    <input type="hidden" name="discount" value="{{ $discount ?? 0 }}">
                    <input type="hidden" name="delivery" value="{{ $delivery ?? 0 }}">
                    <input type="hidden" name="is_project" value="{{ request()->project ?? 0 }}">
                    <input type="hidden" name="employee" value="{{ $emp->au_id ?? '' }}">
                    
                    @if (Auth::user()->au_user_type == 4 && request()->has('comission') && request()->comission != '')
                        <input type="hidden" name="comission" value="{{ request()->comission }}">
                    @else
                        <input type="hidden" name="comission" value="0.00">
                    @endif
                    
                    <button type="submit" class="btn btn-success pull-right" onclick="return confirm('Are you sure you want to complete this sale?')">
                        <i class="fa fa-check"></i> Confirm Sale
                    </button>
                {{ Form::close() }}
      
                <a href="{{ route('buy.pro_sell') }}" class="btn btn-danger pull-right" style="margin-right: 5px;">
                    <i class="fa fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('submitForm');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Do you want to print?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    customClass: {
                        cancelButton: 'btn btn-danger mr-2',
                        confirmButton: 'btn btn-success'
                    },
                    buttonsStyling: false,
                    allowOutsideClick: false,
                    reverseButtons: true
                }).then((result) => {
                    form.submit();
                });
            } else {
                // Fallback if SweetAlert not loaded
                if (confirm('Confirm sale and print invoice?')) {
                    form.submit();
                }
            }
        });
    }
});
</script>

@endsection