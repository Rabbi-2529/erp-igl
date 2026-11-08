@extends('layouts.master')

@section('title', 'Total Customer')

@section('customer_relation_class', 'active')
@section('customer_class', 'active')
@section('total_customer_class', 'active')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Total Customer</h4>
                </div>
            </div>

            
            
            <div class="panel-body">
                <div class="mt-2">
                    <!-- Export Data Button -->
                    <button class="btn btn-sm btn-primary" id="exportDataBtn">
                        Export Data
                    </button>
                </div>
                
                <!-- Modal -->
                <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exportModalLabel">Select Fields to Export</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportFields" id="exportMobileOnly" value="mobile">
                                    <label class="form-check-label" for="exportMobileOnly">
                                        Mobile Number Only
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportFields" id="exportMobileName" value="mobile_name">
                                    <label class="form-check-label" for="exportMobileName">
                                        Mobile Number and Name
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="exportConfirmBtn">Export</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <br>


                <div class="mt-3">
                    <!-- DataTable -->
                    {!! $dataTable->table(['class' => 'table-responsive table-bordered'], true) !!}
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Stylesheets -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">

<!-- JavaScript -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
{{$dataTable->scripts()}}

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#example2').dataTable({
            responsive: false
        });
    });
</script>

<script type="text/javascript">
    // Open Pop-up Windows
    window.onload = function() {
        var a = document.querySelectorAll('.opener'),
            w = [],
            url, random, i;
        for (i = 0; i < a.length; i++) {
            (function(i) {
                a[i].onclick = function(e) {
                    if (!w[i] || w[i].closed) {
                        url = this.href;
                        random = Math.floor((Math.random() * 100) + 1);
                        w[i] = window.open(url);
                    } else {
                        console.log('window ' + url + ' is already opened');
                    }
                    e.preventDefault();
                    w[i].focus();
                };
            })(i);
        }
    };

    // Handle Export Button Click
    document.getElementById('exportDataBtn').addEventListener('click', function() {
        $('#exportModal').modal('show');
    });

    // Handle Export Confirmation
    document.getElementById('exportConfirmBtn').addEventListener('click', function() {
        var selectedOption = $('input[name="exportFields"]:checked').val();
        var exportUrl = "{{ route('total_customer_list.export') }}" + "?fields=" + selectedOption;
        window.location.href = exportUrl;
        $('#exportModal').modal('hide');
    });

</script>


@endsection
