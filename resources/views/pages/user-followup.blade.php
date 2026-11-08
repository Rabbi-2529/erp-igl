@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('user_follow_up','active')
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
    <div class="box">
    <div class="box-header">

  <form action="" method="get">
    <input type="hidden" name="_token" value="" id="_token">
   <div class="col-xs-3">
    <input type="text" name="start_date" value="{{ $today }}" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="start_date" placeholder="Enter Date" >
   </div>
      <div class="col-xs-3">
        <button type="submit" class="btn btn-info" name="searchbtn">Search</button>
      </div>
      </form>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
       </div>
   </div>
      <h1>
        All Users
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User Follow Up List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Name</th>
                  <th>Mobile</th>
                  <th>Team Name</th>
                  <th>TL Name</th>
                  <th>Entry</th>
                  {{--<th>Duplicate Entry</th>--}}
                </tr>
                </thead>
                <tbody>
                    @php($sl=0)
                    @foreach($users as $user)
                    <tr>
                      <td>{{ ++$sl }}</td>

                      <td>{{ $user->au_name }}</td>
                      <td>{{ $user->au_mobile }}</td>
                      <td>{{ $user->team_info == ''? '' : $user->team_info['tl_team_name'] }}</td>
                      <td>{{ @App\Admin_user::tl_name($user->au_team_id, $user->au_company_id)->au_name }}</td>
                      <td align="center"><a href="{{ route('user-follow-up-list',$user->au_id) }}" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Client_feedback::userFollowUp($user->au_id,$today) }}</a></td>
                      {{--<td align="center"><a href="#" class="btn btn-success btn-sm" style="width: 50px;">{{ App\Client_feedback::userFollowUpDuplicate($user->au_id) == 0 ? 0 : App\Client_feedback::userFollowUpDuplicate($user->au_id) }}</a></td>--}}

                    </tr>
                    @endforeach
                    </tbody>
              </table>

            </div>
            <!-- /.box-body -->
          </div>
         @endsection
         @section('custom_style')
         <link rel="stylesheet" href="{{asset('asset/css/icheck-bootstrap.min.css')}}">
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

      </script>
      <script type="text/javascript">

        //InitializeDate();
        /*
        function InitializeDate() {
            var date = new Date();
            var dd = date.getDate();
            var mm = date.getMonth() + 1;
            var yyyy = date.getFullYear();

            var ToDate = yyyy+'-'+mm+'-'+dd;
            var FromDate = yyyy+'-'+(mm-1)+'-'+dd;
            $('#start_date').datepicker('setDate', FromDate);
            $('#end_date').datepicker('setDate', ToDate);
        }*/

        $(document).ready(function(){

        $( "#start_date" ).datepicker({
               daysOfWeekHighlighted: "7",
                todayHighlight: true,
                autoclose: true,
             });



        });




        </script>
         @endsection

