@extends('layouts.master')
@section('title', 'Commission List')

@section('oms_class', 'active')
@section('sell_comission_class', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Commission List</h4>
                </div>
            </div>
            <div class="panel-body">
                <table id="datatable" class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Emp. Name</th>
                      <th>Invoice</th>
                      <th>Created At</th>
                      <th>Invoice Qty</th>
                      <th>Invoice Price</th>
                      <th>Comm.</th>
                      <th>Total Comm.</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($com_details as $item)
                            @php
                                $totalComm = ($item->invoice_pro_price * $item->comission) / 100;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->user_name->au_name }}</td>
                                <td>{{ $item->invoice_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $item->invoice_pro_qty }}</td>
                                <td class="text-right">{{ number_format($item->invoice_pro_price, 2) }}</td>
                                <td class="text-right">{{ number_format($item->comission, 2) }}</td>
                                <td class="text-right">{{ number_format($totalComm, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
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
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            responsive: true,
            autoWidth: false
        });

        $('#product_category').on("change", function() {
            let grp_id = $(this).val();
            let link = "{{ route('inventory.product_submit_ajax') }}";
            $.ajax({
                type: "GET",
                url: link,
                data: { grp_id: grp_id },
                success: function(result) {
                    $(".product_category_wrapper").html(result);
                }
            });
        });

        $(".add_new_supplier_btn").click(function() {
            let new_supplier = $("#supplier-list-container").html();
            $("#others-supplier").append(new_supplier);
        });

        window.remove_supplier = function(btn) {
            $(btn).parent().remove();
        };

        $(".warranty_change").change(function() {
            if (this.value == 1) {
                $(".warranty_input_wrapper").show();
            } else {
                $(".warranty_input_wrapper").hide();
            }
        });
    });
</script>
@endsection
