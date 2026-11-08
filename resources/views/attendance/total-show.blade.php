@extends('layout.master')
@section('payroll_class','menu-open')
@section('attendance_total_class','active')
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
          <h1>
            Payroll
          </h1>
        </section>
        <div class="box">
            <div class="box-body">
            <div class="row">
            <form action="" method="get">
              <div class="col-xs-3">
                <select class="form-control" name="year" id="year">
                @for( $this_year = date('Y');$this_year>=2007; $this_year--)
              <option value="{{ $this_year }}" {{ (isset($request_year)? (($request_year == $this_year)? 'selected':''):'') }}>{{ $this_year }}</option>
            @endfor
                </select>
              </div>
              <div class="col-xs-3">
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

              <div class="col-xs-3">
                <button type="submit" name="searchbtn" class="btn btn-info">Search</button>
              </div>
              </form>
            </div>
          </div>
                <div class="box-header">
                  <h3 class="box-title">User Attendance List</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <table id="example" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      @if ($userType == 5)
                        <th>SL</th>
                        <th>Emp Id</th>
                        <th>User Name</th>

                        <th>InComplete(H)</th>
                        <th>InComplete(S)</th>

                        <th>Over(H)</th>
                        <th>Over(S)</th>

                        <!--<th>Present</th>-->
                        <!--<th>Absent</th>-->
                        <th class="text-center">Action</th>
                      @else
                        <th>SL</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                      @endif

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
                        @if ($userType == 5)
                          {{-- Emp Id Section --}}
                          @foreach ($userComId as $employeId)
                          @if ($user->user_id == $employeId->au_id)
                                <td>{{ $employeId->au_user_id }}</td>
                          @endif
                          @endforeach

                          {{-- User Name Section --}}
                          <td>{{ @App\Models\Admin_user::attendanceUser($user->user_id) }}</td>

                          {{-- InComplete(H) --}}
                          <td>{{ number_format($user->incom_hour + ($user->incom_minute/60),2) }}</td>
                          {{-- InComplete(s) --}}
                          <td>{{ $user->incom_sec }}</td>

                          {{-- Over(H) --}}
                          <td>{{ number_format($user->over_hour + ($user->over_minute/60),2) }}</td>
                          {{-- Over(S) --}}
                          <td>{{ $user->over_sec }}</td>

                          <!--<td></td>-->
                          <!--<td></td>-->

                          <td align="center">
                            <button class="button btn-sm btn-info" onclick="userAttendance('{{ $user->user_id }}','{{ $request_year }}',{{ $request_month }})"><i class="fa fa-eye"></i></button>
                            <button class="button btn-sm btn-info" onclick="userAttendanceDetailsPrint('{{ $user->user_id }}')"><i class="fa fa-download"></i></button>
                          </td>
                        @else
                        @php($workShift = explode(',',$comDetails->au_work_shift))
                        @php($lateCheckIn = $workShift[0])
                        @php($dbtimestamp = strtotime($lateCheckIn))

                        @php($checkIn = strtotime($user->check_in))
                        @php($abcd = date('H:i:s', $checkIn))
                        @php($defg = strtotime($abcd))

                          <td>
                              @if ($defg - $dbtimestamp > 15*60)
                                <p class="text-danger">{{ $user->check_in }}(Late)</p>
                            @else
                                <p class="text-success">{{ $user->check_in }}</p>
                            @endif
                          </td>

                          @php($date1 = \Carbon\Carbon::parse($user->check_in))
                          @php($date2 = \Carbon\Carbon::parse($user->check_out))
                          @php($difference = $date1->diffInMinutes($date2))
                          @php($hour = \App\Models\Admin_user::where('au_company_id',Auth::user()->au_company_id)->first())

                          <td>
                              @if ($difference >= $hour->au_working_hour)
                              <p class="text-success">{{ $user->check_out }}</p>
                            @elseif($difference < $hour->au_working_hour)
                              <p class="text-danger">{{ $user->check_out }}(incomplete)</p>
                            @endif
                          </td>



                          <td>
                            @if ($difference < $hour->au_working_hour && $defg - $dbtimestamp > 15*60)
                                <p class="text-warning">Incomplete and Late</p>
                            @elseif($difference < $hour->au_working_hour)
                                <p class="text-danger">Imcomplete</p>
                            @elseif($defg - $dbtimestamp > 15*60)
                                <p class="text-danger">Late</p>
                            @elseif($difference >= $hour->au_working_hour)
                                <p class="text-success">Complete</p>
                            @endif
                          </td>
                        @endif

                      </tr> 
                    @endforeach
                    </tbody>
                  </table>
                </div>
                <!-- /.box-body -->
              </div>
             </section>

              <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Attendance History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>  
                  <div class="modal-body" id="userData">
                    ... 
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
@endsection
@section('custom_style')
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
</style>
@endsection
@section('custom_script')
    <script>

        $('#example').DataTable({
        order: [[1, 'asc']],
        paging: true,
        pageLength: 10,
        rowCallback: function(row, data, index) {
            var page = $('#example').DataTable().page();
            var length = $('#example').DataTable().page.len();
            var rowNumber = index + 1 + (page * length);
            $('td:eq(0)', row).html(rowNumber);
        },
        responsive: false
        });
    </script>
<script type="text/javascript">
  function deletesup(){
    let clickDel = confirm("Are you sure want to delete this?");
    if (clickDel == true) {
      return true;
    }else{
      return false;
    }  
  }

  function userAttendance(userid,year,month){
        // console.log(year);

        $.ajax({
          type: "GET",
          url: "{{ route('payroll.attendance-ajax') }}",
          data: {userid:userid,year:year,month:month},
          success:function(result){
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
            let route = "{!! route('payroll.attendance-print') !!}?month="+month+"&year="+year+"&user="+user;
            window.open(route, '_blank');
    }
</script>
@endsection
