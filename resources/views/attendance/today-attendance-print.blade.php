<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		table{
			border-collapse: collapse;
			width: 100%;
		}
		table, th, td{
			border: 1px solid lightgray;
		}
		th, td{
			padding: 3px;
		}

		@page { margin: 150px 50px; }

	    #header {
			position: fixed;
			left: 0px;
			top: -120px;
			right: 0px;
			height: 150px;
		    text-align: center;
		}
		#footer {
			position: fixed;
			left: 0px;
			bottom: -100px;
			right: 0px;
			height: 70px;
		}
	    #footer .page:after { content: counter(page, upper-roman); }
	</style>
</head>
<body>

	<h3 style="text-align: center;">Today Attendance Report</h3>
  <table id="example1" class="table table-bordered table-striped">
    <thead>
    <tr>
      <th>SL</th>
      <th>Emp Id</th>
      <th>User Name</th>
      <th>Check In</th>
      <th>Check In Ip</th>
      <th>Check Out</th>
      <th>Check Out Ip</th>
      <th>Working Hour</th>
      <th>Over Time</th>
      <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @php($comWorkShift = \App\Models\Admin_user::where('au_company_id',Auth::user()->au_company_id)->first())
    {{-- @dd($comWorkShift) --}}
    @php($workshift = explode(',',$comWorkShift->au_work_shift))
    {{-- @dd($workshift[0]); --}}
    @php($sl=0)
    @php($late = 0)
    @php($over = 0)
    @foreach ($usersAttendance as $user)

    <tr>
        <td>{{ ++$sl }}</td>
        @foreach ($userComId as $employeId)
                          @if ($user->user_id == $employeId->au_id)
                                <td>{{ $employeId->au_user_id }}</td>
                          @endif
                          @endforeach
          <td>{{ App\Models\Admin_user::attendanceUser($user->user_id) }}</td>

          @php($in_time = date('H:i', strtotime($user->check_in)))
          @php($check_out = date('H:i', strtotime($user->check_out)))

          @php($date1 = \Carbon\Carbon::parse($in_time))
          @php($date2 = \Carbon\Carbon::parse($check_out))
          @php( $total_time = $date1->diffInMinutes($date2) )


          {{-- in time --}}

          @php($au_user = \App\Models\Admin_user::where('au_id',$user->user_id)->first())

          @if ($au_user->au_shifts != null)

              @php($auWorkshift = \App\Models\PayrollShiftManagement::where('id',$au_user->au_shifts)->first())
              @php($workShift = explode(',',$auWorkshift->working_hour))

          @else
          @php($workShift = explode(',',$comWorkShift->au_work_shift))
          @endif




          @php($lateCheckIn = $workShift[0])
          @php($dbtimestamp = strtotime($lateCheckIn))

          @php($checkIn = strtotime($user->check_in))
          @php($abcd = date('H:i:s', $checkIn))
          @php($defg = strtotime($abcd))

          @if ( $defg - $dbtimestamp > 15*60 )
          <td style="color: red;">
            {{ date('H:i A', strtotime($user->check_in)) }}

          </td>

          @else
          <td style="color: green;">
            {{ date('H:i A', strtotime($user->check_in)) }}
          </td>

          @endif
          <td>
            <span>({{ $user->user_ip }})</span>
            @if (App\Models\Admin_user::illegalLlegalEntry($user->user_ip)=='Illegal')
            <span style="color: red;">({{ App\Models\Admin_user::illegalLlegalEntry($user->user_ip) }})</span>
            @else
            <span style="color: green;">({{ App\Models\Admin_user::illegalLlegalEntry($user->user_ip) }})</span>

            @endif
          </td>

          {{-- out time  --}}
          @php($lateCheckOut = $workShift[1])
          @php($dbtimestamp = strtotime($lateCheckOut))

          @php($checkOut = strtotime($user->check_out))
          @php($abcd = date('H:i:s', $checkOut))
          @php($defg = strtotime($abcd))
          @if ($defg - $dbtimestamp < 15*60)

          <td style="color: red;">
            {{ date('H:i A', strtotime($user->check_out)) }}
          </td>

          @else
          <td style="color: green;">
            {{ date('H:i A', strtotime($user->check_out)) }}
          </td>

          @endif

          <td>
            <span>({{ $user->user_ip_checkout }})</span>
            @if (App\Models\Admin_user::illegalLlegalEntry($user->user_ip_checkout)=='Illegal')
            <span style="color: red;">({{ App\Models\Admin_user::illegalLlegalEntry($user->user_ip_checkout) }})</span>
            @else
            <span style="color: green;">({{ App\Models\Admin_user::illegalLlegalEntry($user->user_ip_checkout) }})</span>

            @endif
          </td>

          {{-- working houre  --}}
          @if ($user->check_out == '0000-00-00 00:00:00')
          <td>0H 0M</td>
          @else
          <td>{{ floor($total_time/60) }}H {{ $total_time%60 }}M</td>
          @endif

          {{-- over time  --}}

          @if ( $total_time>0 && !($user->check_out == '0000-00-00 00:00:00'))
          @if ( floor($total_time/60) >=9 )
          <td>{{ floor($total_time/60)-9 }}H {{ $total_time%60}}M</td>

          @elseif( floor($total_time/60) ==8 )
          <td>-0H {{ 60-$total_time%60}}M</td>
          @elseif( floor($total_time/60) ==7 )
          <td>-1H {{ 60-$total_time%60}}M</td>
          @elseif( floor($total_time/60) ==6 )
          <td>-2H {{ 60-$total_time%60}}M</td>
          @elseif( floor($total_time/60) ==5 )
          <td>-3H {{ 60-$total_time%60}}M</td>
          @elseif( floor($total_time/60) ==4 )
          <td>-4H {{ 60-$total_time%60}}M</td>
          @elseif( floor($total_time/60) ==3 )
          <td>-5H {{ 60-$total_time%60}}M</td>

          @else
          <td>Too Late</td>

          @endif
          @else
          <td>0H 0M</td>
          @endif

          {{-- status  --}}
          @if (floor($total_time/60) < 9)
          <td style="color: red;">Incomplete</td>
          @elseif ($user->check_out == '0000-00-00 00:00:00')
          <td style="color: red;">Incomplete</td>
          @else
          <td style="color: green;">Complete</td>

          @endif

      </tr>
    @endforeach
    </tbody>
  </table>


</body>
</html>
