@extends('layout.master')
@section('money_class','menu-open')
@section('currency_class','menu-open')
@section('country','menu-open')
@section('country_list','active')
@section('content')
<section class="content">
        <section class="content-header">
              <h1>
                Country
              </h1>
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Country List</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Name</th>
                          <th>Currency Name</th>
                          <th>Currency Symbol</th>
                          <th>Currency Code</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @foreach ($countries as $country)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $country->name }}</td>
                            <td>{{ $country->currency_name  }}</td>
                            <td>{{ $country->currency_symbol  }}</td>
                            <td>{{ $country->currency_code }}</td>
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
   