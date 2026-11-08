@extends('layout.master')
@section('content')
<section class="content">
    <section class="content-header">
          <h1>
            Follow Up
          </h1>
    
        </section>
        <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Customers List</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Permission Name</th>
                      <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($i)
                    @php($sl=0)
                    @foreach( $permissions as $p )
                    <tr>
                      <td>{{ ++$sl }}</td>
                      <td>{{ $p->permission_title }}</td>
                      <td></td>
                    </tr>
                    @endforeach
                    </tbody>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>
             </section>
@endsection