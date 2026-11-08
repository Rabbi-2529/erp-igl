@extends('layouts.master')
@section('title','Contra List')
@section('inventory_class','active')
@section('accounts_class','active')
@section('voucher_class','active')
@section('contra_list','active')
@section('content')
<section class="content">
        <section class="content-header">
@if(Session::has('errmsg'))
<div class="alert alert-danger alert-dismissible" role="alert">
{{ session('errmsg') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
@endif
@if(Session::has('msg'))
<div class="alert alert-success alert-dismissible" role="alert">
{{ session('msg') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
@endif
</select>

              <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Contra List</h3>
                        </div>
                    </div>

                  <div class="panel-body" >

                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Bank</th>
                          <th>Description</th>
                          <th>Reference</th>
                          <th>Credit</th>
                          <th>Debit</th>
                          <th>Edit</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($contras as $contra)

                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ \Carbon\Carbon::parse($contra->inv_abs_transaction_date)->format('Y-m-d') }}</td>
                            <td>{{ App\Models\Inv_acc_bank_statement::get_Bank_Name_By_Bank_ID($contra->inv_abs_bank_id)->bank_name}}</td>
                            <td>{{ $contra->inv_abs_description}}</td>
                            <td>{{ App\Models\Inv_acc_bank_statement::get_Bank_Name_By_Refer_ID($contra->inv_abs_reference_id)->bank_name }}</td>
                            <td class="text-right">{{ $contra->inv_abs_credit}}</td>
                            <td class="text-right">{{ $contra->inv_abs_debit }}</td>

                                <td>
                                  <a href="{{ route('accounts.edit-contra', $contra->inv_abs_id) }}" 
           class="btn btn-xs btn-primary btn_edit action_btn"
           title="Edit Contra Voucher">
            <i class="fa fa-edit"></i> Edit
        </a>
                                    </td>
                          </tr>
                        @endforeach
                        </tbody>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>
@endsection
@section('custom_style')
    <style>
        .action_btn{
            border: 1px solid;
            padding: 5px;
        }
        .btn_show{
            background-color: green;
            color: white;
        }
        .btn_delete{
            background-color: red;
            color: white;
        }
        .btn_edit{
            background-color: cornflowerblue;
            color: white;
        }
    </style>
    @endsection
