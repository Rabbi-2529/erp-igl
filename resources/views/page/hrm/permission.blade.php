@extends('layouts.master')
@section('title', 'User Permission')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('manage_employee_class','active')

@section('content')

<style media="screen">
    .list-group-horizontal .list-group-item
    {
        display: inline-block;
    }
    .list-group-horizontal .list-group-item
    {
        margin-bottom: 0;
        margin-left:-4px;
        margin-right: 0;
        border-right-width: 0;
    }
    .list-group-horizontal .list-group-item:first-child
    {
        border-top-right-radius:0;
        border-bottom-left-radius:4px;
    }
    .list-group-horizontal .list-group-item:last-child
    {
        border-top-right-radius:4px;
        border-bottom-left-radius:0;
        border-right-width: 1px;
    }
    .custom_color .btn-primary {
        color: #fff;
        background-color: green;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>User Permission for {{ $admin_user->au_name }} ({{ $admin_user->au_company_name }})</h4>
                </div>
            </div>

            <div class="panel-body">

                <form action="{{ route('permission', $admin_user->au_id)}}" method="POST" class="form-horizontal">
                    @csrf
                    <div class="form-group" style="padding-top: 10px;">
                      <div class="col-sm-12">
                        <div class="row custom_color" style="left: 40px; position: relative;">
                          @foreach($permissions as $main_menu)
                          {{-- @if(Auth::user()->au_user_type == 4 || Auth::user()->au_permission_status == 1 || Auth::user()->au_permission_status == 2 || Auth::user()->au_permission_status == 3 || Auth::user()->au_permission_status == 4) --}}
                
                            <h2><i class="fa fa-folder" aria-hidden="true"></i> {{ $main_menu->permission_title }}</h2>
                            <ul class="list-group list-group-horizontal">
                              @foreach($main_menu->sub_menues as $first_step_sub_menues)
                                @if($first_step_sub_menues->permission_type == 2)
                                  <li class="list-group-item" style="width: 350px;">
                                    <input type="checkbox" name="au_permission[]" data-size="mini" value="{{ $first_step_sub_menues->code }}" {{ (in_array($first_step_sub_menues->code, $exist_permissions))? 'checked':'' }}  data-toggle="toggle" data-on="On" data-off="Off">
                                    {{ $first_step_sub_menues->permission_title }}
                                  </li>
                                @endif
                              @endforeach
                              @foreach($main_menu->sub_menues as $first_step_sub_menues)
                                @if($first_step_sub_menues->permission_type == 1)
                                  <h4><i class="fa fa-folder-o" aria-hidden="true"></i> {{ $first_step_sub_menues->permission_title }}</h4>
                                  @foreach($first_step_sub_menues->sub_menues as $second_step_sub_menues)
                                    <li class="list-group-item" style="width: 350px;">
                                      <input type="checkbox" name="au_permission[]" id="checkbox_check_id_{{ $second_step_sub_menues->access_id }}" data-size="mini" value="{{ $second_step_sub_menues->code }}" {{ (in_array($second_step_sub_menues->code, $exist_permissions))? 'checked':'' }}  data-toggle="toggle" data-on="On" data-off="Off">
                                      <label for="checkbox_check_id_{{ $second_step_sub_menues->access_id }}">{{ $second_step_sub_menues->permission_title }}</label>
                                    </li>
                                  @endforeach
                                @endif
                              @endforeach
                            </ul>
                            {{-- @endif --}}
                          @endforeach
                
                        </div>
                      </div>
                
                    </div>
                    <div class="col-sm-3">
                      <button type="submit" id="" class="btn btn-info">Update</button>
                   </div>
                </form>

            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('asset/css/bootstrap-toggle.min.css') }}">

<script>
        $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
        });
</script>

@endsection