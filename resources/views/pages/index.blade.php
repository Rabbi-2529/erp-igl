@php($permission = App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)->first())
@php($permissionStatus = explode('-',$permission->au_permission_status))
@php($checkOut = App\Models\EmployeeAttendance::where('company_id',$permission->au_company_id)
                                      ->where('user_id',Auth::user()->au_id)
                                      ->orderBy('check_in','desc')
                                      ->first())
  @php($inClick = empty($checkOut->in_click) ? 0 : $checkOut->in_click)
  @php($outClick = empty($checkOut->out_click) ? 0 : $checkOut->out_click)

@php($out = date('Y-m-d', strtotime($checkOut)))
{{-- @dd($checkOut->check_out) --}}
@php($todayDate = Carbon\Carbon::now()->format('Y-m-d'))

  @extends('layout.master')
  @section('content')
  <section class="content-header">
      <h1>
        Dashboard
        <small>Control panel</small>
      </h1>
      @if (in_array(8,$permissionStatus))
      <ol class="breadcrumb">
        @if ($check == false)
        <li>
          <form action="{{ route('payroll.check-in') }}" method="POST">
            @csrf
          <button type="submit" class="btn btn-sm btn-success" style="margin-top: -25px;">
          <b>Check In</b>
          </button>
        </form>
        </li>
        @endif

        {{-- @dd($todayDate) --}}
        @if ($check == true && $outCheck == false)
        <li>
          <form action="{{ route('payroll.check-out') }}" method="POST">
            @csrf

          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure...?')" style="margin-top: -25px;">
            <b>Check Out</b>
          </button>
        </form>
        </li>
       @endif

      </ol>
      @endif

    </section>

    {{-- ===================== --}}
    <section class="content">
      <!-- Small boxes (Stat box) -->
      @if(session()->has('message'))
          <div class="alert alert-danger">
              {{ session()->get('message') }}
          </div>
      @endif
      <div class="row">
          @if(session()->has('login'))
          <div id="alert" class="alert alert-success alert-dismissible" role="alert">
                {{ session('login') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif

          @if(session()->has('msg'))
          <div class="alert alert-{{ session('type') }} alert-dismissible" role="alert">
                {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
          @if(session()->has('checkout'))
          <div class="alert alert-{{ session('type') }} alert-dismissible" role="alert">
                {{ session('checkout') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
          @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 6 || Auth::user()->au_user_type == 5 || Auth::user()->au_user_type == 7)
          <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3>{{ $today }}</h3>

              <p>Today's Follow Up</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="{{ url('/todays_followup')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3>{{ $feedback}}</h3>

              <p>Given Follow Up</p>
            </div>
            <div class="icon">
              <i class="fa fa-check-circle"></i>
            </div>
            <a href="{{ url('/given_followup')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
            <h3>{{ $total}}</h3>

              <p>Today's Customers</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ url('/followup_list')}}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3>Due</h3>

              <p>Follow Up</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
          <a href="{{ url('/due-follow-up') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

            @if (Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="row">
              <div class="col-md-6 mt-2">
                @php($intime = [])
                @php($latetime = [])
                @php($comWorkShift = \App\Models\Admin_user::where('au_company_id',Auth::user()->au_company_id)->first())
                {{-- @dd($comWorkShift) --}}
                @php($workshift = explode(',',$comWorkShift->au_work_shift))


                @foreach ($todayAttendance as $user)
                @php($lateCheckIn = $workshift[0])
                @php($dbtimestamp = strtotime($lateCheckIn))

                @php($checkIn = strtotime($user->check_in))
                @php($abcd = date('H:i:s', $checkIn))
                @php($defg = strtotime($abcd))

                @if ( $defg - $dbtimestamp > 15*60 )
                @php( $latetime[] = $user->id)

                @else
                @php( $intime[] = $user->id)

                @endif
                @endforeach


                <div class="inner " style="margin-top: 15px; margin-left:10px;">
                  <h3><a href="{{ route('todayIntimeAttendance') }}" style="color: #fff; text-decaration: none">{{ count($intime) }}</a>/<a href="{{ route('todayLateAttendance') }}" style="color: #fff; text-decaration: none">{{ count($latetime) }}</a></h3>
                </div>
              </div>
              <div class="col-md-6 mt-2">
                <div class="inner " style="margin-top: 15px; margin-right:10px;">
                <h3>{{ $todayAttendanceCount }}/{{ $employeCount }}</h3>
              </div>
              </div>
            </div>

            <p>Today Attendance </p>
            <div class="icon">
              <i class="fa fa-check-circle"></i>
            </div>
            <a href="{{ route('payroll.today-attendance-show') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        @endif
      </div>

      {{-- ==================================== --}}

      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Monthly Recap Report</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-wrench"></i></button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </div>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-8">
                  <p class="text-center">
                    <strong>Sales: 1 Jan, 2014 - 30 Jul, 2023</strong>
                  </p>

                  <div class="chart">
                    <!-- Sales Chart Canvas -->
                    <canvas id="salesChart" style="height: 180px;"></canvas>
                  </div>
                  <!-- /.chart-responsive -->
                </div>
                <!-- /.col -->
                <div class="col-md-4">
                  <p class="text-center">
                    <strong>Goal Completion</strong>
                  </p>

                  <div class="progress-group">
                    <span class="progress-text">Add Products to Cart</span>
                    <span class="progress-number"><b>160</b>/200</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-aqua" style="width: 80%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Complete Purchase</span>
                    <span class="progress-number"><b>310</b>/400</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-red" style="width: 80%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Visit Premium Page</span>
                    <span class="progress-number"><b>480</b>/800</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-green" style="width: 80%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                  <div class="progress-group">
                    <span class="progress-text">Send Inquiries</span>
                    <span class="progress-number"><b>250</b>/500</span>

                    <div class="progress sm">
                      <div class="progress-bar progress-bar-yellow" style="width: 80%"></div>
                    </div>
                  </div>
                  <!-- /.progress-group -->
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- ./box-body -->
            <div class="box-footer">
              <div class="row">
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>
                    <h5 class="description-header">BDT {{ number_format($lifetimeRev,2) }}</h5>
                    <span class="description-text">Life Time REVENUE</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>
                    <h5 class="description-header">BDT {{ number_format($currentMonth,2) }}</h5>
                    <span class="description-text">Current Month</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block border-right">
                    <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                    <h5 class="description-header">$24,813.53</h5>
                    <span class="description-text">TOTAL PROFIT</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 col-xs-6">
                  <div class="description-block">
                    <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                    <h5 class="description-header">1200</h5>
                    <span class="description-text">GOAL COMPLETIONS</span>
                  </div>
                  <!-- /.description-block -->
                </div>
              </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      @endif
      <!-- /.row (main row) -->

    </section>
    @endsection
    @section('custom_script')
        <script>
            $(".alert").delay(5000).slideUp(200, function() {
                $(this).alert('close');
            });
        </script>
    <script>
      $('form').submit(function() {
        $(this).find("button[type='submit']").prop('disabled',true);
      });
    </script>


    <script src="{{ asset('asset/bower_components/chart.js/Chart.js')}}"></script>
    <script src="{{ asset('asset/dist/js/pages/dashboard2.js')}}"></script>

    @endsection
