@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('intelligent_class','menu-open')
@section('search_result_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Result
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Search By Result</h3>
              <div class="form" style="margin-top: 20px;">
              <form class="form-inline" action="">
				  <div class="form-group">
				    <select class="form-control" name="result" style="width: 190px;">
				    	<option value="">Select</option>
              <option value="1" {{ ((request()->result == 1)? 'selected':'') }} style="color: green; font-size: 25px;">*</option>
              <option value="2" {{ ((request()->result == 2)? 'selected':'') }} style="color: green; font-size: 25px;">**</option>
              <option value="3" {{ ((request()->result == 3)? 'selected':'') }} style="color: green; font-size: 25px;">***</option>
              <option value="4" {{ ((request()->result == 4)? 'selected':'') }} style="color: green; font-size: 25px;">****</option>
              <option value="5" {{ ((request()->result == 5)? 'selected':'') }} style="color: green; font-size: 25px;">*****</option>
				    </select>
				  </div>
				  &nbsp;&nbsp;
				  <div class="form-group">
				    <input type="text" class="form-control" name="from" autocomplete="off" id="from1" placeholder="From Date">
				  </div>
				  &nbsp;&nbsp;
				  <div class="form-group">
				    <input type="text" class="form-control" name="to" autocomplete="off" id="from2" placeholder="To Date">
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
                  @foreach( $find_users as $result )
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $result->qb_serial }}</td>
                    <td>{{ $result->qb_mobile }}</td>
                    <td>{{ $result->qb_name }}</td>
                    <td>{{ $result->qb_company_name }}</td>
                    <td>{{ $result->qb_address }}</td>
                    <td>{{ $result->reas_info['sr_reason'] }}</td>
                    <td>
                      @for( $i=0; $i<$result->qb_result; $i++ )
                        <b style="color: green; font-size: 20px;">*</b>
                      @endfor
                    </td>
                    <td>{{ $result->followinfo['fm_msg'] }}</td>
                    <td><a href="{{ url('/client_feedback',['id' => $result->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a></td>
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
