@php
    $user_access = auth()->user()->au_permission_status;
    $user_accesses = explode('-', $user_access);
    // function check_or_permission($permission_array) {
    //   $user_access = auth()->user()->au_permission_status;
    //   $user_accesses = explode('-', $user_access);
    //   $return_value = false;
    //   foreach ($permission_array as $permission_key){
    //     if(in_array($permission_key, $user_accesses)) {
    //       return true;
    //     } else {
    //       $return_value = false;
    //     }
    //   }
    //   return $return_value;
    // }
@endphp

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

<li class="header">
    <form id="search-form" method="GET" action="{{ route('search') }}" onsubmit="return false;">
        <div class="input-group">
            <input type="text" class="form-control" name="q" placeholder="Search Clients (11 digits)" id="search-input" maxlength="11">
            <span class="input-group-btn" id="search-result">
                <button type="submit" class="btn btn-primary" id="search-submit-btn">
                    <i class="fa fa-search"></i>
                </button>
            </span>
        </div>
        <div id="search-loading" style="display: none; margin-top: 5px; text-align: center;">
            <i class="fa fa-spinner fa-spin"></i> Searching...
        </div>
        <div id="search-error" style="color: red; font-size: 12px; margin-top: 5px; display: none;"></div>
    </form>
</li>
<!-- Client Search END -->

<script>
$(document).ready(function () {
    // Function to perform search
    function performSearch() {
        var q = $('#search-input').val().trim();
        
        // Clear previous results
        $('#search-result').empty();
        $('#search-error').hide();
        
        // Validate input - check if exactly 11 digits
        if (q.length !== 11 || !/^\d+$/.test(q)) {
            if (q.length > 0) {
                $('#search-error').text('Please enter an 11-digit mobile number').show();
                // Add the search button back
                $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
            } else {
                $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
            }
            return false;
        }
        
        // Show loading
        $('#search-loading').show();
        $('#search-result').empty();
        
        // Make AJAX request
        $.ajax({
            type: 'GET',
            url: '{{ route('search') }}',
            data: {q: q},
            success: function (data) {
                $('#search-loading').hide();
                
                if (data.status == 'success') {
                    var url = data.url;
                    var button = '<a href="' + url + '" class="btn btn-success" target="_blank">Follow Up</a>';
                    $('#search-result').html(button);
                } else if (data.status == 'error') {
                    var message = data.message;
                    if (message == 'No matching records found') {
                        var url = data.url;
                        var button = '<a href="' + url + '" class="btn btn-primary" target="_blank">Add Customer</a>';
                        $('#search-result').html(button);
                    } else {
                        $('#search-error').text(message).show();
                        $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
                    }
                }
            },
            error: function (data) {
                $('#search-loading').hide();
                $('#search-error').text('An error occurred while searching').show();
                $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
            }
        });
    }
    
    // Handle Enter key press
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            performSearch();
            return false;
        }
    });
    
    // Handle search button click
    $(document).on('click', '#search-submit-btn', function(e) {
        e.preventDefault();
        performSearch();
        return false;
    });
    
    // Real-time validation for 11 digits (optional - keeps showing button)
    $('#search-input').on('input', function() {
        var q = $(this).val().trim();
        
        // Clear error when typing
        $('#search-error').hide();
        
        // If exactly 11 digits, show search button with tooltip
        if (q.length === 11 && /^\d+$/.test(q)) {
            $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
        } else if (q.length > 0) {
            // Show info message for invalid length
            $('#search-error').text('Need exactly 11 digits. Current: ' + q.length + ' digits').show();
            $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn" disabled style="opacity:0.6;"><i class="fa fa-search"></i></button>');
        } else {
            $('#search-result').html('<button type="submit" class="btn btn-primary" id="search-submit-btn"><i class="fa fa-search"></i></button>');
        }
    });
});
</script>
  <li class="">
          <a href="{{ url('/index')}}" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        <!-- HRM START -->
        @if(check_or_permission([82,83,84,87,91,96,97,98]) == true)
            <li class="treeview @yield('human_resource_class')">
                <a href="#">
                    <i class="fa fa-users"></i><span> HRM</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="treeview @yield('attendence_class')">
                        <a href="#">
                            <i class="fa fa-clock-o"></i><span>Attendance</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @if(in_array('82', $user_accesses))
                                <li class="treeview @yield('my_attendence_report_class')">
                                    <a href="{{ route('payroll.attendance-show') }}">My Attendance</a>
                                </li>
                            @endif

                       @if((in_array('91', $user_accesses) || in_array(101, $user_accesses)) || Auth::user()->au_user_type == 5)
    <li class="@yield('attendence_report_class')">
        <a href="{{ route('payroll.total-show') }}">Total Attendance</a>
    </li>
