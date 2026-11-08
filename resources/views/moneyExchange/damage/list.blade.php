@extends('layout.master')
@section('money_class','menu-open')
@section('damage_class','menu-open')
@section('damage_list','active')
@section('content')
<section class="content">
            <section class="content-header">
                  <h1>
                    Damage
                  </h1>
                </section>
                <div class="box">
                        <div class="box-header">
                          <h3 class="box-title">Damage List</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                          <table id="example1" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                              <th>SL</th>
                              <th>Country Name</th>
                              <th>Currency Name</th>
                              <th>Damage Qty</th>
                            </tr>
                            </thead>
                            <tbody>
                            
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center"></td>
                              </tr>
                            
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
        