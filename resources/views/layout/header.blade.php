<header class="main-header">
    <!-- Logo -->
    <a href="{{url('/index')}}" class="logo" style="text-decoration: none; background-color: white;">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="{{ asset('asset/logo_text.png') }}" alt=""></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="{{ asset('asset/logo_text.png') }}" alt=""></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" style="height: 0px;padding-top: 5px;">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" style="text-decoration: none;">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>

      </a>
      <div style="text-align:center; width:80%;">
          <span id="clock" style="color:white;"></span><br>
          <span style="color: white; font-size: 19px;">{{ Auth::user()->au_company_name }}</span>
      </div>
      {{-- &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
      <br>
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
      &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
       --}}

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          <!-- Notifications: style can be found in dropdown.less -->

          <li class="dropdown notifications-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="margin-top: -45px;">
                  @php
                  $com = Auth::user()->au_company_id;

                  $short_notify = App\Models\Inv_product_detail::where('inv_pro_det_com_id',$com)
                      ->where('inv_pro_det_status',1)
                      ->whereRaw('inv_pro_det_available_qty < inv_pro_det_short_qty')
                      ->count();

                  $short_detail = App\Models\Inv_product_detail::where('inv_pro_det_com_id',$com)
                      ->where('inv_pro_det_status',1)
                      ->whereRaw('inv_pro_det_available_qty < inv_pro_det_short_qty')
                      ->get();
                @endphp
                <i class="fa fa-bell-o"></i>
                <span class="label label-danger">{{ $short_notify }}</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">You have {{ $short_notify }} short products</li>
                <li>
                  <!-- inner menu: contains the actual data -->
                  <ul class="menu" style="overflow: scroll; height: 200px;">
                    @foreach ($short_detail as $short)
                    <li>
                        <a href="#">
                          <i class="fa fa-arrow-right text-aqua"></i> <b>{{ $short->inv_pro_det_pro_id }}</b><br>
                          <span class="fa fa-asterisk">
                            Short: {{ $short->inv_pro_det_short_qty - $short->inv_pro_det_available_qty}},
                            Available: {{ $short->inv_pro_det_available_qty}}
                          </span>
                        </a>
                    </li>
                    @endforeach
                  </ul>
                </li>
                <li class="footer"><a href="{{ route('inventory.short') }}">View all</a></li>
              </ul>
            </li>

          <!-- Tasks: style can be found in dropdown.less -->
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="margin-top: -50px;">
              <img src=" {{ config('app.custom_image_path') }}{{ Auth::user()->au_company_img }}" class="user-image" alt="User Image">
              <span class="hidden-xs">{{ Auth::user()->au_name }}</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="{{ config('app.custom_image_path') }}{{ Auth::user()->au_company_img }}" class="img-circle" alt="User Image">

                <p>
                  {{ Auth::user()->au_name }}
                  <small>Member since {{ Auth::user()->au_created_date }}</small>
                </p>
              </li>
              <!-- Menu Body -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="{{ url('/user_profile',['id' => Auth::user()->au_id]) }}" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="javascript:void(0);" class="btn btn-default btn-flat" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->

        </ul>
      </div>
    </nav>
  </header>
  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
  </form>
  <script type="text/javascript">

tday=new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
    tmonth=new Array("January","February","March","April","May","June","July","August","September","October","November","December");

        function GetClock(){
        var d=new Date();
        var nday=d.getDay(),nmonth=d.getMonth(),ndate=d.getDate(),nyear=d.getYear();
        if(nyear<1000) nyear+=1900;
        var nhour=d.getHours(),nmin=d.getMinutes(),nsec=d.getSeconds(),ap;

        if(nhour==0){ap=" AM";nhour=12;}
        else if(nhour<12){ap=" AM";}
        else if(nhour==12){ap=" PM";}
        else if(nhour>12){ap=" PM";nhour-=12;}

        if(nmin<=9) nmin="0"+nmin;
        if(nsec<=9) nsec="0"+nsec;

        document.getElementById('clock').innerHTML=""+tday[nday]+", "+tmonth[nmonth]+" "+ndate+", "+nyear+" "+nhour+":"+nmin+":"+nsec+ap+"";
        }

        window.onload=function(){
        GetClock();
        setInterval(GetClock,1000);
        }
  </script>
