
<aside class="main-sidebar" >
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->

	  <div class="user-panel text-center">
        <div class="image">
            <img src="{{ asset('/asset/image/')}}/{{ Auth::user()->au_company_img }}" class="img-circle" alt="User Image">
        </div>
        <div class="info">
            <p>{{ Auth::user()->au_name }}</p>
            <a href="#"><i class="fa fa-circle text-success"></i>
                Online</a>
        </div>
    </div>
      
<ul class="sidebar-menu" data-widget="tree">
        <li class="header"></li>
        <li class="">
          <a href="{{ url('/index')}}" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        <!-- @if( Auth::user()->au_user_type == 4 ||  Auth::user()->au_user_type == 5 || Auth::user()->au_user_type == 6)
        <li class="">
          <a href="{{ url('/todays_followup')}}" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>Todays Follow up</span>
          </a>
        </li>
        @endif -->
        <li class="treeview @yield('sds_menu_class')">
          <a href="#" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>CRM</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
          <li class="treeview @yield('create_company_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Create Company</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
		            <li class="@yield('create_company_sub')"><a href="{{ url('/company_register')}}"><i class="fa fa-circle-o"></i> Create Company</a></li>
		            <li class="@yield('company_list_class')"><a href="{{ url('/admin_list')}}"><i class="fa fa-circle-o"></i> Company List</a></li>
              </ul>
		        <li class="@yield('add_reason_class')"><a href="{{ url('/add_reason')}}"><i class="fa fa-circle-o"></i> Add Reason/Service</a></li>
		        <li class="@yield('provide_reason_class')"><a href="{{ url('/provide_reason')}}"><i class="fa fa-circle-o"></i> Provide Reason</a></li>
                <li class="@yield('add_category_class')"><a href="{{ url('/add_category')}}"><i class="fa fa-circle-o"></i> Add Category</a></li>
                <li class="@yield('find_us_class')"><a href="{{ url('/find_us')}}"><i class="fa fa-circle-o"></i> Find Us</a></li>
            </li>
            <li class="treeview @yield('department_class')">
				<a href="#" style="text-decoration: none;">
				  <i class="fa fa-dashboard"></i> <span>Department</span>
				  <span class="pull-right-container">
					<i class="fa fa-angle-left pull-right"></i>
				  </span>
				</a>
			   <ul class="treeview-menu">
				<li class="@yield('department_add_class')"><a href="{{ route('department') }}"><i class="fa fa-circle-o"></i> Department</a></li>
				<li class="@yield('department_assign_class')"><a href="{{ route('department-assign') }}"><i class="fa fa-circle-o"></i> Assign</a></li>
				</ul>

			  </li>
          </ul>
        </li>
        <!-- end crm -->


        <!-- Start OMS -->
        <li class="treeview @yield('oms_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>OMS</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                      @if( Auth::user()->au_id == 1)
		            <li  class="@yield('oms_reason')"><a href="{{url('root/reason')}}"><i class="fa fa-circle-o"></i> Reason</a></li>
		            <li  class="@yield('oms_mode')"><a href="{{url('root/mode')}}"><i class="fa fa-circle-o"></i> Mode</a></li>
                      @endif
		          </ul>
            </li>
            <!-- End OMS -->


            <li class="treeview @yield('sms_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>SMS</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>
			          <ul class="treeview-menu">
			            <li class="@yield('sms_setup_class')"><a href="{{ url('/sms_setup')}}"><i class="fa fa-circle-o"></i> SMS Setup</a></li>
			            <li class="@yield('sms_campaign_class')"><a href="{{ url('/sms_campaign')}}"><i class="fa fa-circle-o"></i> SMS Campaign</a></li>
			            <li class="@yield('template_class')"><a href="{{ url('/sms_temp_edit')}}"><i class="fa fa-circle-o"></i> Template</a></li>
			          </ul>
		        </li>
		        <li class="treeview @yield('leave_management')">
				<a href="#" style="text-decoration: none;">
				  <i class="fa fa-dashboard"></i> <span>Leave Management</span>
				  <span class="pull-right-container">
					<i class="fa fa-angle-left pull-right"></i>
				  </span>
				</a>
				  <ul class="treeview-menu">
						<li class="@yield('leave_add')">
						  <a href="{{ route('leave.leaveTypeadd') }}">
							<i class="fa fa-circle-o"></i> Add Leave Type
						  </a>
						</li>
						<li class="@yield('leave_list')">
							<a href="{{ route('leave.leaveTypeList') }}">
							  <i class="fa fa-circle-o"></i> All Leave Type
							</a>
						</li>
				  </ul>

			</li>
    </section>
    </aside>
