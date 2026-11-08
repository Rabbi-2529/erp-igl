@extends('layouts.master')
@section('sds_menu_class','active')
@section('customer_class','active')
@section('my_customer_list_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Customer List
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">The User's Customer</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                {!! $dataTable->table(['class' => 'table-responsive table-bordered '], true) !!}
            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@section('custom_style')

<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
@endsection
@section('custom_script')
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    {{$dataTable->scripts()}}
    <script>
        $('#example2').dataTable( {
            responsive: false,
        } );

    </script>
    <script type="text/javascript">
        window.onload = function(){
            var a = document.querySelectorAll('.opener'), w = [], url, random, i;
            for(i = 0; i < a.length; i++){
                (function(i){
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
    </script>
@endsection
