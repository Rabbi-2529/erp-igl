@php

    $admin_user  = auth()->user()->au_user_type;
    $user_access = auth()->user()->au_permission_status;
    $user_accesses = explode('-', $user_access);
    function check_or_permission($permission_array) {
      $user_access = auth()->user()->au_permission_status;
      $user_accesses = explode('-', $user_access);
      $return_value = false;
      foreach ($permission_array as $permission_key){
        if(in_array($permission_key, $user_accesses)) {
          return true;
        } else {
          $return_value = false;
        }
      }
      return $return_value;
    }
@endphp

<style>
  /* Chrome, Safari, Edge, Opera */
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* Firefox */
  input[type=number] {
    -moz-appearance: textfield;
  }
</style>


<a href="{{ url('/') }}" class="logo" style="background-color: rgb(255, 255, 255)"> 
  <span class="logo-lg">
      <img src="{{ asset('assets/img/icons/logo.png') }}" alt="IGL ERP">
  </span>
  <span class="logo-mini">

<img src="{{ asset('assets/img/icons/mini-logo.png') }}" alt="IGL ERP">
</span>
</a>

<div class="se-pre-con"></div>

<!-- Header Navbar -->
<nav class="navbar navbar-static-top">
  <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="pe-7s-keypad"></span>
  </a>

  <span class="top-fixed-link">

@if($admin_user == 4)
    <a href="{{ route('buy.pro_sell') }}" class="btn btn-success btn-outline" title="Sale"><i class="fa fa-balance-scale"></i><span class="erp-sc-text"> Sale</span></a>
    <a href="{{ route('customer.accounts.payment') }}" class="btn btn-success btn-outline" title="Customer Receive"><i class="fa fa-money"></i><span class="erp-sc-text"> Customer Receive</span></a>
    <a href="{{ route('inventory.supplier.accounts.payment') }}" class="btn btn-success btn-outline" title="Supplier Payment"><i class="fa fa-money" aria-hidden="true"></i><span class="erp-sc-text"> Supplier Payment</span></a>
    <a href="{{ route('buy.buy-product-new') }}" class="btn btn-success btn-outline" title="Purchase"><i class="ti-shopping-cart"></i><span class="erp-sc-text"> Purchase</span></a>
@else
    @if(check_or_permission([61]))
        <a href="{{ route('buy.buy-product-new') }}" class="btn btn-success btn-outline" title="Purchase"><i class="ti-shopping-cart"></i><span class="erp-sc-text"> Purchase</span></a>
         <a href="{{ route('order_customer') }}" class="btn btn-success btn-outline" title="Order Customer"><i class="ti-shopping-cart"></i><span class="erp-sc-text"> Order Customer</span></a>
    @endif
    @if(check_or_permission([62]))
        <a href="{{ route('buy.pro_sell') }}" class="btn btn-success btn-outline" title="Sale"><i class="fa fa-balance-scale"></i><span class="erp-sc-text"> Sale</span></a>
    @endif
    @if(check_or_permission([58]))
        <a href="{{ route('inventory.supplier.accounts.payment') }}" class="btn btn-success btn-outline" title="Supplier Payment"><i class="fa fa-money" aria-hidden="true"></i><span class="erp-sc-text"> Supplier Payment</span></a>
    @endif
    @if(check_or_permission([60]))
        <a href="{{ route('customer.accounts.payment') }}" class="btn btn-success btn-outline" title="Customer Receive"><i class="fa fa-money"></i><span class="erp-sc-text"> Customer Receive</span></a>
    @endif
@endif


      {{-- <span id="clock" style="font-weight: bold; display: inline-block; margin-top: 18px; color:#37a000;"></span> --}}
    
  </span>

  <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
      <!-- Messages -->

        @php
          $com = Auth::user()->au_company_id;
          // Out-of-stock bell: only for people who can open Short Quantity
          // (company admin with the Inventory module, staff with permission 69, root)
          $canSeeStock = $admin_user == 1
              || ($admin_user == 4 ? check_or_permission([4]) : check_or_permission([69]));
          $short_notify = $canSeeStock
              ? App\Models\Inv_product_detail::where('inv_pro_det_com_id',$com)
                  ->where('inv_pro_det_status',1)
                  ->whereRaw('inv_pro_det_available_qty < inv_pro_det_short_qty')
                  ->count()
              : 0;
        @endphp

        @if($canSeeStock)
        <li class="dropdown notifications-menu">
          <a href="{{ route('inventory.short') }}" title="Products below their short quantity">
          <i class="pe-7s-attention"></i>
          @if($short_notify > 0)<span class="label label-danger">{{ $short_notify }}</span>@endif
          </a>
        </li>
        @endif

        <!-- light / dark mode -->
        <li class="erp-mode-li">
          <a href="#" id="erpModeToggle" role="button" title="Switch to dark mode" aria-label="Switch to dark mode"><i class="fa fa-moon-o"></i></a>
        </li>

        <!-- settings -->
        <li class="dropdown dropdown-user">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="pe-7s-settings"></i></a>
          <ul class="dropdown-menu">
            <li>
              <a href="{{ url('/user_profile',['id' => Auth::user()->au_id]) }}"><i class="pe-7s-users"></i> Profile</a>
              </li>
            <li>
              <a href="#" data-toggle="modal" data-target="#erpPasswordModal"><i class="pe-7s-lock"></i> Change Password</a>
            </li>
            <li>
              <a href="{{ route('logout') }}"><i class="pe-7s-key"></i> Logout</a>
            </li>
          </ul>
        </li>
      </ul>
  </div>
</nav>
<script type="text/javascript">
  // Light / Dark button: remembered in this browser; pages with charts reload so the charts redraw in the new colours
  (function () {
    var root = document.documentElement, btn = document.getElementById('erpModeToggle');
    if (!btn) { return; }
    function paint() {
      var dark = root.getAttribute('data-theme') === 'dark', label = dark ? 'Switch to light mode' : 'Switch to dark mode';
      btn.innerHTML = '<i class="fa ' + (dark ? 'fa-sun-o' : 'fa-moon-o') + '"></i>';
      btn.setAttribute('title', label); btn.setAttribute('aria-label', label);
    }
    paint();
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var dark = root.getAttribute('data-theme') !== 'dark';
      if (dark) { root.setAttribute('data-theme', 'dark'); } else { root.removeAttribute('data-theme'); }
      var meta = document.getElementById('erp-color-scheme');
      if (meta) { meta.setAttribute('content', dark ? 'dark' : 'only light'); }
      try { localStorage.setItem('erp.mode', dark ? 'dark' : 'light'); } catch (err) {}
      paint();
      if (document.querySelector('canvas, .canvasjs-chart-container')) { window.location.reload(); }
    });
  })();

  tday=new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
      tmonth=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
  
          function GetClock(){
          if (!document.getElementById('clock')) { return; } // the clock is switched off in the header
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
  
          // addEventListener, so pages that set window.onload (the dashboard) are not overwritten
          window.addEventListener('load', function(){
          if (!document.getElementById('clock')) { return; }
          GetClock();
          setInterval(GetClock,1000);
          });

    </script>