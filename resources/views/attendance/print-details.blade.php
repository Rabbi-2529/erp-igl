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

		@page { margin: 0px 50px; }

	 /*   #header { */
		/*	position: fixed; */
		/*	left: 0px; */
		/*	top: -120px; */
		/*	right: 0px; */
		/*	height: 150px; */
		/*    text-align: center; */
		/*}*/
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
	<!--<div id="header">-->
	<!--		<div class="logo">-->
	<!--			<img src="{{asset('assets/uploads/default.png')}}" alt="">-->
	<!--		</div>-->
	<!--		<div id="footer">-->
	<!--			<p style="font-size: 20px;">We Are Authorized Aggregator of: -->
	<!--				<img src="" style="width: 20px; height: 20px; margin-right: 5px;">-->
	<!--				<img src="" style="width: 20px; height: 20px; margin-right: 5px;">-->
	<!--				<img src="" style="width: 20px; height: 20px; margin-right: 5px;">-->
	<!--				<img src="" style="width: 20px; height: 20px; margin-right: 5px;">-->
	<!--				<img src="" style="width: 20px; height: 20px;">-->
	<!--			</p>-->
	<!--		</div>-->
	<!--</div>-->
	@php($monthNum  = (int)$request_month)
	@php($dateObj   = DateTime::createFromFormat('!m', $monthNum))
	@php($monthName = $dateObj->format('F'))

	{{-- @dd($request_month) --}}
	<h3 style="text-align: center; margin-right: 300px;">Attendance Details of {{ App\Models\Admin_user::attendanceUser($uid) }} For the month of {{ $monthName }}</h3>
	<table>
		<thead>
			<tr>
			<th style="width: 50px;">DATE</th>
			<th style="width: 81px;">CHECK IN</th>
			<th style="width: 50px;">LATE</th>
			<th style="width: 145px;">CHECK OUT</th>
			<th style="">TOTAL WORK</th>
			<th style="">STATUS</th>
			</tr>
		</thead>


		<tbody>
			@for($j=1; $j<=$days_in_month; $j++)
			@php($show_date = $request_year.'-'.$request_month.'-'.((strlen($j < 2)) ? "0".$j : $j))
			@php($day = Carbon\Carbon::make($request_year.'-'.$request_month.'-'.((strlen($j < 2)) ? "0".$j : $j))->format('l'))
			@php($checkIn = App\Models\EmployeeAttendance::checkInByUser($uid,$show_date))
			@php($checkOut =  App\Models\EmployeeAttendance::checkOutByUser($uid,$show_date) )
			@php($isHoliday = in_array($j, $holidays));
            <tr>
                <td style="{{ $day == "Friday" ? 'background-color:red;': '' }}text-align: left;"><b>{{ $j }}</b></td>
                <td style="{{ $day == "Friday" ? 'background-color:red;': '' }}text-align: left;">
					@php($workShift = explode(',',$comDetails->au_work_shift))
                    @php($lateCheckIn = $workShift[0])
                    @php($dbtimestamp = strtotime($lateCheckIn))

					@php($checkInTime = \Carbon\Carbon::parse($checkIn)->format('H:i:s'))
                    @php($defg = strtotime($checkInTime))

					@if ($defg - $dbtimestamp > 15*60)
							{{ $checkIn }}
					@else
							{{ $checkIn }}
					@endif

				</td>
				<td style="{{ $day == "Friday" ? 'background-color:red;': '' }}text-align: left;">
					@php($workShift = explode(',',$comDetails->au_work_shift))
                    @php($lateCheckInTime = $workShift[0])
					@if ($checkIn == 0 || $defg - $dbtimestamp < 15*60)
						{{ 0 }}
					@else
						@php($date3 = \Carbon\Carbon::parse($lateCheckInTime))
						@php($date4 = \Carbon\Carbon::parse($checkIn))
						@php($difference = $date3->diffInMinutes($date4))
					@if ($difference > 60)
						{{ floor($difference/60) }}H {{ ($difference%60) }}M
					@else
						{{ ($difference%60) }}M
					@endif

					@endif




				</td>
                <td style="{{ $day == "Friday" ? 'background-color:red;': '' }}text-align: left;">
					@php($date1 = \Carbon\Carbon::parse($checkIn))
					@php($date2 = \Carbon\Carbon::parse($checkOut))
					@php($difference = $date1->diffInMinutes($date2))

					@if ($difference >= $comDetails->au_working_hour)
						{{ $checkOut }}
					@elseif($difference < $comDetails->au_working_hour)
						{{ $checkOut }}
					@endif
					{{-- {{ $checkOut }} --}}
				</td>
				<td style="{{ $day == "Friday" ? 'background-color:red;': '' }}text-align: center;">
					@php($date5 = \Carbon\Carbon::parse($checkIn))
					@php($date6 = \Carbon\Carbon::parse($checkOut))
					@php($difference = $date5->diffInMinutes($date6))
					@if ($checkOut == 0)
						{{ 0 }}
					@else
					{{ floor($difference/60) }}H {{ ($difference%60) }}M
					@endif


				</td>
				<td style="{{ $day == 'Friday' || $isHoliday ? 'background-color:red;' : '' }} text-align: left;">
    @if($isHoliday)
        <b style="color: black;">Holiday</b>
    @else
        @php($workShift = explode(',', $comDetails->au_work_shift))
        @php($lateCheckIn = $workShift[0])
        @php($dbtimestamp = strtotime($lateCheckIn))
        @php($checkInTime = \Carbon\Carbon::parse($checkIn)->format('H:i:s'))
        @php($defg = strtotime($checkInTime))
        @php($date1 = \Carbon\Carbon::parse($checkIn))
        @php($date2 = \Carbon\Carbon::parse($checkOut))
        @php($difference = $date1->diffInMinutes($date2))

        @if ($defg - $dbtimestamp > 15*60 && $difference < $comDetails->au_working_hour)
            Late and Incomplete
        @elseif($checkIn == 0 && $checkOut == 0)
            <b style="color: red;">Absent</b>
        @elseif($defg - $dbtimestamp < 15*60 && $checkOut == 0)
            Ok but No Check Out
        @elseif($defg - $dbtimestamp > 15*60 && $checkOut == 0)
            Late and No Check Out
        @elseif($defg - $dbtimestamp < 15*60 && $difference >= $comDetails->au_working_hour)
            Ok and Complete
        @elseif($defg - $dbtimestamp < 15*60 && $difference < $comDetails->au_working_hour)
            Ok but Incomplete
        @else
            <b style="color: green;">All Complete</b>
        @endif
    @endif
