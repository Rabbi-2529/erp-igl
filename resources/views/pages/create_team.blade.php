@extends('layout.master')
@section('user_register_class','menu-open')
@section('user_register_create_team','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        All Team
      </h1>
    </section>
	<div class="box">
            <!-- /.box-header -->
            <div class="box">
            <div class="box-header">
              <h3 class="box-title">Team List</h3>
            </div>
            @if(session()->has('msg1'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg1') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            @if( session()->has('msg') )
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Team Name</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($teams as $team)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $team->tl_team_name }}</td>
                  <td align="center"><a href="{{action('UserController@team_update' , $team['tl_sl_id'])}}" class="btn btn-info btn-sm">UPDATE</a></td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
            <!-- /.box-body -->
            <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Team Registration</h3>
            </div>

            <!-- /.box-header -->
            <!-- form start -->

            {{Form::open(['action' => 'UserController@create_team_insert' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Team Name</label>
                  <div class="col-sm-6">
                    <input type="text" name="tl_team_name" class="form-control" id="inputEmail3" placeholder="Enter Company Name" required>
                    @if($errors->has('tl_team_name'))
                      <p class="text-danger">{{ $errors->first('tl_team_name') }}</p>
                    @endif
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
