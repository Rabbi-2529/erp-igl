<div class="sidebar">
    <div class="user-panel text-center">
        <div class="image">
            @php
                $imagePath = asset('assets/img/icons/user.png');
                $customImagePath = asset('public/profile/' . Auth::user()->au_company_img);
            @endphp
            <img src="{{ isset($customImagePath) ? $customImagePath : $imagePath }}"
                class="img-circle" alt="User">
        </div>
        <div class="info">
            <p>{{ Auth::user()->au_name }}</p>
            <a href="#"><i class="fa fa-circle text-success"></i>
                Online</a>
        </div>
    </div>

    <ul class="sidebar-menu">

        <!-- Client Search START -->
        <li class="header">
            <form id="search-form" method="GET" action="{{ route('search') }}">
          <div class="input-group">
              <input type="number" class="form-control" name="q" placeholder="Search Clients" id="search-input">
              <span class="input-group-btn" id="search-result">
              </span>
          </div>
        </form>
        </li>
        <!-- Client Search END -->

        <li class="treeview {{ (url('/') == request()->url()) ? 'active' : '' }}">
            <a href="{{ url('/') }}">
                <i class="ti-dashboard"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- HRM START -->
        <li class="treeview @yield('company_class')">
            <a href="#">
                <i class="fa fa-users"></i><span>Branch </span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="treeview @yield('branch_class')">
                    <a href="#">
                        <i class="fa fa-users"></i><span>Branch Registration</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                   <ul class="treeview-menu">
                    <li class="@yield('add_branch_class')">
                        <a href="{{ route('branch.create') }}">Add Branch</a>
                    </li>                        
                    <li class="@yield('manage_branch_class')">
                        <a href="{{ route('branch.index') }}">Manage Branch</a>
                    </li>
                          <li class="@yield('manage_branch_class')">
                        <a href="{{ route('branch.assign') }}">Assign Branch User</a>
                    </li>
                    <li class="@yield('assigned_users_class')">
                    <a href="{{ route('branch.assignedUsers') }}">Assigned Branch Users</a>
                </li>
                </ul>

                </li>
                
            
       
            
                




    </ul>
    </li>
                <!-- HRM START -->
      <li class="treeview @yield('human_resource_class')">
    <a href="#">
        <i class="fa fa-users"></i><span> HRM</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu">

     <li class="treeview @yield('attendance_class')">
                <a href="#">
                    <i class="fa fa-calendar-check"></i><span>Attendance</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="@yield('branches_attendance_class')">
                        <a href="{{ route('company.hrm.attendance.branches') }}">
                            <i class="fa fa-building"></i> All Branches
                        </a>
                    </li>
                    <li class="@yield('today_attendance_class')">
                        <a href="{{ route('company.hrm.attendance.today') }}">
                            <i class="fa fa-calendar-day"></i> Today's Attendance
                        </a>
                    </li>
                      <li class="@yield('employee_attendance_class')">
                        <a href="{{ route('company.hrm.attendance.total-show') }}">
                            <i class="fa fa-users"></i> Attendance Report
                        </a>
                    </li>
                    <li class="@yield('attendance_analytics_class')">
                        <a href="{{ route('company.hrm.attendance.analytics') }}">
                            <i class="fa fa-chart-bar"></i> Analytics
                        </a>
                    </li>
                    <li class="@yield('employee_attendance_class')">
                        <a href="{{ route('company.hrm.attendance.employee-wise') }}">
                            <i class="fa fa-users"></i> Employee Wise
                        </a>
                    </li>

                </ul>
            </li>
            <li class="treeview @yield('employee_management_class')">
    <a href="#">
        <i class="fa fa-users"></i><span>Employee Management</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu">
        <li class="@yield('employee_list_class')">
            <a href="{{ route('company.hrm.employees.list') }}">
                <i class="fa fa-list"></i> Employee List
            </a>
        </li>
                           <li class="@yield('transfer_employees_class')">
                    <a href="{{ route('company.hrm.employees.transfer.list') }}">
                        <i class="fa fa-exchange"></i> Transfer Employees
                    </a>
                </li>
           
    </ul>
</li>
 

    </ul>
    
    
</li>


  <li class="treeview @yield('sms_class')">
                    <a href="#" style="text-decoration: none;">
                    <i class="fa fa-dashboard"></i> <span>SMS</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
               
               
      
            
             
           
                    <li class="@yield('template_class')">
                <a href="{{ url('/sms_temp_edit')}}">
                    <i class="fa fa-circle-o"></i> Template
                </a>
                </li>
        
                    </ul>
            </li>
</div>