@extends('layout.master')
@section('user_register_class','menu-open')
@section('user_register_user_list','active')
@section('content')
<section class="content">
    @if(session()->has('success'))
              <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
  @endif
<section class="content-header">
      <h1>
        All Users
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Name</th>
                  <th>Emp Id</th>
                  <th>Mobile</th>
                  <th>Email</th>
                  <th>Team Name</th>
                  <th>TL Name</th>
                  <th>Customers</th>
                  <th>Flexiload</th>
                  <th>Status</th>
                  <th class="text-center" style="width: 75px !important;">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach($users as $user)
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $user->au_name }}</td>
                  <td>{{ @$user->au_user_id	 }}</td>
                  <td>{{ $user->au_mobile }}</td>
                  <td>{{ $user->au_email }}</td>
                  <td>{{ !empty($user->team_info['tl_team_name']) ? $user->team_info['tl_team_name'] : '' }}</td>
                  <td>{{ @App\Models\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }}</td>
                  <td align="center"><a href="{{ url('user/customer_list',['id' => $user->au_id]) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ $user->customers_count }}</a></td>
                  <td>
                    <form action="{{ route('user-flexi-permission',$user->au_id) }}" method="post">
                      @csrf

                    {{-- <input type="hidden" name="user" value="{{ $user->au_id }}"> --}}
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" onchange="show_terget(this.value)" type="radio" {{ $user->au_flexi_status == 1 ? 'checked' : '' }} name="status" id="inlineRadio1" value="1">
                      <label class="form-check-label" for="inlineRadio1">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" onchange="show_terget(this.value)" type="radio" {{ $user->au_flexi_status == 0 ? 'checked' : '' }} name="status" id="inlineRadio2" value="0">
                      <label class="form-check-label" for="inlineRadio1">No</label>
                    </div>
                    <div id="balance" style="{{ Auth::user()->au_flexi_status == 0 ? '' : 'display:none;' }}">
                      <input type="text" class="form-control" value="{{ $user->au_flexi_balance }}" style="width:75px;" name="balance">
                    </div>
                    <button type="submit" class="btn btn-info btn-xs pull-right" style="margin-top: -60px;"><i class="fa fa-check"></i></button>
                  </form>
                  </td>

                  <td>
                    @if($user->au_status == 0)
                    <a href="{{ route('pages.user_list_activate',['id' => $user->au_id]) }}" class="user-activate" data-rel="tooltip" title="Confirm">
                        <span class="label label-sm label-warning">Inactive</span>
                    </a>
                    @elseif($user->au_status == 1)
                        <a href="{{ route('pages.user_list_deactivate',['id' => $user->au_id]) }}" class="user-inactivate" data-rel="tooltip" title="Conform">
                            <span class="label label-sm label-success">Active</span>
                        </a>
                    @endif
                  </td>

                  <td align="center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-info btn-xs">Action</button>
                      <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                        <li><a href="{{ url('/user_profile',['id' => $user->au_id]) }}">Show</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ url('/user_edit',['id' => $user->au_id]) }}">Update</a></li>

                        <li class="divider">{{ @App\Models\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }}</li>
                        <li class="divider"></li>
                        <li><a href="{{ route('permission',['id' => $user->au_id]) }}">Permission</a></li>
                      </ul>
                </div>
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
            </div>
          @endsection



          @section('custom_script')

          <script type="text/javascript">
            function show_terget(value) {
                if (value == '1') {
                    $('#balance').show();
                }
                else if (value == '0') {
                    $('#balance').hide();

                }
            }

            $("#checkAll").click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            // =====
        //     $(document).ready(function(){
        //     $('body').on('click', '.user-activate, .user-inactivate', function(e){
        //         e.preventDefault();
        //         var url = $(this).attr('href');
        //         var $element = $(this);
        //         $.ajax({
        //             url: url,
        //             type: 'GET',
        //             dataType: 'json',
        //             success: function(data){
        //                 if(data.status == 'success'){
        //                     // change the status and class of the user on the page
        //                     var userId = data.user_id;
        //                     var status = data.user_status;
        //                     var newClass = (status === 'Active') ? 'user-inactivate' : 'user-activate';
        //                     $element.removeClass('user-activate user-inactivate').addClass(newClass);
        //                     $element.find('.label').removeClass('label-success label-warning').addClass((status === 'Active') ? 'label-warning' : 'label-success');
        //                     $element.find('.label').text(status);
        //                 }
        //             },
        //             error: function(xhr, status, error){
        //                 console.log(xhr.responseText);
        //             }
        //         });
        //     });
        // });
        
      </script>
      @endsection
