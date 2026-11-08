{{--@extends('layout.master')--}}
@section('sds_menu_class','menu-open')
@section('customer_class','menu-open')
@section('total_customer_list_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Customer List
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Total Customers List</h3>



            </div>
            <!-- /.box-header -->



            <div class="mt-5">
                <a href="{{ route('total_customer_list.export') }}" class="btn btn-primary">
                    Export Data
                </a>

            </div>
            <div class="box-body">
                {!! $dataTable->table() !!}
{{--                {!! $dataTable->table(['class' => 'table table-bordered'], true) !!}--}}

            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@push('scripts')
    {{$dataTable->scripts()}}
@endpush
@section('custom_style')

    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
@endsection
@section('custom_script')









    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

{{--    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>--}}

    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>

    <script>


        $(document).ready( function () {

        } );


    </script>


@endsection





