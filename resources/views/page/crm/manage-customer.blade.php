@extends('layouts.master')
@section('content')
@section('title', 'My Customer')

@section('customer_relation_class','active')
@section('customer_class','active')
@section('my_customer_class','active')


<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>My Customer</h4>
                </div>
            </div>

            <div class="panel-body">
                {!! $dataTable->table(['class' => 'table-responsive table-bordered '], true) !!}
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">

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