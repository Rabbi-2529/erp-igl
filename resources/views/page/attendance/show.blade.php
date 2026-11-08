


@extends('layouts.master')
@section('title', 'My Attendance')

@section('human_resource_class', 'active')
@section('attendence_class', 'active')
@section('my_attendence_report_class', 'active')

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
                    <h4>My Attendance Report</h4>
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
                  {{-- Month Year Selection Form START --}}
                  <form action="" method="get">
                      <div class="row print-hidden">
                          <div class="col-xs-3">
                              <!-- Year dropdown -->
                              <select class="form-control" name="year" id="year">
                                @for( $this_year = date('Y'); $this_year >= 2007; $this_year--)
                                  <option value="{{ $this_year }}" {{ (isset($request_year) && $request_year == $this_year) ? 'selected' : '' }}>{{ $this_year }}</option>
                                @endfor
                              </select>
                          </div>
                          
                          <div class="col-xs-3">
                              <!-- Month dropdown -->
                              <select class="form-control" name="month" id="month">
                                @foreach (range(1, 12) as $monthNum)
                                  <option value="{{ str_pad($monthNum, 2, '0', STR_PAD_LEFT) }}" {{ (isset($request_month) && $request_month == $monthNum) ? 'selected' : '' }}>
                                      {{ date('F', mktime(0, 0, 0, $monthNum, 1)) }}
                                  </option>
                                @endforeach
                              </select>
                          </div>
                          <div class="col-xs-6">
                              <!-- Search button -->
                              <button type="submit" name="searchbtn" class="btn btn-info">Search</button>
                              @if ($userType == 4)
                              <button class="btn btn-success pull-right" onclick="printAttendanceList()">Print</button>
                              @endif
                          </div>
                      </div>
                  </form>
                  {{-- Month Year Selection Form END --}}

                  <div class="box-header">
                      <h3 class="box-title text-center">Attendance Report - <span id="selectedMonthYear"></span></h3>
                  </div>

                  <div class="box-body">
                      <table id="attendanceTable" class="table table-striped table-bordered table-hover">
                        <thead>
                          <tr>
                              <th style="width: 20px;" class="text-center">SL</th>
                              <th class="text-center">Date</th>
                              <th style="width: 100px;" class="text-center">Check In</th>
                              <th class="text-center">Late | Ontime</th>
                              <th style="width: 200px;">In IP | Reason</th>
                              <th class="text-center">Check Out</th>
                              <th>Out IP</th>
                              <th class="text-center">Status</th>
                          </tr>
                        </thead>

                        <tbody>
                          @php
                              $sl = 0;
                          @endphp
                        
                          @foreach ($usersAttendance as $user)
                          <tr>
                                <td class="text-center">{{ ++$sl }}</td>

                                @php
                                    $lateCut = [];
                                    $workShift = explode(',',$branchDetails->au_work_shift);
                                    $lateCheckIn = $workShift[0];
                                    $dbtimestamp = strtotime($lateCheckIn);
                                    $checkIn = strtotime($user->check_in);
                                    $abcd = date('H:i:s', $checkIn);
                                    $defg = strtotime($abcd);
                                    $checkInDate = date('d-m-Y', strtotime($user->check_in));

                                    // Convert check_in time string to a PHP timestamp
                                    $checkInTime = strtotime($user->check_in);
                                    // Convert check_out time string to a PHP timestamp
                                    $checkOutTime = strtotime($user->check_out);
                                      
                                    // Convert the timestamp to the desired time format (HH:MM:SS)
                                    $formattedTimeIn = date('H:i:s', $checkInTime);
                                    $formattedTimeOut = date('H:i:s', $checkOutTime);

                                    $date1 = \Carbon\Carbon::parse($user->check_in);
                                    $date2 = \Carbon\Carbon::parse($user->check_out);
                                    $difference = $date1->diffInMinutes($date2);
                     $hour = \App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)
            ->where('au_branch_id', Auth::user()->au_branch_id)
            ->first();
                                    $expectedIpAddresses = explode(',', $expectedIpAddress);

                                @endphp

                                {{-- Checkin Date --}}
                                <td class="text-center">{{ $checkInDate }}</td>

                                {{-- Checkin Time --}}
                                <td class="text-center">
                                  @if ($defg - $dbtimestamp > 15*60)
                                      <p class="text-danger">{{ $formattedTimeIn }}</p>
                                  @else
                                      <p class="text-success">{{ $formattedTimeIn }}</p>
                                  @endif
                                </td>

                                {{-- Late | Ontime --}}
                                <td class="text-center">
                                  @if ($defg - $dbtimestamp > 15*60)
                                      @php
                                          $lateSeconds = $defg - $dbtimestamp - 15*60;
                                          $hours = floor($lateSeconds / 3600);
                                          $minutes = floor(($lateSeconds % 3600) / 60);
                                          $seconds = $lateSeconds % 60;
                                      @endphp

                                      <p class="text-danger">
                                          {{ $hours }}H {{ $minutes }}M {{ $seconds }}S
                                      </p>
                                  @else
                                      <p class="text-success">On Time</p>
                                  @endif
                                </td>


                                {{-- Checkin Ip --}}
                                <td>
                                  @if (in_array($user->user_ip, $expectedIpAddresses))
                                      <span style="color: green;">{{ $user->user_ip }}</span>
                                  @else
                                      <span style="color: red;">{{ $user->user_ip }} | {{ $user->au_checkin_reason }}</span>
                                  @endif
                                </td>
                              

                                {{-- Checkout Time --}}
                                <td class="text-center">
                                  @if ($difference >= $hour->au_working_hour)
                                    <p class="text-success">{{ $formattedTimeOut }}</p>
                                  @elseif($difference < $hour->au_working_hour)
                                    <p class="text-danger">{{ $formattedTimeOut }}</p>
                                  @endif
                                </td>
                              

                                {{-- Checkout IP --}}
                                <td>
                                  @if (in_array($user->user_ip, $expectedIpAddresses))
                                      <span style="color: green;">{{ $user->user_ip_checkout }}</span>
                                  @else
                                      <span style="color: red;">{{ $user->user_ip_checkout }}</span>
                                  @endif
                                </td>
      
                                <td class="text-center">
                                  @if ($difference < $hour->au_working_hour && $defg - $dbtimestamp > 15*60)
                                      <p class="text-warning">Incomplete and Late</p>
                                  @elseif($difference < $hour->au_working_hour)
                                      <p class="text-danger">Incomplete</p>
                                  @elseif($defg - $dbtimestamp > 15*60)
                                      <p class="text-danger">Late</p>
                                  @elseif($difference >= $hour->au_working_hour)
                                      <p class="text-success">Complete</p>
                                  @endif
                                </td>
                          </tr> 
                        @endforeach
                        </tbody>
                      </table>
                  </div>
              </div>
            </div>
        </div>
    </div>
</div>

<script>
    updateSelectedMonthYear();
    $('form').submit(function() {
        updateSelectedMonthYear();
    });

    function updateSelectedMonthYear() {
        var selectedMonth = $("#month option:selected").text();
        var selectedYear = $("#year option:selected").text();
        $("#selectedMonthYear").text(selectedMonth + ' ' + selectedYear);
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

