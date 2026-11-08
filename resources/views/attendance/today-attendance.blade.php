@php use Carbon\Carbon; @endphp
@extends('layout.master')
@section('payroll_class','menu-open')
@section('today_attendance_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            @if(session()->has('del'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('del') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session()->has('update'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('update') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <h1 style="float: left">
                Today Attendance List
            </h1>
            <a href="{{ route('payroll.today-attendance-print') }}" class="btn btn-primary" style="margin-left: 90%">Print</a>
        </section>
        <div class="box">
            <div class="box-header">
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="myTable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Emp Id</th>
                        <th>Phone Number</th>
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
                            <td>{{ $user->au_mobile }}</td>
                            <td>{{ App\Models\Admin_user::attendanceUser($user->user_id) }}</td>

                            @php($in_time = date('H:i', strtotime($user->check_in)))
                            @php($check_out = date('H:i', strtotime($user->check_out)))

                            @php($date1 = Carbon::parse($in_time))
                            @php($date2 = Carbon::parse($check_out))
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

                            @if ( $defg - $dbtimestamp > $checkVal*60 )
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
                                @if (App\Models\Admin_user::illegalLlegalEntry($user->user_ip)==='Illegal')
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
            </div>
            <!-- /.box-body -->
        </div>
    </section>

    <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="userData">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('custom_style')
    <style>
        .action_btn {
            border: 1px solid;
            padding: 5px;
        }

        .btn_show {
            background-color: green;
            color: white;
        }

        .btn_delete {
            background-color: red;
            color: white;
        }

        .btn_edit {
            background-color: cornflowerblue;
            color: white;
        }
    </style>
@endsection
@section('custom_script')
    <script type="text/javascript">
        
        $('#myTable').DataTable({
        order: [[1, 'asc']],
        paging: true,
        pageLength: 10,
        rowCallback: function(row, data, index) {
            var page = $('#myTable').DataTable().page();
            var length = $('#myTable').DataTable().page.len();
            var rowNumber = index + 1 + (page * length);
            $('td:eq(0)', row).html(rowNumber);
        },
        responsive: false
    });


        function deletesup() {
            let clickDel = confirm("Are you sure want to delete this?");
            if (clickDel == true) {
                return true;
            } else {
                return false;
            }
        }

        function userAttendance(userid, year, month) {
            // console.log(year);

            $.ajax({
                type: "GET",
                url: "{{ route('payroll.attendance-ajax') }}",
                data: {userid: userid, year: year, month: month},
                success: function (result) {
                    $('#userData').html(result);
                    $('#attendanceModal').modal('show');
                }
            })
        }

        function userAttendanceDetailsPrint(value) {
            let month = $("#month").val();
            let year = $("#year").val();
            let user = value;
            // alert(user);
            let route = "{!! route('payroll.attendance-print') !!}?month=" + month + "&year=" + year + "&user=" + user;
            window.open(route, '_blank');
        }
    </script>
@endsection
