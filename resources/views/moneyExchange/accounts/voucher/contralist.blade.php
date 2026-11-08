@extends('layout.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('voucher_class','menu-open')
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

              <h1>
                Contra
              </h1>
        
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Contra List</h3>
                    </div>
                   
                    <!-- /.box-header -->
                    <div class="box-body">


                      <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Date</th>
                          <th>Bank</th>
                          <th>Description</th>
                          <th>Reference</th>
                          <th>Credit</th>
                          <th>Debit</th>
                          
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($contras as $contra)

                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{$contra->inv_abs_transaction_date}}</td>
                            <td>{{ App\Inv_acc_bank_statement::get_Bank_Name_By_Bank_ID($contra->inv_abs_bank_id)->bank_name}}</td>
                            <td>{{ $contra->inv_abs_description}}</td>
                            <td>{{ App\Inv_acc_bank_statement::get_Bank_Name_By_Refer_ID($contra->inv_abs_reference_id)->bank_name }}</td>
                            <td class="text-right">{{ $contra->inv_abs_credit}}</td>
                            <td class="text-right">{{ $contra->inv_abs_debit }}</td>
                          

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