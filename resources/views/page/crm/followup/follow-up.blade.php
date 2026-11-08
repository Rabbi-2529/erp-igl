@extends('layouts.master')
@section('title', 'Follow Up')


@section('customer_relation_class','active')
@section('followup_class','active')

@section('follow_cus_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Follow-Up Customers List</h4>
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
        responsive: true,
    } );
</script>

@endsection