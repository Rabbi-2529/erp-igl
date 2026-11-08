@extends('layouts.master')
@section('sds_menu_class','active')
@section('followup_class','active')
@section('follow_cus_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Follow Up
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title"> Customers List</h3>
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
            responsive: true,
        } );

    </script>
@endsection
