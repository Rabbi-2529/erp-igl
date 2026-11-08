@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('intelligent_class','menu-open')
@section('search_find_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Find us
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Find us List</h3>
              <div class="form" style="margin-top: 20px;">
              <form class="form-inline" method="get" action="">
				  <div class="form-group">
				    <select class="form-control" name="find" style="width: 190px;">
				    	<option value="">Select</option>
              @foreach( $finds as $find )
              <option value="{{ $find->sf_slid }}" {{ ((request()->find == $find->sf_slid)? 'selected':'') }}>{{ $find->sf_howto }}</option>
              @endforeach
				    </select>
				  </div>
				  &nbsp;&nbsp;
				  <div class="form-group">
				    <input type="text" class="form-control" name="from" data-date-format="yyyy-mm-dd" autocomplete="off" id="from1" placeholder="From Date">
				  </div>
				  &nbsp;&nbsp;
				  <div class="form-group">
				    <input type="text" class="form-control" name="to" data-date-format="yyyy-mm-dd" autocomplete="off" id="from2" placeholder="To Date">
				  </div>
				  &nbsp;&nbsp;
          @if( Auth::user()->au_user_type == 5 )
          <div class="form-group">
            <select class="form-control" name="user">
              <option value="">Select</option>
              @foreach( $users as $user )
              <option value="{{ $user->au_id }}">{{ $user->au_name }}</option>
              @endforeach
            </select>
          </div>
          @endif
				  <input type="submit" class="btn btn-info" name="search_findus" value="Search">
				</form>
				</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>SL</th>
                    <th>Cus ID</th>
                    <th>Mobile</th>
                    <th>Cus Name</th>
                    <th>Company Name</th>
                    <th>Location</th>
                    <th>Reason</th>
                    <th>Result</th>
                    <th>Feedback</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($find_users as $fu)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $fu->qb_serial }}</td>
                    <td>{{ $fu->qb_mobile }}</td>
                    <td>{{ $fu->qb_name }}</td>
                    <td>{{ $fu->qb_company_name }}</td>
                    <td>{{ $fu->qb_address }}</td>
                    <td>{{ $fu->reas_info['sr_reason'] }}</td>
                    <td>
                      @for( $i=0; $i<$fu->qb_result; $i++ )
                        <b style="color: green; font-size: 20px;">*</b>
                      @endfor
                    </td>
                    <td>{{ $fu->followinfo['fm_msg'] }}</td>
                    <td><a href="{{ url('/client_feedback',['id' => $fu->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          </section>
          @endsection
          @section('custom_script')
          <script type="text/javascript">
          $(document).ready(function(){
            var date = new Date();
            date.setDate(date.getDate());
              $( "#from1" ).datepicker({
                     daysOfWeekHighlighted: "7",
                     autoclose: true,
                     todayHighlight: true,
                   });
              $( "#to" ).datepicker({
                     daysOfWeekHighlighted: "7",
                     todayHighlight: true,
                   });
          });

          $(document).ready(function(){
            var date = new Date();
            date.setDate(date.getDate());
              $( "#from2" ).datepicker({
                     daysOfWeekHighlighted: "7",
                     autoclose: true,
                     todayHighlight: true,
                   });
              $( "#to" ).datepicker({
                     daysOfWeekHighlighted: "7",
                     todayHighlight: true,
                   });
          });
          </script>
          @endsection