@endif

                              @if((in_array(87, haystack: $user_accesses) || in_array(100, $user_accesses)) || Auth::user()->au_user_type == 5)

                                <li class="@yield('today_attendance_class')">
                                    <a href="{{ route('payroll.today-attendance-show') }}">Today Attendance</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    
                                        <li class="treeview @yield('Create Employee Fine')">
                    @if(in_array('97', $user_accesses))
                    <a href="#">
                        <i class="fa fa-clock-o"></i><span>Fine</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    @endif
                    
                    <ul class="treeview-menu">
                    @if(in_array('97', $user_accesses))
                        <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('inventory.accounts.fines.create') }}">Create Fine</a>
                        </li> 
                        @endif
                        
                        @if(in_array('97', $user_accesses))
                        <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('fines.index') }}">Fine Report</a>
                        </li>    
                        @endif
                        
                        @if(in_array('98', $user_accesses))               
                        <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('employee_fines.index') }}">Employee Fine</a>
                        </li>
                        @endif


                        

                    </ul>
                </li>

                    @if(in_array('95', $user_accesses))
                    <li class="treeview @yield('payroll')">
                        <a href="#">
                            <i class="fa fa-paypal"></i><span>Payroll</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="treeview">
                                <a href="#">Manage Salary</a>
                            </li>
                            <li class="treeview">
                                <a href="#">Salary Advance</a>
                            </li>
                            <li class="treeview">
                                <a href="#">Salary Generate</a>
                            </li>
                        </ul>
                    </li>
                    @endif


                    <li class="treeview @yield('leave')">
                        <a href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z"/></svg><span> Leave</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="treeview @yield('leave_aplication')">
                                <a href="{{ route('leaveApplication') }}">Leave Application</a>
                            </li>
                            <li class="treeview @yield('leave_request')">
                                <a href="{{ route('leaveRequest') }}">Leave Request</a>
                            </li>
                        </ul>
                    </li>
                           <li class="treeview @yield('employee_activity')">
                    <a href="#">
                       <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512">
            <style>svg{fill:#ffffff}</style>
            <path d="M320 32C206.8 32 112 126.8 112 240c0 71.1 33.6 136.3 90.6 177.6L160 480c0 17.7 14.3 32 32 32H448c17.7 0 32-14.3 32-32l-42.6-62.4c57-41.3 90.6-106.5 90.6-177.6C528 126.8 433.2 32 320 32zm0 96a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm0 272a208 208 0 0 1-146.1-61.6C204.3 316.5 254.3 288 320 288s115.7 28.5 146.1 50.4A208 208 0 0 1 320 400z"/>
        </svg>
                        <span>Activity</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('employee_activity_my')">
                            <a href="{{ route('employee-activity.index') }}">
                                My Activity
                            </a>
                        </li>
                        @if(auth()->user() && auth()->user()->au_user_type == 5)
                          <li class="@yield('employee_activity_total')">
                                <a href="{{ route('employee-activity.total-activity') }}">
                                    Total Activity
                                </a>
                            </li>
                            <li class="@yield('employee_activity_today')">
                                <a href="{{ route('employee-activity.today-activity') }}">
                                    Today Activity
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
  <li class="@yield('employee_blood_donation')">
                        <a href="{{ route('payroll.blood_donates.index') }}">
                            Blood Donation
                        </a>
                      </li>
                      
              

                    {{-- @if(in_array('83', $user_accesses))
                        <li class="treeview @yield('shift_manage_class')">
                            <a href="#">
                                <i class="fa fa-user"></i><span>Shift Management</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if(in_array('84', $user_accesses))
                                    <li class="treeview @yield('add_shift_class')">
                                        <a href="{{ route('payroll.shift.add') }}">Add Shift</a>
                                    </li>
                                @endif
                                <li class="treeview @yield('shift_list_class')">
                                    <a href="{{ route('payroll.shift.list') }}">Shift List</a>
                                </li>
                            </ul>
                        </li>
                    @endif --}}

                    @if(check_or_permission([74]) == true)
                        <li class="treeview @yield('agm_class')">
                            <a href="#">
                                <i class="fa fa-user"></i><span>Head Of Sales</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview @yield('agm_team_add_class')">
                                    <a href="#">Add Leader/Manager</a>
                                </li>
                                <li class="treeview @yield('agm_team_list_class')">
                                    <a href="#">Leader/Manager List</a>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
        <!-- HRM END -->

        <!-- HR LETTERS: every employee sees their own letters; HR, approvers and department heads see the letters they work on -->
        @php
            $ltMenu = ['work' => false, 'make' => false];
            try {
                $ltSvc = \App\Services\HrmLetters::forUser();
                $ltMenu['make'] = $ltSvc->isHrExecutive();
                $ltMenu['work'] = $ltMenu['make'] || $ltSvc->canApprove()
                    || \Illuminate\Support\Facades\DB::table('admin_user')->where('au_company_id', $ltSvc->company)->where('au_dep_head_id', auth()->user()->au_id)->exists();
            } catch (\Throwable $e) {
                $ltMenu = null;     // letter tables not made yet
            }
        @endphp
        @if($ltMenu !== null)
            @if($ltMenu['work'])
                <li class="treeview @yield('hrm_letters_class')">
                    <a href="#"><i class="fa fa-envelope-o"></i> <span>HR Letters</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                    <ul class="treeview-menu">
                        @if($ltMenu['make'])<li><a href="{{ route('hrm.letters.create') }}">Generate Letter</a></li>@endif
                        <li><a href="{{ route('hrm.letters.index') }}">Letters</a></li>
                        @if($ltMenu['make'] && class_exists(\App\Services\HrmLetterRequests::class) && \App\Services\HrmLetterRequests::ready())<li><a href="{{ route('hrm.letters.requests') }}">Letter Requests</a></li>@endif
                        <li><a href="{{ route('hrm.letters.my') }}">My Letters</a></li>
                    </ul>
                </li>
            @else
                <li class="@yield('my_letters_class')"><a href="{{ route('hrm.letters.my') }}"><i class="fa fa-envelope-o"></i> <span>My Letters</span></a></li>
            @endif
        @endif


        @if(check_or_permission([40,41,42,43]) == true)
            <li class="treeview @yield('user_register_class')">
            <a href="#" style="text-decoration: none;">
                <i class="fa fa-dashboard"></i> <span>User Registration</span>
                <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                @if(in_array('40', $user_accesses))
                <li class="@yield('user_register_create')"><a href="{{ url('/create_team')}}"><i class="fa fa-circle-o"></i> Create Team</a></li>
                @endif
                @if(in_array('41', $user_accesses))
                <li class="@yield('user_register_user_reg')"><a href="{{ url('/user_register')}}"><i class="fa fa-circle-o"></i> User Register</a></li>
                @endif
                @if(in_array('42', $user_accesses))
                <li class="@yield('user_register_team_leader')"><a href="{{ url('/team_leaders')}}"><i class="fa fa-circle-o"></i> Team Leader/Manager</a></li>
                @endif
                @if(in_array('43', $user_accesses))
                <li class="@yield('user_register_user_list')"><a href="{{ url('/user_list')}}"><i class="fa fa-circle-o"></i> User</a></li>
                @endif
            </ul>
            </li>
        @endif

        <!-- CRM START -->
        @if(check_or_permission([11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]) == true)
        <li class="treeview @yield('customer_relation_class')">
            <a href="#">
                <i class="metismenu-icon pe-7s-user"></i><span> CRM</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="@yield('my_lead_link_class')">
                    <a href="{{ route('crm.my-lead-link') }}"><i class="fa fa-share-alt"></i> My Lead Link</a>
                </li>

                @if(check_or_permission([11,12,13,14,15]) == true)
                <li class="treeview @yield('customer_class')">
                    <a href="#">
                        <i class="fa fa-users"></i><span> Customer</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">

                        @if(in_array('11', $user_accesses))
                        <li class="treeview @yield('create_customer_class')">
                            <a href="{{ url('/create_customer')}}"> Create Customer</a>
                        </li>
                        @endif

                        @if(in_array('12', $user_accesses))
                        <li class="treeview @yield('my_customer_class')">
                            <a href="{{ url('/customer_list')}}"> My Customers</a>
                        </li>
                        @endif

                  @if(
                        in_array('14', $user_accesses) || 
                        in_array('102', $user_accesses) || 
                        in_array('104', $user_accesses)
                    )
                        <li class="treeview @yield('ordered_customer_class')">
                            <a href="{{ url('/order_customer')}}"> Ordered Customers</a>
                        </li>
                    @endif
                        @if(in_array('13', $user_accesses))
                        <li class="treeview @yield('total_customer_class')">
                            <a href="{{ url('/total_customer_list')}}"> Total Customers</a>
                        </li>
                        @endif

                        @if(in_array('15', $user_accesses))
                        <li class="treeview @yield('admins_created_class')">
                        <a href="{{ url('/admin_customer' ,['id' => Auth::user()->au_id]) }}">Admin's Created</a>
                        </li>
                        @endif

                        @if(check_or_permission([22,23,24,25]) == true)
                        <li class="treeview @yield('intelligent_search_class')">
                            <a href="#">
                                <i class="fa fa-search"></i><span> Intelligent Search</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if(in_array('22', $user_accesses))
                                <li class="treeview @yield('search_find_class')">
                                    <a href="{{ url('/search_findus')}}"> Search By Find Us</a>
                                </li>
                                @endif
                                @if(in_array('23', $user_accesses))
                                <li class="treeview @yield('search_reason_class')">
                                    <a href="{{ url('/search_reason')}}"> Search By Reason</a>
                                </li>
                                @endif
                                @if(in_array('24', $user_accesses))
                                <li class="treeview @yield('search_result_class')">
                                    <a href="{{ url('/search_result')}}"> Search By Result</a>
                                </li>
                                @endif
                                @if(in_array('25', $user_accesses))
                                <li class="treeview @yield('search_feedback_class')">
                                    <a href="{{ url('/search_feedback')}}"> Search By Feedback</a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                {{-- @if (Auth::user()->au_user_type != 7)
                    @if(check_or_permission([19]) == true)
                        <li class="treeview @yield('team_member_class')">
                            <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Team Member</span>
                                <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                            @if(in_array('19', $user_accesses))
                                <li class="@yield('team_member_list_class')">
                                <a href="{{ url('/members_list',['id' => Auth::user()->au_team_id]) }}"> Team Member List</a>
                                </li>
                            @endif
                            </ul>
                        </li>
                    @endif
                @endif --}}


                @if(check_or_permission([11,12,13,14,15]) == true)
                <li class="treeview @yield('followup_class')">
                    <a href="#">
                        <i class="fa fa-list"></i><span> Followup</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(in_array('20', $user_accesses))
                        <li class="treeview @yield('today_followup_class')">
                            <a href="{{ url('/todays_followup')}}"> Today Follow Up</a>
                        </li>
                        <li class="treeview @yield('follow_cus_class')">
                            <a href="{{ url('/followup')}}"> Follow Up</a>
                        </li>
                        @endif
                        <li class="treeview @yield('due_followup_class')">
                            <a href="{{ url('/due-follow-up')}}"> Due Follow Up</a>
                        </li>
                        @if(in_array('21', $user_accesses))
                        <li class="treeview @yield('nonfollow_cus_class')">
                            <a href="{{ url('/non_followup')}}"> Non FollowUp</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                
                
                <li class="treeview @yield('visit_class')">
                    <a href="#">
                        <i class="fa ti-book"></i><span> Sales Visit</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(in_array('95', $user_accesses))
                        <li class="treeview @yield('assign_visit_class')">
                            <a href="{{ route('sales-visit') }}"> Assign Visit</a>
                        </li>
                        <li class="treeview @yield('visit_report_class')">
                            <a href="{{ route('sales-visit-report') }}"> Visit Report</a>
                        </li>
                        @endif
                        <li class="treeview @yield('my_visit_class')">
                            <a href="{{ route('my-visit') }}"> My Visit</a>
                        </li>
                        <li class="treeview @yield('my_visit_report_class')">
                            <a href="{{ route('my-visit-report') }}"> My Visit Report</a>
                        </li>
                    </ul>
                </li>
                
                     @if(in_array('103', $user_accesses))
                        <li class="treeview @yield('quotation_class')">
                    <a href="#">
                        <i class="metismenu-icon pe-7s-file"></i><span> Quotations</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <!-- Add Quotation -->
                        <li class="@yield('quotations_create')">
                            <a href="{{ route('quotations.create') }}"><i class="fa fa-circle-o"></i> Add Quotation</a>
                        </li>
                
                        <!-- Manage Quotation -->
                        <li class="@yield('quotations_manage')">
                            <a href="{{ route('quotations.index') }}"><i class="fa fa-circle-o"></i> Manage Quotations</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(in_array('18', $user_accesses))
                <li class="treeview @yield('monthly_sell_class')">
                    <a href="{{ url('/monthly_summery')}}"> Monthly Sell Summery</a>
                </li>
                @endif

                @if(in_array('16', $user_accesses))
                <li class="treeview @yield('today_customer_list_class')">
                <a href="{{ url('/todays_customer_list')}}"> Customer Entry Info</a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        <!-- CRM END -->

        <!-- OMS START -->
        @if(check_or_permission([26,27,28,29,30,31,32,33,34,35,36,44,45]) == true)
        <li class="treeview @yield('oms_class')">
            <a href="#">
                <i class="fa ti-briefcase"></i><span> OMS</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                @if(check_or_permission([26,27,28,29,30,44]) == true)

                <li class="treeview @yield('conveyance_class')">
                    <a href="#">
                        <i class="fa fa-money"></i><span> Conveyance</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">

                        @if(in_array('26', $user_accesses))
                        <li class="treeview @yield('conveyance_user_class')">
                        <a href="{{ url('/oms_convence_users')}}">{{ (Auth::user()->au_user_type == 4)? 'Users' : 'My Bill Details' }}</a>
                        </li>
                        @endif

                        @if(in_array('27', $user_accesses))
                        <li class="treeview @yield('convence_add_bill_class')">
                            <a href="{{ url('/convence_add_new_bill')}}"> Add New Bill</a>
                        </li>
                        @endif

                        @if(in_array('44', $user_accesses))
                        <li class="treeview @yield('convence_my_pending_bill_class')">
                            <a href="{{ url('/convence_my_pending_bill') }}">My Pending Bill</a>
                        </li>
                        @endif

                        @if(in_array('28', $user_accesses))
                        <li class="treeview @yield('conveyance_pending_class')">
                            <a href="{{ url('/convence_pending_bill')}}">Pending Bill</a>
                        </li>
                        @endif

                        @if(in_array('29', $user_accesses))
                        <li class="treeview @yield('conveyance_reject_class')">
                            <a href="{{ url('/convence_reject_bill')}}">Reject Bill</a>
                        </li>
                        @endif

                        @if(in_array('30', $user_accesses))
                        <li class="@yield('convence_paid_bill')">
                            <a href="{{ url('/convence_paid_bill')}}"> Paid/Final Bill</a>
                        </li>
                        @endif

                    </ul>
                    
                </li>
                @endif
                
                @if(check_or_permission([31,32,33,34,35,45]) == true)
                <li class="treeview @yield('meal_class')">
                    <a href="#">
                        <i class="fa fa-paypal"></i><span> Meal</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">

                        @if(in_array('31', $user_accesses))
                        <li class="treeview @yield('meal_user_class')">
                            <a href="{{ url('/oms_lunch_users')}}"> {{ (Auth::user()->au_user_type == 4)? 'Users' : 'My Bill Details' }}</a>
                        </li>
                        @endif

                        @if(in_array('32', $user_accesses))
                        <li class="treeview @yield('lunch_add_new_bill')">
                        <a href="{{ url('/lunch_add_new_bill')}}"> Add New Bill
                        </a>
                        </li>
                        @endif

                        @if(in_array('45', $user_accesses))
                            <li class="treeview @yield('lunch_my_pending_bill')">
                        <a href="{{ url('/lunch_my_pending_bill') }}"> My Pending Bill
                        </a>
                        </li>
                        @endif

                        @if(in_array('33', $user_accesses))
                            <li class="treeview @yield('meal_pending_class')">
                        <a href="{{ url('/lunch_pending_bill')}}"> Pending Bill
                        </a>
                        </li>
                        @endif

                        @if(in_array('34', $user_accesses))
                            <li class="treeview @yield('meal_reject_class')">
                        <a href="{{ url('/lunch_reject_bill')}}"> Reject Bill
                        </a>
                        </li>
                        @endif

                        @if(in_array('35', $user_accesses))
                            <li class="treeview @yield('lunch_paid_bill')">
                        <a href="{{ url('/lunch_paid_bill')}}"> Paid/Final Bill
                        </a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                @if(in_array('36', $user_accesses))
                <li class="treeview @yield('oms_accounts_class')">
                    <a href="{{ url('/accounts')}}"> Accounts</a>
                </li>
                @endif

            </ul>
        </li>
        @endif
        <!-- OMS END -->

        <!--Start Invenory-->
        @if(check_or_permission([54,55,56,57,58,59,60,61,62,63,64,65,66,67]) == true)
            <li class="treeview @yield('inventory_class')">
                <a href="#" style="text-decoration: none;">
                <i class="ti-shopping-cart"></i> <span>Inventory</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
                </a>
                <ul class="treeview-menu">
                @if(check_or_permission([54,55,56]) == true)
                <li class="treeview @yield('product_class')">
                    <a href="#" style="text-decoration: none;">
                    <i class="metismenu-icon fa fa-cubes"></i> <span>Product</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(in_array('54',$user_accesses))
                        <li class="treeview @yield('inv_pro_grp')">
                        <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Product Group</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">

                        <li class="@yield('add_pro_grp')">
                            <a href="{{ route('inventory.add_group') }}">
                            <i class="fa fa-circle-o"></i> Add Group
                            </a>
                        </li>

                        <li class="@yield('pro_grp_list')">
                            <a href="{{ route('inventory.group_list') }}">
                            <i class="fa fa-circle-o"></i> Group List
                            </a>
                        </li>

                        </ul>
                        </li>
                        @endif
                        @if(in_array('55',$user_accesses))
                        <li class="treeview @yield('inv_pro_type')">
                        <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Product Type</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">

                        <li class="@yield('add_type')">
                            <a href="{{ route('inventory.type_add')}}">
                            <i class="fa fa-circle-o"></i> Add Type
                            </a>
                        </li>

                        <li class="@yield('type_list')">
                            <a href="{{ route('inventory.type_list')}}">
                            <i class="fa fa-circle-o"></i> Type List
                            </a>
                        </li>

                        </ul>
                        </li>
                        @endif
                        @if(in_array('56',$user_accesses))
                        <li class="treeview @yield('inv_pro_class')">
                            <a href="#" style="text-decoration: none;">
                            <i class="fa fa-dashboard"></i> <span>Product</span>
                            <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">

                            <li class="@yield('pro_add')">
                            <a href="{{ route('inventory.add_product')}}">
                                <i class="fa fa-circle-o"></i> Add Product
                            </a>
                            </li>

                            <li class="@yield('pro_list')">
                            <a href="{{ route('inventory.product_list')}}">
                                <i class="fa fa-circle-o"></i> Product List
                            </a>
                            </li>
                        </ul>
                        </li>
                        @endif
                    </ul>
                    </li>
                    @endif
                    @if(check_or_permission([57,58]) == true)
                    <li class="treeview @yield('supplier_class')">
                        <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Supplier</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            @if(in_array('57',$user_accesses))
                            <li class="treeview @yield('inv_supplier_class')">
                                <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Supplier</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                                </a>

                                <ul class="treeview-menu">

                                <li class="@yield('supplier_add')">
                                    <a href="{{ route('inventory.add') }}">
                                    <i class="fa fa-circle-o"></i> Add Supplier
                                    </a>
                                </li>


                                <li class="@yield('supplier_list')">
                                    <a href="{{ route('inventory.list') }}">
                                    <i class="fa fa-circle-o"></i> Supplier List
                                    </a>
                                </li>

                                </ul>
                                </li>
                                @endif
                        


                            {{--=================   Supplier Accounts Menu Start   ===================--}}

                        
                            @if(in_array('58',$user_accesses))
                            <li class="treeview @yield('inv_supplier_acc_class')">
                                <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Supplier Accounts</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                                </a>

                                <ul class="treeview-menu">

                                <li class="@yield('supplier_deposit_withdraw')">
                                    <a href="{{ route('inventory.supplier.accounts.deposit-withdraw') }}">
                                    <i class="fa fa-circle-o"></i> Deposit/Withdraw
                                    </a>
                                </li>
                                <li class="@yield('supplier_payment')">
                                        <a href="{{ route('inventory.supplier.accounts.payment') }}">
                                        <i class="fa fa-circle-o"></i> Payment
                                        </a>
                                    </li>

                                    <li class="@yield('supplier_today_payment')">
                                        <a href="{{ route('inventory.supplier.accounts.today-payment') }}">
                                            <i class="fa fa-circle-o"></i> Today's Payment
                                        </a>
                                        </li>

                                    <li class="@yield('supplier_payment_collection')">
                                        <a href="{{ route('inventory.supplier.accounts.payment-collection') }}">
                                        <i class="fa fa-circle-o"></i> Payment Collection
                                        </a>
                                    </li>
                                    <li class="@yield('supplier_account_statement')">
                                        <a href="{{ route('inventory.supplier.accounts.account-statement') }}">
                                        <i class="fa fa-circle-o"></i> Account Statements
                                        </a>
                                    </li>
                                    </ul>
                                    </li>
                                @endif
                            </ul>

                        {{--====================== End Supplier Accounts Menu======================--}}

                        </li>
                        @endif
                        @if(check_or_permission([59,60]) == true)
                        <li class="treeview @yield('customer_class')">
                            <a href="#" style="text-decoration: none;">
                            <i class="fa fa-dashboard"></i> <span>Customer</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                            </a>
                            <ul class="treeview-menu">
                                @if(in_array('59',$user_accesses))
                                <li class="treeview @yield('inv_customer_class')">
                                    <a href="#" style="text-decoration: none;">
                                    <i class="fa fa-dashboard"></i> <span>Customer</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                    </a>

                                    <ul class="treeview-menu">

                                    <li class="@yield('customer_add')">
                                        <a href="{{ route('customer.customer_add') }}">
                                        <i class="fa fa-circle-o"></i> Create
                                        </a>
                                    </li>


                                    <li class="@yield('customer_list')">
                                        <a href="{{ route('customer.customer_list') }}">
                                        <i class="fa fa-circle-o"></i> Customer List
                                        </a>
                                    </li>

                                    </ul>
                                    </li>
                                    @endif


                                    <!--====================== Start Customer Account Menu ==================-->
                                    @if(in_array('60',$user_accesses))
                                    <li class="treeview @yield('inv_customer_acc_class')">
                                    <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Customer Accounts</span>
                                        <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>

                                    <ul class="treeview-menu">

                                        <li class="@yield('customer_deposit_withdraw')">
                                        <a href="{{ route('customer.accounts.deposit-withdraw') }}">
                                            <i class="fa fa-circle-o"></i> Deposit/Withdraw
                                        </a>
                                        </li>


                                        <li class="@yield('customer_payment')">
                                        <a href="{{ route('customer.accounts.payment') }}">
                                            <i class="fa fa-circle-o"></i> Payment Collection
                                        </a>
                                        </li>

                                        <li class="@yield('customer_payment_refund')">
                                        <a href="{{ route('customer.accounts.payment-refund') }}">
                                            <i class="fa fa-circle-o"></i> Payment Refund
                                        </a>
                                        </li>
                                        <li class="@yield('customer_today_payment')">
                                            <a href="{{ route('customer.accounts.today-payment') }}">
                                            <i class="fa fa-circle-o"></i> Today's Payment
                                            </a>
                                        </li>
                                        <li class="@yield('customer_account_statement')">
                                        <a href="{{ route('customer.accounts.account-statement') }}">
                                            <i class="fa fa-circle-o"></i> Account Statements
                                        </a>
                                        </li>
                                    </ul>
                                    </li>
                                    @endif
                        <!--====================== Start Customer Account Menu ==================-->
                            </ul>
                            </li>
                            @endif
                            @if(check_or_permission([61,62]) == true)
                            <li class="treeview @yield('pro_inv_class')">
                            <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Product Inventory</span>
                                <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                                <ul class="treeview-menu">
                                @if(in_array('61',$user_accesses))
                                <li class="treeview @yield('inv_buy_class')">
                                    <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Purchase Product</span>
                                        <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>

                                    <ul class="treeview-menu">

                                        <li class="@yield('buy_add')">
                                        <a href="{{ route('buy.buy-product-new') }}">
                                            <i class="fa fa-circle-o"></i> Add Product
                                        </a>
                                        </li>

                                    </ul>
                                    </li>
                                    @endif
                                    @if(in_array('62',$user_accesses))
                                    <li class="@yield('sell_pro')">
                                        <a href="{{ route('buy.pro_sell') }}">
                                        <i class="fa fa-circle-o"></i> Sell Product
                                        </a>
                                    </li>
                                    @endif
                                </ul>

                            </li>
                            @endif
                            @if(check_or_permission([63,64,65,66,76]) == true)
                            <li class="treeview @yield('accounts_class')">
                                <a href="#" style="text-decoration: none;">
                                    <i class="fa fa-dashboard"></i> <span>Accounts</span>
                                    <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <!--  Bank Accounts -->

                                    <ul class="treeview-menu">
                                    @if(in_array('63',$user_accesses))
                                    <li class="treeview @yield('bank_class')">
                                        <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Bank</span>
                                            <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>

                                        <ul class="treeview-menu">
                                            <li class="@yield('bank_add')">
                                            <a href="{{ route('accounts.add-bank') }}">
                                                <i class="fa fa-circle-o"></i> Add Bank
                                            </a>
                                            </li>

                                            <li class="@yield('bank_list')">
                                            <a href="{{ route('accounts.bank-list') }}">
                                                <i class="fa fa-circle-o"></i> Bank List
                                            </a>
                                            </li>

                                            <li class="@yield('bank_deposit_withdraw')">
                                            <a href="{{ route('accounts.bank-diposit-withdraw') }}">
                                                <i class="fa fa-circle-o"></i> Diposit/Withdraw
                                            </a>
                                            </li>
                                        </ul>
                                        </li>
                                        @endif
                                    
                                    <!-- End Bank Accounts -->
                                    <!-- Start Expenses -->
                                    
                                    @if(in_array('64',$user_accesses))
                                    <li class="treeview @yield('expense_class')">
                                        <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Expense</span>
                                            <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>

                                        <ul class="treeview-menu">
                                            <li class="@yield('expense_category')">
                                            <a href="{{ route('accounts.expense-categories') }}">
                                                <i class="fa fa-circle-o"></i> Expense Categories
                                            </a>
                                            </li>

                                            <li class="@yield('expenses')">
                                            <a href="{{ route('accounts.expenses') }}">
                                                <i class="fa fa-circle-o"></i> Expenses
                                            </a>
                                            </li>
                                        </ul>
                                        </li>
                                        @endif
                                    

                                    
                                        @if(in_array('76',$user_accesses))
                                        <li class="treeview @yield('ledger_class')">
                                            <a href="#" style="text-decoration: none;">
                                                <i class="fa fa-dashboard"></i> <span>Ledger</span>
                                                <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                                </span>
                                            </a>

                                            <ul class="treeview-menu">
                                                <li class="@yield('ledger_add')">
                                                    <a href="{{ route('accounts.ledger-add') }}">
                                                    <i class="fa fa-circle-o"></i> Cash
                                                    </a>
                                                </li>
                                                <li class="@yield('ledger_category')">
                                                <a href="{{ route('accounts.ledger-category') }}">
                                                    <i class="fa fa-circle-o"></i> Ledger Categories
                                                </a>
                                                </li>

                                                <li class="@yield('ledger')">
                                                <a href="{{ route('accounts.ledger') }}">
                                                    <i class="fa fa-circle-o"></i> Ledger
                                                </a>
                                                </li>
                                            </ul>
                                            </li>
                                            @endif
                                        
                                    
                                    @if(in_array('65',$user_accesses))
                                    <li class="treeview @yield('voucher_class')">
                                        <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Voucher</span>
                                            <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>

                                        <ul class="treeview-menu">
                                            <li class="@yield('contra_create')">
                                            <a href="{{ route('accounts.create-contra') }}">
                                                <i class="fa fa-circle-o"></i> Create Contra
                                            </a>
                                            </li>

                                            <li class="@yield('contra_list')">
                                            <a href="{{ route('accounts.contra-list') }}">
                                                <i class="fa fa-circle-o"></i> Contra List
                                            </a>
                                            </li>

                                            <li class="@yield('expenses-voucher')">
                                            <a href="{{ route('accounts.expenses-voucher') }}">
                                                <i class="fa fa-circle-o"></i> Expenses Entry
                                            </a>
                                            </li>
                                        </ul>
                                        </li>
                                        @endif
                                        <!--   End Expenses -->
                                    <!--   End Contra Menu -->

                                    <!--=============  General Ledger ===============-->
                                    @if(in_array('66',$user_accesses))
                                    <li class=" @yield('ledger_class')">
                                        <a href="{{route('accounts.general_ledger')}}" style="text-decoration: none;">
                                            <i class="fa fa-circle-o"></i> <span>General Ledger</span>
                                        </a>
                                    </li>
                                    @endif
                                    <!--============= End General Ledger ===============-->
                                    @if(check_or_permission([68]) == true)
                                    @if(in_array('68',$user_accesses))
                                    <li class=" @yield('acc_state_class')">
                                        <a href="{{route('accounts.account-statement')}}" style="text-decoration: none;">
                                            <i class="fa fa-circle-o"></i> <span>Account Statement</span>
                                        </a>
                                    </li>
                                    @endif
                                    @endif
                                    </ul>
                                </li>
                                @endif
                                @if(check_or_permission([67]) == true)
                                @if(in_array('67',$user_accesses))
                                <li class="treeview @yield('report_class')">
                                    <a href="#" style="text-decoration: none;">
                                    <i class="fa fa-dashboard"></i> <span>Reports</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                    </a>
                                    <ul class="treeview-menu">
                                            <li class="@yield('sell_reports')">
                                            <a href="{{ route('reports.sell-reports') }}">
                                                <i class="fa fa-circle-o"></i> Sell Reports
                                            </a>
                                            </li>
                                            <li class="@yield('buy_reports')">
                                                <a href="{{ route('reports.buy-reports') }}">
                                                <i class="fa fa-circle-o"></i> Buy Reports
                                                </a>
                                            </li>

                                            <li class="@yield('combined_reports')">
                                                <a href="{{ route('reports.combined-reports') }}">
                                                <i class="fa fa-circle-o"></i> Combined Reports
                                                </a>
                                            </li>
                                    </ul>

                                    </li>
                                    @endif
                                    @endif
                                    @if(check_or_permission([70]) == true)
                                    @if(in_array('70',$user_accesses))
                                    <li class="treeview @yield('damage_class')">
                                    <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Damage/Crack</span>
                                        <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>
                                        <ul class="treeview-menu">
                                            <li class="@yield('add_damage')">
                                                <a href="{{ route('pro_damage.damage-add') }}">
                                                <i class="fa fa-circle-o"></i> Add
                                                </a>
                                            </li>
                                            <li class="@yield('damage_list')">
                                                <a href="{{ route('pro_damage.damage-list') }}">
                                                    <i class="fa fa-circle-o"></i> List
                                                </a>
                                            </li>
                                        </ul>

                                    </li>
                                    @endif
                                    @endif
                                    @if(check_or_permission([71]) == true)
                                    @if(in_array('70',$user_accesses))
                                    <li class="treeview @yield('return_class')">
                                    <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Return</span>
                                        <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>
                                        <ul class="treeview-menu">
                                            <li class="@yield('sale_return')">
                                                <a href="{{ route('product_return.sell-return-view') }}">
                                                <i class="fa fa-circle-o"></i> Sale Return
                                                </a>
                                            </li>
                                            <li class="@yield('buy_return')">
                                                <a href="{{ route('product_return.buy-return-view') }}">
                                                    <i class="fa fa-circle-o"></i> Buy Return
                                                </a>
                                            </li>
                                        </ul>

                                    </li>
                                    @endif
                                    @endif

                                    @if(check_or_permission([86]) == true)
                                    @if(in_array('86',$user_accesses))
                                    <li class="treeview @yield('return_class')">
                                    <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Warranty</span>
                                        <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>
                                        <ul class="treeview-menu">
                                        <li class="@yield('warranty_claim')">
                                            <a href="{{ route('inventory.warranty-claim') }}">
                                            <i class="fa fa-circle-o"></i> Warranty Claim
                                            </a>
                                        </li>
                                        </ul>

                                    </li>
                                    @endif
                                    @endif


                                    @if(check_or_permission([69]) == true)
                                    @if(in_array('69',$user_accesses))
                                    <li class="@yield('short_quantity')">
                                        <a href="{{ route('inventory.short') }}">
                                        <i class="fa fa-circle-o"></i> Short Quatity
                                        </a>
                                    </li>
                                    @endif
                                    @endif
                </ul>
            </li>
        @endif
        <!--End Invenory-->

        <!--Start SMS-->
        @if(check_or_permission([37,38,39]) == true)
            <li class="treeview @yield('sms_class')">
                    <a href="#" style="text-decoration: none;">
                    <i class="fa fa-dashboard"></i> <span>SMS</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                @if(in_array('37', $user_accesses))
                    <li class="@yield('sms_setup_class')">
                <a href="{{ url('/sms_setup')}}">
                    <i class="fa fa-circle-o"></i> SMS Setup
                </a>
                </li>
                @endif
                @if(in_array('38', $user_accesses))
                    <li class="@yield('sms_campaign_class')">
                <a href="{{ url('/sms_campaign')}}">
                    <i class="fa fa-circle-o"></i> SMS Campaign
                </a>
                </li>
                @endif
                @if(in_array('39', $user_accesses))
                    <li class="@yield('template_class')">
                <a href="{{ url('/sms_temp_edit')}}">
                    <i class="fa fa-circle-o"></i> Template
                </a>
                </li>
                @endif
                    </ul>
            </li>
        @endif
        <!-- PC Settings START -->
        <li class="treeview @yield('pc_settings_class')">
            <a href="{{ route('pc_setting.index') }}">
                <i class="fa fa-gear"></i><span> PC Settings</span>
            </a>
        </li>
        <!-- System Settings END -->


    </ul>
</div>