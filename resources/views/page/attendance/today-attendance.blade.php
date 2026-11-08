@extends('layouts.master')
@section('content')
@section('title', 'Manage Attendance')

@section('human_resource_class','active')
@section('attendence_class','active')
@section('today_attendance_class','active')

@php use Carbon\Carbon; @endphp
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-body">
                <table class="datatable table table-striped table-bordered table-hover">
                    <caption>Today Attendance List</caption>
                    <thead>
                        <tr>
                            <th>SL.</th>
                            <th>Emp Id</th>
                            <th>Mobile</th>
                            <th>Name</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Check In/Out IP</th>
                            <th>Stay Time</th>
                            <th>Over Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $comWorkShift = \App\Models\Admin_user::where('au_company_id',Auth::user()->au_company_id)->first();
                            $workshift = explode(',', $comWorkShift->au_work_shift);
                            $late = 0;
                            $over = 0;
                        @endphp

                        @if ($usersAttendance == FALSE)
                            <tr><td colspan="7" class="text-center">There are currently No Attendance</td></tr>
                        @else
                            @php $sl = 1; @endphp
                            @foreach ($usersAttendance as $row)
                                <tr class="{{ ($sl & 1) ? 'odd gradeX' : 'even gradeC' }}">
                                    {{-- SL. --}}
                                    <td>{{ $sl }}</td>
                                    {{-- Emp Id --}}
                                    @foreach ($userComId as $employeId)
                                        @if ($row->user_id == $employeId->au_id)
                                            <td>{{ $employeId->au_user_id }}</td>
                                        @endif
                                    @endforeach
                                    {{-- Mobile --}}
                                    <td>{{ $row->au_mobile }}</td>
                                    {{-- Name --}}
                                    <td>{{ App\Models\Admin_user::attendanceUser($row->user_id) }}</td>

                                    {{-- Check In --}}
                                    @php
                                        $in_time = date('H:i', strtotime($row->check_in));
                                        $check_out = date('H:i', strtotime($row->check_out));
                                        $date1 = Carbon::parse($in_time);
                                        $date2 = Carbon::parse($check_out);
                                        $total_time = $date1->diffInMinutes($date2);

                                        $au_user = \App\Models\Admin_user::where('au_id', $row->user_id)->first();

                                        if ($au_user->au_shifts != null) {
                                            $auWorkshift = \App\Models\PayrollShiftManagement::where('id', $au_user->au_shifts)->first();
                                            $workShift = explode(',', $auWorkshift->working_hour);
                                        } else {
                                            $workShift = explode(',', $comWorkShift->au_work_shift);
                                        }

                                        $lateCheckIn = $workShift[0];
                                        $dbtimestamp = strtotime($lateCheckIn);
                                        $checkIn = strtotime($row->check_in);
                                        $abcd = date('H:i:s', $checkIn);
                                        $defg = strtotime($abcd);
                                    @endphp

                                    @if ($defg - $dbtimestamp > $checkVal * 60)
                                        <td style="color: red;">
                                            {{ date('H:i A', strtotime($row->check_in)) }}
                                        </td>
                                    @else
                                        <td style="color: green;">
                                            {{ date('H:i A', strtotime($row->check_in)) }}
                                        </td>
                                    @endif

                                    {{-- Check Out --}}
                                    @php
                                        $lateCheckOut = $workShift[1] ?? null; // safely access index 1 or assign null
                                       $dbtimestamp = strtotime($lateCheckOut);
                                    
                                        $checkOut = strtotime($row->check_out);
                                        $abcd = $checkOut ? date('H:i:s', $checkOut) : 'N/A';
                                    @endphp
                                
                                    @if ($abcd == "00:00:00")
                                        <td style="color: rgb(25, 14, 179);">No Checkout</td>
                                    @elseif($defg - $dbtimestamp < 15*60)
                                        <td style="color: red;">
                                            {{ date('H:i A', strtotime($row->check_out)) }}
                                        </td>
                                    @else
                                        <td style="color: green;">{{ date('H:i A', strtotime($row->check_out)) }}</td>
                                    @endif

                                    {{-- Check In/Out Ip --}}
                                    <td class="center">
                                        {{-- checkin ip --}}
                                        <span>({{ $row->user_ip }})</span>
                                        @php
                                            $ipEntry = App\Models\Admin_user::illegalLlegalEntry($row->user_ip);
                                            $ipColor = ($ipEntry === 'Illegal') ? 'red' : 'green';
                                        @endphp
                                        <span style="color: {{ $ipColor }};">({{ $ipEntry }})</span>

                                        <br>
                                        {{-- checkout ip --}}
                                        @if ($row->user_ip_checkout !== null)
                                            @php
                                                $userIpCheckout = $row->user_ip_checkout;
                                                $ipEntry = App\Models\Admin_user::illegalLlegalEntry($userIpCheckout);
                                            @endphp

                                            <span style="color: {{ ($ipEntry == 'Illegal') ? 'red' : 'green' }}">
                                                ({{ $userIpCheckout }}) ({{ $ipEntry }})
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Stay Time --}}
                                    @if ($row->check_out == '0000-00-00 00:00:00')
                                        <td>On Going</td>
                                    @else
                                        @php
                                            $hours = floor($total_time / 60);
                                            $minutes = $total_time % 60;
                                            $formatted_time = $hours . 'H ' . $minutes . 'M';
                                        @endphp
                                        @if ($hours < 9 || ($hours == 9 && $minutes < 0))
                                            <td style="color: red;">{{ $formatted_time }}</td>
                                        @else
                                            <td style="color: green;">{{ $formatted_time }}</td>
                                        @endif
                                    @endif

                                    {{-- Over Time --}}
                                    @if ($total_time > 0 && !($row->check_out == '0000-00-00 00:00:00'))
                                        @if ($hours >= 9)
                                            <td style="color: green;">{{ $hours - 9 }}H {{ $minutes }}M</td>
                                        @elseif ($hours >= 3)
                                            <td style="color: red;">-{{ 9 - $hours }}H {{ 60 - $minutes }}M</td>
                                        @else
                                            <td style="color: red;">Too Late</td>
                                        @endif
                                    @else
                                        <td>On Going</td>
                                    @endif

                                    {{-- Status --}}
                                    @if (floor($total_time/60) < 9)
                                        <td style="color: red;">Incomplete</td>
                                    @elseif ($row->check_out == '0000-00-00 00:00:00')
                                        <td style="color: red;">Incomplete</td>
                                    @else
                                        <td style="color: green;">Complete</td>
                                    @endif
                                </tr>
                                @php $sl++; @endphp
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