</td>
            </tr>
        @endfor

			{{-- @php($total = 0)
			@foreach( $transactions as $date => $data ) --}}
            {{-- @foreach ($usersAttendance as $item)
                <tr>
                    <td style="text-align: center; font-size: 13px;">{{ $loop->iteration }}</td>
                    <td style="text-align: center; font-size: 13px;">{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                    <td style="text-align: center; font-size: 13px;">
                        @php($workShift = explode(',',$comDetails->au_work_shift))
                        @php($lateCheckIn = $workShift[0])
                        @php($dbtimestamp = strtotime($lateCheckIn))

                        @php($checkIn = \Carbon\Carbon::parse($item->check_in)->format('H:i:s'))
                        @php($defg = strtotime($checkIn))
                        @if ($defg - $dbtimestamp > 15*60)
                                {{ \Carbon\Carbon::parse($item->check_in)->format('H:i:s') }}(Late)
                        @else
                                {{ \Carbon\Carbon::parse($item->check_in)->format('H:i:s') }}
                        @endif

                    </td>
                    @php($date1 = \Carbon\Carbon::parse($item->check_in))
                    @php($date2 = \Carbon\Carbon::parse($item->check_out))
                    @php($difference = $date1->diffInMinutes($date2))
                    <td style="text-align: center; font-size: 13px;">
                        @if ($difference >= $comDetails->au_working_hour)
                            {{ \Carbon\Carbon::parse($item->check_out)->format('H:i:s') }}
                        @elseif($difference < $comDetails->au_working_hour)
                            {{ \Carbon\Carbon::parse($item->check_out)->format('H:i:s') }}(incomplete)
                        @endif

                    </td>
                </tr>
            @endforeach --}}


		</tbody>





	</table>


</body>
</html>
