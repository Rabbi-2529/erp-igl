@extends('layouts.master')
@section('title', 'Manage Team')

@section('human_resource_class','active')
@section('hrm_class','active')
@section('team_manage_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Manage Team</h4>
                </div>
            </div>

            <div class="panel-body">
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                      <thead>
                      <tr>
                        <th>SL</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Team Name</th>
                        <th>Members</th>
                        <th>Customers</th>
                        <th>Flexiload</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                      </tr>
                      </thead>
                      <tbody>
                      @php($sl=0)
                      @foreach($team_leaders as $team_leader)
                      <tr>
                        <td>{{ ++$sl }}</td>
                        <td>{{ $team_leader->au_user_id }}</td>
                        <td>{{ $team_leader->au_name }}</td>
                        <td>{{ $team_leader->au_mobile }}</td>
                        <td>{{ $team_leader->au_email }}</td>
                      <td>{{ optional($team_leader->team_info)['tl_team_name'] ?? 'No team name' }}</td>

                        <td align="center">
                        <a href="{{ url('/members_list',['id' => $team_leader->au_team_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Models\Admin_user::team_member($team_leader->au_team_id, $team_leader->au_company_id)->count() }}
                        </a>
                        </td>
      
                        <td align="center">
                        <a href ="{{ url('user/customer_list',['id' => $team_leader->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Models\Sds_query_book::user_customer($team_leader->au_id, $team_leader->au_company_id)->count() }}
                        </a>
                        </td>
      
                          <td>
                          <form action="{{ route('user-flexi-permission',$team_leader->au_id) }}" method="post">
                          @csrf
                            {{-- <input type="hidden" name="user" value="{{ $user->au_id }}"> --}}
                            <div class="form-check form-check-inline">
                              <input class="form-check-input" onchange="show_terget(this.value)" type="radio" {{ $team_leader->au_flexi_status == 1 ? 'checked' : '' }} name="status" id="inlineRadio1" value="1">
                              <label class="form-check-label" for="inlineRadio1">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input" onchange="show_terget(this.value)" type="radio" {{ $team_leader->au_flexi_status == 0 ? 'checked' : '' }} name="status" id="inlineRadio2" value="0">
                              <label class="form-check-label" for="inlineRadio1">No</label>
                            </div>
                            <div id="balance" style="{{ Auth::user()->au_flexi_status == 0 ? '' : 'display:none;' }}">
                              <input type="text" class="form-control" value="{{ $team_leader->au_flexi_balance }}" style="width:75px;" name="balance">
                            </div>
                            <button type="submit" class="btn btn-info btn-xs pull-right" style="margin-top: -60px;"><i class="fa fa-check"></i></button>
                          </form>
                          </td>
      
                          <td>
                            @if($team_leader->au_status == 0)
                            <a href="{{ route('pages.team_leader_active',['id' => $team_leader->au_id]) }}" class="user-activate" data-rel="tooltip" title="Confirm">
                                <span class="label label-sm label-warning">Suspend</span>
                            </a>
                            @elseif($team_leader->au_status == 1)
                                <a href="{{ route('pages.team_leader_suspend',['id' => $team_leader->au_id]) }}" class="user-inactivate" data-rel="tooltip" title="Conform">
                                    <span class="label label-sm label-success">Active</span>
                                </a>
                            @endif
                        </td>
      
                        <td align="center">
                          <div class="btn-group">
                            <button type="button" class="btn btn-info">Action</button>
                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                              <li>
                              <a href="{{ url('/user_profile',['id' => $team_leader->au_id]) }}">Show
                              </a>
                              </li>
                              <li class="divider"></li>
                              <li>
                              <a href="{{ url('/user_edit',['id' => $team_leader->au_id]) }}">Update
                              </a>
                              </li>
                              <li class="divider"></li>
                              <li><a href="{{ route('permission',['id' => $team_leader->au_id]) }}">Permission</a></li>
                            </ul>
                      </div>
                        </td>
                      </tr>
                      @endforeach
                      </tbody>
                    </table>
      
                  </div>
            </div>
        </div>
    </div>
</div>


@endsection