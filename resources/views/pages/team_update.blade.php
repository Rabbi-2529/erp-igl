@extends('layouts.master')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Team Name Update
      </h1>
    </section>
          <div class="box box-info">
            <div class="box-header with-border">

            </div>
            <!-- /.box-header -->
            <!-- form start -->

            {{Form::open(['action' => ['UserController@team_update_submit', $team->tl_sl_id] , 'method' => 'post' , 'class' => 'form-horizontal'])}}
              <div class="box-body">
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">Team Name</label>
                  <div class="col-sm-6">

                    <input type="text" value="{{ $team->tl_team_name }}" name="tl_team_name" class="form-control" id="inputEmail3" placeholder="Enter Company Name" required>

                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-info pull-right">UPDATE</button>
                 </div>
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
         </section>
@endsection
