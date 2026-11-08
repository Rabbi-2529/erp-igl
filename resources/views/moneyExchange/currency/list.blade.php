@extends('layout.master')
@section('money_class','menu-open')
@section('currency_class','menu-open')
@section('currencies_class','menu-open')
@section('currency_list','active')
@section('content')
<section class="content">
   
   
        <section class="content-header">
              <h1>
                Currency
              </h1>
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Currencies List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Country</th>
                          <th>Currency Name</th>
                          <th>Currencies</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($currencies as $curr)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $curr->country_name['name'] }}</td>
                            <td>{{ $curr->country_name['currency_name'] }}</td>
                            <td>({{ $curr->country_name['currency_symbol'] }})&nbsp;{{ $curr->currency_note }}</td>
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
    