@extends('layouts.master')
@section('title', 'Create Team')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('create_team_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Create Team</h4>
                </div>
            </div>

            <div class="panel-body">
            <div class="form-group row">
                        <label for="au_name" class="col-sm-2 col-form-div">Team Name<i class="text-danger">*</i></label>
                        <div class="col-sm-4">
                            {{Form::open(['action' => 'UserController@create_team_insert' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                                                <input type="text" name="tl_team_name" class="form-control" id="inputEmail3" placeholder="Enter Company Name" required>
                                                @if($errors->has('tl_team_name'))
                                                  <p class="text-danger">{{ $errors->first('tl_team_name') }}</p>
                                                @endif
                        </div>
                        <div class="form-group">
                        <button type="submit" class="btn btn-primary w-md m-b-5">Create</button>
                        
                        </div>
            </div>
            </div>
            
        </div>
    </div>
</div>
	<div class="box">
            <!-- /.box-header -->
            <div class="box">
            <div class="box-header">
              <h3 class="box-title">Team List</h3>
            </div>

            
            <!-- /.box-header -->
            <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>SL</th>
            <th>Team Name</th>
            <th class="text-center">Team Leader</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @php $sl=0; @endphp
        @foreach($teams as $team)
        <tr>
            <td>{{ ++$sl }}</td>
            <td>{{ $team->tl_team_name }}</td>
            <td align="center">
                @php
                $team_leader_name = '';
                foreach($team_leaders as $team_leader) {
                    if($team_leader->au_team_id == $team->tl_sl_id) {
                        $team_leader_name = $team_leader->au_name;
                        break;
                    }
                }
                @endphp
                @if($team_leader_name)
                {{ $team_leader_name }}
                @else
                <span style="color: green;">Available</span>
                @endif
            </td>
            <td align="center">
                <a href="{{ action('UserController@team_update', $team['tl_sl_id']) }}" class="btn btn-info btn-sm">UPDATE</a>
            </td>
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
              
            </div>

            <!-- /.box-header -->
            <!-- form start -->


              <!-- /.box-body -->
              <div class="box-footer">
                
              </div>
              <!-- /.box-footer -->
            {{Form::close()}}
          </div>
          </div>
         
@endsection
@section('custom_script')
<script>
      $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
</script>

@endsection