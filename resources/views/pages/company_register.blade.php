@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('create_company_class','menu-open')
@section('create_company_sub','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Created Today
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Companies List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Company Name</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($companys as $company)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $company->au_user_id }}</td>
                  <td>{{ $company->au_name }}</td>
                  <td>{{ $company->au_company_name }}</td>
                  <td>{{ $company->au_mobile }}</td>
                  <td>{{ $company->au_email }}</td>
                  <td align="center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-info">Action</button>
                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                        <li><a href="{{ url('/user_profile',['id' => $company->au_id]) }}">Show</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Update</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Activate Company</a></li>
                      </ul>
                </div>
                  </td>
                  @endforeach
                </tr>
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Company Registration</h3>
            </div>
            @if(session()->has('msg'))
              <h3 class="text-success">{{ session('msg') }}</h3>
            @endif
            <!-- /.box-header -->
            <!-- form start -->
            {{Form::open(['action' => 'SdsController@company_register_insert','method' => 'post','files' => 'true' ,'class' => 'form-horizontal'])}}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Company Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_company_name" class="form-control" id="inputEmail3" placeholder="Enter Company Name">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_name" class="form-control" id="inputEmail3" placeholder="Enter Authorized Name">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">User ID</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_user_id" class="form-control" id="inputEmail3" placeholder="Place User ID">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Email</label>
                  <div class="col-sm-6">
                    <input type="email" name="au_email" class="form-control" id="inputEmail3" placeholder="Place Email...">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Mobile</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_mobile" class="form-control" id="inputEmail3" placeholder="Enter Mobile Number">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
                  <div class="col-sm-6">
                    <input type="password" name="au_password" class="form-control" id="inputPassword3" placeholder="Create Password...">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Address</label>
                  <div class="col-sm-6">
                    <input type="text" name="au_address"  class="form-control" id="inputEmail3" placeholder="Put Detail Address..">
                  </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">Company Logo</label>
                    <div class="col-sm-6">
                      <input type="file" name="logo" class="form-control" id="inputEmail3" placeholder="">
                    </div>
                </div>
                <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Image</label>
                  <div class="col-sm-6">
                    <input type="file" name="au_company_img" class="form-control" id="inputEmail3" placeholder="">
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">Create</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
         </section>
@endsection
@section('custom_script')
<script>
$('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
</script>
@endsection
