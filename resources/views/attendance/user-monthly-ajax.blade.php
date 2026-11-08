<table id="dynamic-table1" class="table table-bordered table-striped">
    <thead>
    <tr>
        @for($i = 1; $i <= 7; $i++)
            <th>{{ Carbon\Carbon::make($request_year.'-'.$request_month.'-'.$i)->format('l') }}</th>
        @endfor
    </tr>
    </thead>
    <tbody>
    @php($total_entry = 0)
    @php($ok_complete = 0)
    @php( $total = 0 )
    @php($total_entry_admin = 0)
    @php($absent_count = 0)
    @php($all_complete = 0)
    <tr>
        @for($j=1; $j<=$days_in_month; $j++)
            @php($show_date = $request_year.'-'.$request_month.'-'.((strlen($j < 2)) ? "0".$j : $j))
            <td class="text-center"><button type="button" class="btn btn-primary btn-sm">
                {{ $j }} <br>
                @php($checkIn = App\Models\EmployeeAttendance::checkInByUser($user,$show_date))</button><br>
                @php( $checkOut =  App\Models\EmployeeAttendance::checkOutByUser($user,$show_date) )
                
                {{ $checkIn }}/{{ $checkOut }}
                @php($workShift = explode(',',$comDetails->au_work_shift))
                @php($lateCheckIn = $workShift[0])
                @php($dbtimestamp = strtotime($lateCheckIn))

                @php($checkInTime = strtotime($checkIn))
                @php($abcd = date('H:i:s', $checkInTime))
                @php($defg = strtotime($abcd))

                @php($date1 = \Carbon\Carbon::parse($checkIn))
                @php($date2 = \Carbon\Carbon::parse($checkOut))
                @php($difference = $date1->diffInMinutes($date2))

                @if ($defg - $dbtimestamp > 15*60 && $difference < $comDetails->au_working_hour)
                    <p class="text-danger">Late / Incomplete</p>

                @elseif($checkIn == 0 && $checkOut == 0 && Carbon\Carbon::make($request_year.'-'.$request_month.'-'.$j)->format('l') == 'Friday')
                    <p class="text-success">Weekend</p>
                @elseif($checkIn == 0 && $checkOut == 0 )
                    @php($absent_count++)
                    <p class="text-danger">Absent</p>
                @elseif($defg - $dbtimestamp < 15*60 && $checkOut == 0)
                   <p class="text-success">Ok / <b class="text-danger">No Check Out</b></p>
                @elseif($defg - $dbtimestamp > 15*60 && $checkOut == 0)
                   <p class="text-success">Late/No Check Out</p>
                @elseif($defg - $dbtimestamp < 15*60 && $difference >= $comDetails->au_working_hour)
                    @php($ok_complete++)
                    <p class="text-success">Ok/Complete</p>
                @elseif($defg - $dbtimestamp < 15*60 && $difference < $comDetails->au_working_hour)
                    <p class="text-success">Ok/<b class="text-danger">Incomplete</b></p>
                @else
                @php($all_complete++)
                    <p class="text-success">All Complete</p>
                @endif

            </td>

        @if($j%7 == 0)
    </tr>
    
    <tr>
        @endif
        @endfor
    </tr>
</tbody>
</table>
<h5>Work Hour Complete: <span style="color: green">{{ $all_complete }}</span></h5>
<h5>Time Maintain: <span style="color: green">{{ $ok_complete }}</span></h5>
<h5>Total Working Days: <span style="color: green">{{ $ok_complete+$all_complete }}</span></h5>
<h5>Total Absent: <span style="color: red">{{ $absent_count }}</span></h5>
