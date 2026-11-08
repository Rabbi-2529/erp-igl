@extends('layouts.master')
@section('title', 'Attendance Report')

@section('human_resource_class','active')
@section('attendence_class','active')
@section('attendence_report_class','active')

@section('content')

<style>
  .action_btn{
      border: 1px solid;
      padding: 5px;
  }
  .btn_show{
      background-color: green;
      color: white;
  }
  .btn_delete{
      background-color: red;
      color: white;
  }
  .btn_edit{
      background-color: cornflowerblue;
      color: white;
  }
  @media print {
      .print-hidden {
          display: none;
      }
  }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Total Attendance Report</h4>
                </div>
            </div>

            <div class="panel-body">

                @if(session()->has('del'))
                    <!-- Alert for deletion -->
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('del') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('update'))
                    <!-- Alert for update -->
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('update') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="box">
                    {{-- Month Year Selction --}}
                    <form action="" method="get">
                        <div class="row print-hidden">
                            <div class="col-xs-3">
                                <!-- Year dropdown -->
                                <select class="form-control" name="year" id="year">
                                  @for( $this_year = date('Y');$this_year>=2007; $this_year--)
                                    <option value="{{ $this_year }}" {{ (isset($request_year)? (($request_year == $this_year)? 'selected':''):'') }}>{{ $this_year }}</option>
                                  @endfor
                                </select>
                            </div>
                            
                            <div class="col-xs-3">
                                <!-- Month dropdown -->
                                <select class="form-control" name="month" id="month">
                                  <option value="01" {{ (isset($request_month)? (($request_month == 1)? 'selected':''):'') }}>January</option>
                                  <option value="02" {{ (isset($request_month)? (($request_month == 2)? 'selected':''):'') }}>February</option>
                                  <option value="03" {{ (isset($request_month)? (($request_month == 3)? 'selected':''):'') }}>March</option>
                                  <option value="04" {{ (isset($request_month)? (($request_month == 4)? 'selected':''):'') }}>April</option>
                                  <option value="05" {{ (isset($request_month)? (($request_month == 5)? 'selected':''):'') }}>May</option>
                                  <option value="06" {{ (isset($request_month)? (($request_month == 6)? 'selected':''):'') }}>June</option>
                                  <option value="07" {{ (isset($request_month)? (($request_month == 7)? 'selected':''):'') }}>July</option>
                                  <option value="08" {{ (isset($request_month)? (($request_month == 8)? 'selected':''):'') }}>August</option>
                                  <option value="09" {{ (isset($request_month)? (($request_month == 9)? 'selected':''):'') }}>September</option>
                                  <option value="10" {{ (isset($request_month)? (($request_month == 10)? 'selected':''):'') }}>October</option>
                                  <option value="11" {{ (isset($request_month)? (($request_month == 11)? 'selected':''):'') }}>November</option>
                                  <option value="12" {{ (isset($request_month)? (($request_month == 12)? 'selected':''):'') }}>December</option>
                                </select>
                            </div>
                            <div class="col-xs-6">
                                <!-- Search button -->
                                <button type="submit" name="searchbtn" class="btn btn-info">Search</button>
                              
                                <button class="btn btn-success pull-right" onclick="printAttendanceList()">Print</button>
                            </div>
                        </div>
                    </form>

                    <div class="box-header">
                        <h3 class="box-title text-center">Attendance Report - <span id="selectedMonthYear"></span></h3>
                    </div>

                    <div class="box-body">

                        <!-- Attendance List table START-->
                        <table id="attendanceTable" class="table table-striped table-bordered table-hover">
                          <thead>
                            <tr>
                                <th class="print-hidden">SL</th>
                                <th>Emp Id</th>
                                <th>User Name</th>
                                <th>Total Late</th>
                                <th>Late Cut</th>
                                <th>Late Details</th>
                                <th>Absent</th>
                                <th>Total Cut</th>
                                <th>Paid Leave</th>
                                <th>Final Cut</th>
                                <th class="text-center print-hidden">Action</th>
                            </tr>
                          </thead>

                          <tbody>

                            @php
                                $comWorkShift = \App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)->first();
                                $workshift = explode(',', $comWorkShift->au_work_shift);
                                $sl = 0;
                                $over = 0;
                                $present = 0;
                                $absent = 0;
                                $late = 0;
                                $lateCut = [];
                            @endphp

                            @foreach ($usersAttendance as $user)
                              @php
                                  $lateCheckInsForUser = $lateCheckIns->where('user_id', $user->user_id);
                                  $lateCount = $lateCheckInsForUser->count();
                                  $late += $lateCount;

                                  $user_id = $user->user_id;
                                  
                                  $lateCut[$user_id] = [
                                      '15_minutes_late' => 0,
                                      '30_minutes_late' => 0,
                                      '1_hour_late' => 0,
                                       'in_time' => 0,  
                                      'total_late_cut' => 0,
                                  ];
                              @endphp

                              <tr>
                                  {{-- Serial --}}
                                  <td class="print-hidden">{{ ++$sl }}</td>

                                  {{-- Emp Id --}}
                                  @foreach ($userComId as $employeId)
                                    @if ($user->user_id == $employeId->au_id)
                                      <td>{{ $employeId->au_user_id }}</td>
                                    @endif
                                  @endforeach

                                  {{-- User Name --}}
                                  <td>{{ @App\Models\Admin_user::attendanceUser($user->user_id) }}</td>

                                  {{-- Total Late Count--}}
                                  <td>
                                    <span style="color: {{ $lateCount == 0 ? 'green' : 'red' }}">
                                        {{ $lateCount }}
                                    </span>
                                    Day.
                                  </td>

                                  {{-- Late Cut --}}
                                  <td>
                                    @foreach ($lateCheckInsForUser as $latecheck)
                                        @php
                                            $checkInTime = strtotime(\Carbon\Carbon::parse($latecheck->check_in)->format('H:i:s'));
                                
                                            if ($checkInTime > strtotime('10:00:00')) {
                                                $lateCut[$user_id]['1_hour_late'] += 1;
                                                $lateCut[$user_id]['total_late_cut'] += 1;
                                            } elseif ($checkInTime > strtotime('09:30:00')) {
                                                $lateCut[$user_id]['30_minutes_late'] += 1;
                                                $lateCut[$user_id]['total_late_cut'] += 0.50;
                                            } elseif ($checkInTime > strtotime('09:15:00')) {
                                                $lateCut[$user_id]['15_minutes_late'] += 1;
                                                $lateCut[$user_id]['total_late_cut'] += 0.33;
                                            } elseif ($checkInTime < strtotime(datetime: '09:00:00')) {
                                                $lateCut[$user_id]['in_time'] += 1;
                                                $lateCut[$user_id]['total_late_cut'] += 0;
                                            }
                                        @endphp
                                    @endforeach
                                
                                    <span style="color: {{ $lateCount == 0 ? 'green' : 'red' }};">
                                        {{
                                            // Round up if the decimal part is greater than or equal to 0.8, otherwise, round down
                                            ($lateCut[$user_id]['total_late_cut'] - floor($lateCut[$user_id]['total_late_cut']) >= 0.8) ? ceil($lateCut[$user_id]['total_late_cut']) : floor($lateCut[$user_id]['total_late_cut'])
                                        }}
                                    </span>
                                    Day.
                                  </td>                              

                                  {{-- Late Details --}}
                                  <td>
                                    15MIN:
                                    <span style="color: {{ $lateCut[$user_id]['15_minutes_late'] == 0 ? 'green' : 'red' }};">
                                      {{ $lateCut[$user_id]['15_minutes_late'] }}
                                    </span> / 
                                    30MIN:
                                    <span style="color: {{ $lateCut[$user_id]['30_minutes_late'] == 0 ? 'green' : 'red' }};">
                                      {{ $lateCut[$user_id]['30_minutes_late'] }}
                                    </span> / 
                                    1H:
                                    <span style="color: {{ $lateCut[$user_id]['1_hour_late'] == 0 ? 'green' : 'red' }};">
                                      {{ $lateCut[$user_id]['1_hour_late'] }}
                                    </span>
                                  </td>
                                    @php
                                        $workingDays = $days_in_month - $totalWeekends - $totalHolidays;
                                       
                                        $checkedAttendence = $user->check_in;
                                        $absent = $workingDays - $checkedAttendence;
                                        $dayCut = $absent + $lateCut[$user_id]['total_late_cut'];
                                        $leaveData = $monthLeave->where('user_id', $user->user_id)->first();
                                        $totalDaysAccepted = $leaveData ? $leaveData->total_day_accepted : 0;
                                        $finalCut = $dayCut - $totalDaysAccepted;
                                    @endphp

                                  {{-- Absent --}}
                                  <td>
                                    <span style="color: {{ $absent == 0 ? 'green' : 'red' }};">
                                    {{ $absent }}
                                    </span>
                                    Days.
                                  </td>

                                  {{-- Total Cut --}}
                                  <td>
                                    <span style="color: {{ $dayCut == 0 ? 'green' : 'red' }};">
                                        {{
                                            // Round up if the decimal part is greater than or equal to 0.8, otherwise, round down
                                            ($dayCut - floor($dayCut) >= 0.8) ? ceil($dayCut) : floor($dayCut)
                                        }}
                                    </span>
                                    Days.
                                  </td>


                                  {{-- Paid Leave --}}
                                  <td>
                                    <a href="#" class="showLeaveModal" data-toggle="modal" data-target="#leaveModal{{ $user->user_id }}">
                                        <span style="color: {{ $totalDaysAccepted == 0 ? 'red' : 'green' }}">
                                            {{ $totalDaysAccepted }} Days.
                                        </span>
                                    </a>
                                  </td>

                                  {{-- Paid Leave Modal START --}}
                                    <div class="modal fade" id="leaveModal{{ $user->user_id }}" tabindex="-1" role="dialog" aria-labelledby="leaveModalLabel{{ $user->user_id }}" aria-hidden="true">
                                      <div class="modal-dialog modal-lg" role="document">
                                          <div class="modal-content">
                                              <div class="modal-header">
                                                  <h5 class="modal-title" id="leaveModalLabel{{ $user->user_id }}">
                                                      @foreach ($userComId as $employeId)
                                                        @if ($user->user_id == $employeId->au_id)
                                                          <p>{{ $employeId->au_user_id }}</p>
                                                        @endif
                                                      @endforeach
                                                      <p>{{ @App\Models\Admin_user::attendanceUser($user->user_id) }}</p>
                                                  </h5>
                                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                  </button>
                                              </div>
                                              <div class="modal-body">
                                                @php
                                                    $userLeaveData = $monthLeave->where('user_id', $user->user_id)->first();
                                                @endphp
                                            
                                                @if ($userLeaveData)
                                                    <div class="leave-info">
                                                      
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>Requested Leave:</strong>
                                                                {{ $userLeaveData->leave_date }}
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Accepted Leave:</strong>
                                                                {{ $userLeaveData->leave_date_accept_start }} To {{ $userLeaveData->leave_date_accept_end }}
                                                            </div>
                                                        </div>
                                            
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <strong>Reason:</strong>
                                                                {{ $userLeaveData->user_desc }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p>No leave data available for this user.</p>
                                                @endif
                                            </div>
                                            
                                          </div>
                                      </div>
                                    </div>
                                    @push('scripts')
                                      <script>
                                          $(document).ready(function () {
                                              $('.showLeaveModal').click(function () {
                                                  var targetModal = $(this).data('target');
                                                  $(targetModal).modal('show');
                                              });
                                          });
                                      </script>
                                    @endpush
                                  {{-- Paid Leave Modal END --}}

                                  {{-- Final Cut --}}
                                  <td>
                                    <span style="font-weight:bold; color: {{ $finalCut == 0 ? 'green' : 'red' }};">
                                        {{
                                            // Round up if the decimal part is greater than or equal to 0.8, otherwise, round down
                                            ($finalCut - floor($finalCut) >= 0.8) ? ceil($finalCut) : floor($finalCut)
                                        }}
                                    </span>
                                    Days.
                                  </td>

                                  {{-- Action --}}
                                  <td align="center" class="print-hidden">
                                    <button class="button btn-sm btn-info" onclick="userAttendance('{{ $user->user_id }}','{{ $request_year }}',{{ $request_month }})"><i class="fa fa-eye"></i></button>
                                    
                                    <button class="button btn-sm btn-info" onclick="userAttendanceDetailsPrint('{{ $user->user_id }}')"><i class="fa fa-download"></i></button>
                                  </td>

                              </tr> 
                            @endforeach
                          </tbody>
                        </table>
                        <!-- Attendance List table END-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Month Report Modal START -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="modal-title" id="attendanceModalLabel">Attendance History</h5>
                    </div>
                </div>
                <div class="row mt-2" id="employeeInfoHeader">
                    <div class="col-md-6">
                        <strong>Employee ID:</strong> <span id="modalEmployeeId">Loading...</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Employee Name:</strong> <span id="modalEmployeeName">Loading...</span>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>  
            <div class="modal-body" id="userData">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading attendance data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printModalContent()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Month Report Modal END -->


<script>
    updateSelectedMonthYear();
  
    $('#showLeaveModal').click(function () {
        $('#leaveModal{{ $user->user_id }}').modal('show');
    });

    $('form').submit(function() {
        updateSelectedMonthYear();
    });

    function deletesup(){
      let clickDel = confirm("Are you sure want to delete this?");
      if (clickDel == true) {
        return true;
      }else{
        return false;
      }  
    }

 function userAttendance(userid, year, month) {
    // Show loading state
    $('#userData').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading attendance data...</p></div>');
    $('#modalEmployeeId').text('Loading...');
    $('#modalEmployeeName').text('Loading...');
    $('#attendanceModal').modal('show');
    
    // Get employee details first
    $.ajax({
        type: "GET",
        url: "{{ route('payroll.get-employee-details') }}", // You'll need to create this route
        data: {userid: userid},
        success: function(employee) {
            $('#modalEmployeeId').text(employee.au_user_id || 'N/A');
            $('#modalEmployeeName').text(employee.au_name || 'N/A');
        },
        error: function() {
            $('#modalEmployeeId').text('N/A');
            $('#modalEmployeeName').text('N/A');
        }
    });
    
    // Get attendance data
    $.ajax({
        type: "GET",
        url: "{{ route('payroll.attendance-ajax') }}",
        data: {userid: userid, year: year, month: month},
        success: function(result) {
            $('#userData').html(result);
        },
        error: function(xhr, status, error) {
            $('#userData').html('<div class="alert alert-danger">Error loading attendance data. Please try again.</div>');
        }
    });
}

    function updateSelectedMonthYear() {
        var selectedMonth = $("#month option:selected").text();
        var selectedYear = $("#year option:selected").text();
        $("#selectedMonthYear").text(selectedMonth + ' ' + selectedYear);
    }

    function userAttendanceDetailsPrint(value) {
        let month = $("#month").val();
        let year = $("#year").val();
        let user = value;
        let route = "{!! route('payroll.attendance-print') !!}?month="+month+"&year="+year+"&user="+user;
        window.open(route, '_blank');
    }

    function printAttendanceList() {
      window.print();
    }
</script>

<script>
  $(document).ready(function() {
      $('#attendanceTable').DataTable({
          "order": [[1, 'asc']],
          "paging": true
      });
  });
</script>

@endsection
