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
        <div class="branch-card" style="
    max-width: 200px;
    padding: 1.2rem;
    background: var(--erp-badge, var(--erp-red)); /* FelnaTech red, or the Theme Colors highlight colour */
    color: var(--erp-on-accent);
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
    margin: 2px;
">
    <div style="
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.2;
    ">
        <i class="fas fa-code-branch" style="font-size: 3.5rem;"></i>
    </div>
    <div style="
        position: relative;
        z-index: 1;
    ">
        <div style="
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        ">
            Branch:{{ auth()->user()->au_branch_name }}
        </div>
       
    </div>
</div>
    </div>

    <ul class="sidebar-menu">

        <!-- Client Search START -->
       <!-- Client Search START -->
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
        <!-- Client Search END -->

        <li class="treeview {{ (url('/') == request()->url()) ? 'active' : '' }}">
            <a href="{{ url('/') }}">
                <i class="ti-dashboard"></i>
                <span>Dashboard</span>
            </a>
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
                <li class="treeview @yield('hrm_class')">
                    <a href="#">
                        <i class="fa fa-users"></i><span>Management</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('add_employee_class')">
                            <a href="{{ url('/user_register') }}">Add Employee</a>
                        </li>                        
                        <li class="treeview @yield('manage_employee_class')">
                            <a href="{{ url('/user_list') }}">Manage Employee</a>
                        </li>
                        <li class="treeview @yield('create_team_class')">
                            <a href="{{ url('/create_team')}}">Create Team</a>
                        </li>
                        <li class="treeview @yield('team_manage_class')">
                            <a href="{{ url('/team_leaders')}}">Manage Team</a>
                        </li>
                           <li class="@yield('sell_monitoring_class')">
                        <a href="{{ route('sell.monitoring') }}">
                           Sell Monitoring
                        </a>
                      </li>
                        
                                
                                       <li class="@yield('employee_activity')">
                        <a href="{{ route('employee-activity.total-activity') }}">
                           Employee  Activity
                        </a>
                      </li>
                    <li class="@yield('employee_activity_today')">
                        <a href="{{ route('employee-activity.today-activity') }}">
                            Employee Today Activity
                        </a>
                    </li>

                        {{-- <li class="treeview @yield('shift_manage_class')">
                            <a href="#">
                                <i class="fa fa-user"></i><span>Shift Management</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview @yield('add_shift_class')">
                                    <a href="{{ route('payroll.shift.add') }}">Add Shift</a>
                                </li>
                                <li class="treeview @yield('shift_list_class')">
                                    <a href="{{ route('payroll.shift.list') }}">Shift List</a>
                                </li>
                            </ul>
                        </li> --}}
                    </ul>
                </li>
                
                <li class="treeview @yield('attendence_class')">
                    <a href="#">
                        <i class="fa fa-clock-o"></i><span>Attendance</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('today_attendance_class')">
                            <a href="{{ route('payroll.today-attendance-show') }}">Today Attendance</a>
                        </li>

                        <li class="treeview @yield('attendence_report_class')">
                            <a href="{{ route('payroll.total-show') }}">Attendance Report</a>
                        </li>
                        
                        <li class="treeview @yield('holiday_class')">
                            <a href="{{ route('payroll.office-holiday') }}">Holiday & Weekend</a>
                        </li>
                    </ul>
                </li>
                
                        <li class="treeview @yield('Create Employee Fine')">
                    <a href="#">
                        <i class="fa fa-clock-o"></i><span>Fine</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                    <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('inventory.accounts.fines.create') }}">Create Fine</a>
                        </li> 
                        <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('fines.index') }}">Fine Report</a>
                        </li>                     
                        <li class="treeview @yield('Create Employee Fine')">
                            <a href="{{ route('employee_fines.index') }}">Employee Fine</a>
                        </li>
                        

                    </ul>
                </li>
                

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
                          <li class="@yield('salary_advanced')">
                            <a href="{{ route('salary-records.salary_advanced_create') }}">Salary Advance</a>
                         
                        </li>
                        <li class="@yield('salary_generate')">
                            <a href="{{ route('salary-generate.generate') }}">Salary Generate</a>
                        </li>
                        <li class="@yield('employee_list')">
                        <a href="{{ route('payroll.employee_list') }}">
                           Employee List
                        </a>
                      </li>
                   
                      <li class="@yield('employee_dob')">
                        <a href="{{ route('payroll.employee_date_of_birth') }}">
                           Employee DOB
                        </a>
                      </li>
                     <li class="@yield('employee_bg')">
                        <a href="{{ route('payroll.employee_blood_group') }}">
                           Employee BG
                        </a>
                      </li>
                  <li class="@yield('employee_blood_donation')">
                        <a href="{{ route('payroll.blood_donates.index') }}">
                           Blood Donation
                        </a>
                      </li>
                     
                      <li class="treeview @yield('shift_management')">
                        <a href="#" style="text-decoration: none;">
                       <span>Shift Management</span>
                          <span class="pull-right-container">
                           
                          </span>
                        </a>
                          <ul class="treeview-menu">
                                <li class="@yield('shift_add')">
                                  <a href="{{ route('payroll.shift.add') }}">
                                   Add Shift
                                  </a>
                                </li>
                                <li class="@yield('list_class')">
                                    <a href="{{ route('payroll.shift.list') }}">
                                    Shift List
                                    </a>
                                </li>
                          </ul>
                      </li>
                    </ul>
                </li>
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
                <li class="treeview @yield('hrm_letters_class')">
                    <a href="#">
                        <i class="fa fa-envelope-o"></i><span>Letters</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="{{ route('hrm.letters.create') }}">Generate Letter</a></li>
                        <li><a href="{{ route('hrm.letters.index') }}">All Letters</a></li>
                        @if(\App\Services\HrmLetterRequests::ready())<li><a href="{{ route('hrm.letters.requests') }}">Letter Requests</a></li>@endif
                        <li><a href="{{ route('hrm.letters.my') }}">My Letters</a></li>
                        <li><a href="{{ route('hrm.letters.types') }}">Letter Types</a></li>
                        <li><a href="{{ route('hrm.letters.templates') }}">Templates</a></li>
                        <li><a href="{{ route('hrm.letters.letterhead') }}">Letterhead PAD</a></li>
                        <li><a href="{{ route('hrm.letters.signatories') }}">Signatories</a></li>
                        <li><a href="{{ route('hrm.letters.access') }}">Access</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <!-- HRM END -->

        <!-- CRM START -->
        <li class="treeview @yield('customer_relation_class')">
            <a href="#">
                <i class="metismenu-icon pe-7s-user"></i><span> CRM</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="treeview @yield('customer_class')">
                    <a href="#">
                        <i class="fa fa-users"></i><span> Customer</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('create_customer_class')">
                            <a href="{{ url('/create_customer')}}"> Create Customer</a>
                        </li>
                        <li class="treeview @yield('lead_form_class')">
                            <a href="{{ route('crm.lead-form') }}"> Auto Lead Form</a>
                        </li>
                        <li class="treeview @yield('my_lead_link_class')">
                            <a href="{{ route('crm.my-lead-link') }}"> My Lead Link</a>
                        </li>
                        <li class="treeview @yield('my_customer_class')">
                            <a href="{{ url('/customer_list')}}"> My Customers</a>
                        </li>
                        <li class="treeview @yield('ordered_customer_class')">
                            <a href="{{ url('/order_customer')}}"> Ordered Customers</a>
                        </li>
                        <li class="treeview @yield('total_customer_class')">
                            <a href="{{ url('/total_customer_list')}}"> Total Customers</a>
                        </li>
                        <li class="treeview @yield('customer_relation_class')">
                            <a href="{{ url('/failed-submissions')}}"> Failed Customers Try</a>
                        </li>
                        <li class="treeview @yield('intelligent_search_class')">
                            <a href="#">
                                <i class="fa fa-search"></i><span> Intelligent Search</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview @yield('search_find_class')">
                                    <a href="{{ url('/search_findus')}}"> Search By Find Us</a>
                                </li>
                                <li class="treeview @yield('search_reason_class')">
                                    <a href="{{ url('/search_reason')}}"> Search By Reason</a>
                                </li>
                                <li class="treeview @yield('search_result_class')">
                                    <a href="{{ url('/search_result')}}"> Search By Result</a>
                                </li>
                                <li class="treeview @yield('search_feedback_class')">
                                    <a href="{{ url('/search_feedback')}}"> Search By Feedback</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>


                <li class="treeview @yield('followup_class')">
                    <a href="#">
                        <i class="fa fa-list"></i><span> Followup</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('today_followup_class')">
                            <a href="{{ url('/todays_followup')}}"> Today Follow Up</a>
                        </li>
                        <li class="treeview @yield('due_followup_class')">
                            <a href="{{ url('/due-follow-up')}}"> Due Follow Up</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview @yield('visit_class')">
                    <a href="#">
                        <i class="fa ti-book"></i><span> Sales Visit</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('assign_visit_class')">
                            <a href="{{ route('sales-visit') }}"> Assign Visit</a>
                        </li>
                        <li class="treeview @yield('visit_report_class')">
                            <a href="{{ route('sales-visit-report') }}"> Visit Report</a>
                        </li>
                    </ul>
                </li>

        <!-- Quotation START -->
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

        <!-- Quotation END -->

                <li class="treeview @yield('monthly_sell_class')">
                    <a href="{{ url('/monthly_summery')}}"> Monthly Sell Summery</a>
                </li>
            </ul>
        </li>
        <!-- CRM END -->


        <!-- SALES MANAGEMENT START -->
        {{-- <li class="treeview @yield('customer_relation_class')">
            <a href="#">
                <i class="metismenu-icon pe-7s-user"></i><span> Sales Management</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="treeview @yield('customer_class')">
                    <a href="#">
                        <i class="fa fa-users"></i><span> Customer</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('create_customer_class')">
                            <a href="{{ url('/create_customer')}}"> Create Customer</a>
                        </li>
                        <li class="treeview @yield('lead_form_class')">
                            <a href="{{ route('crm.lead-form') }}"> Auto Lead Form</a>
                        </li>
                        <li class="treeview @yield('my_lead_link_class')">
                            <a href="{{ route('crm.my-lead-link') }}"> My Lead Link</a>
                        </li>
                        <li class="treeview @yield('my_customer_class')">
                            <a href="{{ url('/customer_list')}}"> My Customers</a>
                        </li>
                        <li class="treeview @yield('ordered_customer_class')">
                            <a href="{{ url('/order_customer')}}"> Ordered Customers</a>
                        </li>
                        <li class="treeview @yield('total_customer_class')">
                            <a href="{{ url('/total_customer_list')}}"> Total Customers</a>
                        </li>

                        <li class="treeview @yield('intelligent_search_class')">
                            <a href="#">
                                <i class="fa fa-search"></i><span> Intelligent Search</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview @yield('search_find_class')">
                                    <a href="{{ url('/search_findus')}}"> Search By Find Us</a>
                                </li>
                                <li class="treeview @yield('search_reason_class')">
                                    <a href="{{ url('/search_reason')}}"> Search By Reason</a>
                                </li>
                                <li class="treeview @yield('search_result_class')">
                                    <a href="{{ url('/search_result')}}"> Search By Result</a>
                                </li>
                                <li class="treeview @yield('search_feedback_class')">
                                    <a href="{{ url('/search_feedback')}}"> Search By Feedback</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="treeview @yield('today_followup_class')">
                    <a href="{{ url('/todays_followup')}}"> Today Follow Up</a>
                </li>
                <li class="treeview @yield('due_followup_class')">
                    <a href="{{ url('/due-follow-up')}}"> Due Follow Up</a>
                </li>
                <li class="treeview @yield('monthly_sell_class')">
                    <a href="{{ url('/monthly_summery')}}"> Monthly Sell Summery</a>
                </li>
            </ul>
        </li> --}}
        <!-- SALES MANAGEMENT END -->


        @if(check_or_permission([2]) == true)
        <!-- OMS START -->
        <li class="treeview @yield('oms_class')">
            <a href="#">
                <i class="fa ti-briefcase"></i><span> OMS</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="treeview @yield('conveyance_class')">
                    <a href="#">
                        <i class="fa fa-money"></i><span> Conveyance</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <!--<li class="treeview @yield('conveyance_user_class')">-->
                        <!--    <a href="{{ url('/oms_convence_users')}}">Users</a>-->
                        <!--</li>                        -->
                        <li class="treeview @yield('conveyance_pending_class')">
                            <a href="{{ url('/convence_pending_bill')}}">Pending Bill</a>
                        </li>
                        <li class="treeview @yield('conveyance_reject_class')">
                            <a href="{{ url('/convence_reject_bill')}}">Reject Bill</a>
                        </li>
                    </ul>
                </li>
                
                <li class="treeview @yield('meal_class')">
                    <a href="#">
                        <i class="fa fa-paypal"></i><span> Meal</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('meal_user_class')">
                            <a href="{{ url('/oms_lunch_users')}}"> Users</a>
                        </li>                        
                        <li class="treeview @yield('meal_pending_class')">
                            <a href="{{ url('/lunch_pending_bill')}}"> Pending Bill</a>
                        </li>
                        <li class="treeview @yield('meal_reject_class')">
                            <a href="{{ url('/lunch_reject_bill')}}"> Reject Bill</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview @yield('office_lunch_class')">
                    <a href="#">
                        <i class="fa fa-user-secret"></i><span> Office Lunch</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('today_lunch_class')">
                            <a href="{{ route('today_office_lunch')}}"> Today Lunch</a>
                        </li>
                        <li class="treeview @yield('lunch_user_class')">
                            <a href="{{ route('lunch-user')}}"> Lunch User</a>
                        </li>
                    </ul>
                </li>


                <li class="treeview @yield('sell_comission_class')">
                    <a href="{{ route('comission.sell-comission') }}"> Sell Commission</a>
                </li>
                <li class="treeview @yield('holiday_class')">
                    <a href="{{ url('/accounts')}}"> Accounts</a>
                </li>

            </ul>
        </li>
        <!-- OMS END -->
        @endif
        
               @if(check_or_permission([7]) == true)
              <li class="treeview @yield('projects_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-dashboard"></i> <span>Projects</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
                <ul class="treeview-menu">
                      <li class="@yield('add_projects')">
                        <a href="{{ route('projects.add-project') }}">
                          <i class="fa fa-circle-o"></i> Add Projects
                        </a>
                      </li>
                      <li class="@yield('pay_projects')">
                        <a href="{{ route('projects.payments-project') }}">
                          <i class="fa fa-circle-o"></i> Projects Payments
                        </a>
                      </li>
                      <li class="@yield('details_projects')">
                          <a href="{{ route('projects.details-project') }}">
                            <i class="fa fa-circle-o"></i> Projects Details
                          </a>
                      </li>

                </ul>
              </li>

              

              @endif

        <!-- Inventory START -->
        @if(check_or_permission([4]) == true)
        
        <li class="treeview @hasSection('accounts_class')@else @yield('inventory_class')@endif">
            <a href="#">
                <i class="ti-shopping-cart"></i><span> Inventory</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
		          <ul class="treeview-menu">
                  <li class="treeview @yield('product_class')">
                      <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Product</span>
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                      </a>
                        <ul class="treeview-menu">

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
                            {{-- <li class="@yield('list_sup_pro')">
                                <a href="{{ route('inventory.pro_list') }}">
                                  <i class="fa fa-circle-o"></i>Supplier Product List
                                </a>
                              </li> --}}
                        </ul>
                      </li>

                      <li class="treeview @yield('supplier_class')">
                          <a href="#" style="text-decoration: none;">
                            <i class="fa fa-dashboard"></i> <span>Supplier</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                          </a>
                            <ul class="treeview-menu">
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

                                  {{-- <li class="treeview @yield('supplier_product')">
                                      <a href="#" style="text-decoration: none;">
                                      <i class="fa fa-dashboard"></i> <span>Supplier Product</span>
                                      <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                      </span>
                                    </a>
                                    <ul class="treeview-menu">

                                      <li class="@yield('add_sup_pro')">
                                        <a href="{{ route('inventory.pro_add')}}">
                                          <i class="fa fa-circle-o"></i> Add Product
                                        </a>
                                      </li>



                                    </ul>
                                    </li> --}}
                            


                               {{--=================   Supplier Accounts Menu Start   ===================--}}

                            
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
                                </ul>

                          {{--====================== End Supplier Accounts Menu======================--}}

                          </li>




                          <li class="treeview @yield('customer_class')">
                              <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Customer</span>
                                <span class="pull-right-container">
                                  <i class="fa fa-angle-left pull-right"></i>
                                </span>
                              </a>
                                <ul class="treeview-menu">
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


                                      <!--====================== Start Customer Account Menu ==================-->

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
                                          <li class="@yield('customer_account_statement')">
                                            <a href="{{ route('customer.accounts.account-statement') }}">
                                              <i class="fa fa-circle-o"></i> Account Statements
                                            </a>
                                          </li>
                                        </ul>
                                        </li>
                          <!--====================== Start Customer Account Menu ==================-->
                                </ul>
                              </li>

                              <li class="treeview @yield('pro_inv_class')">
                                <a href="#" style="text-decoration: none;">
                                  <i class="fa fa-dashboard"></i> <span>Product Inventory</span>
                                  <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                  </span>
                                </a>
                                  <ul class="treeview-menu">
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
 <li class="@yield('purchase_list')">
                                                    <a href="{{ route('buy.purchase.list') }}">
                                                        <i class="fa fa-circle-o"></i> Purchase List
                                                    </a>
                                          </li>


                                          {{-- <li class="@yield('available_list')">
                                            <a href="{{ route('reports.buy_pdf') }}">
                                              <i class="fa fa-circle-o"></i> Available Product
                                            </a>
                                          </li> --}}

                                        </ul>
                                        </li>

                                        <li class="@yield('sell_pro')">
                                          <a href="{{ route('buy.pro_sell') }}">
                                            <i class="fa fa-circle-o"></i> Sell Product
                                          </a>
                                        </li>
                                  </ul>

                                </li>



                                    <li class="treeview @yield('asset_class')">
    <a href="#">
        <i class="fa fa-cubes"></i> <span>Fixed Assets</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu">
        <li class="@yield('asset_list')">
            <a href="{{ route('assets.index') }}">
                <i class="fa fa-circle-o"></i> Asset List
            </a>
        </li>
        <li class="@yield('asset_add')">
            <a href="{{ route('assets.create') }}">
                <i class="fa fa-circle-o"></i> Add Asset
            </a>
        </li>
        <li class="@yield('asset_categories')">
            <a href="{{ route('assets.categories') }}">
                <i class="fa fa-circle-o"></i> Categories
            </a>
        </li>
    </ul>
</li>


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
        {{-- <li class="@yield('sell_reports_confirm')">
            <a href="{{ route('reports.sell-confirm_reports') }}">
                <i class="fa fa-circle-o"></i> Confirm Sell Reports
            </a>
        </li>
        <li class="@yield('buy_reports_confirm')">
            <a href="{{ route('reports.buy-report-confirm') }}">
                <i class="fa fa-circle-o"></i> Confirm Buy Reports
            </a>
        </li> --}}
        <li class="@yield('combined_reports')">
            <a href="{{ route('reports.combined-reports') }}">
                <i class="fa fa-circle-o"></i> Combined Reports
            </a>
        </li>
        <!-- ===== STOCK REPORT ADDED HERE ===== -->
         <li class="@yield('stock_report')">
            <a href="{{ route('reports.stock.report') }}">
                <i class="fa fa-cubes"></i> Stock Report
            </a>
        </li>
    </ul>
</li>

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


                                        <li class="treeview @yield('warranty_claim_class')">
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


                                        <li class="treeview @yield('profit_loss')">
                                          <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Profit/Loss</span>
                                            <span class="pull-right-container">
                                              <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                          </a>
                                            <ul class="treeview-menu">
                                                  <li class="@yield('profit_class')">
                                                    <a href="{{ route('profit_loss.profit') }}">
                                                      <i class="fa fa-circle-o"></i> Profit Statement
                                                    </a>
                                                  </li>
                                                  <li class="@yield('loss_class')">
                                                      <a href="{{ route('profit_loss.loss') }}">
                                                        <i class="fa fa-circle-o"></i> Loss Statement
                                                      </a>
                                                  </li>
                                            </ul>

                                        </li>
                                      <li class="@yield('short_quantity')">
                                          <a href="{{ route('inventory.short') }}">
                                            <i class="fa fa-circle-o"></i> Short Quatity
                                          </a>
                                      </li>



		          </ul>
            </li>
            @endif

        <!-- Accounts (main menu) START -->
        @if(check_or_permission([4]) == true)
        <li class="treeview @hasSection('money_class')@else @yield('accounts_class')@endif">
    <a href="#" style="text-decoration: none;">
      <i class="fa fa-calculator"></i> <span>Accounts</span>
      <span class="pull-right-container">
        <i class="fa fa-angle-left pull-right"></i>
      </span>
    </a>
   <!--  Bank Accounts -->

      <ul class="treeview-menu">
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

              <li class="@yield('cheques_class')">
                <a href="{{ route('accounts.cheques') }}">
                  <i class="fa fa-circle-o"></i> Cheque Register
                </a>
              </li>

              <li class="@yield('bank_recon_class')">
                <a href="{{ route('accounts.bank-reconciliation') }}">
                  <i class="fa fa-circle-o"></i> Bank Reconciliation
                </a>
              </li>

              <li class="@yield('petty_cash_class')">
                <a href="{{ route('accounts.petty-cash') }}">
                  <i class="fa fa-circle-o"></i> Petty Cash
                </a>
              </li>

              {{-- ============ Bank Loan Submenu ============ --}}
              <li class="treeview @yield('bank_loan_class')">
                <a href="#" style="text-decoration: none;">
                  <i class="fa fa-circle-o"></i> <span>Loan</span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>

                <ul class="treeview-menu">
                  <li class="@yield('bank_loan_add')">
                    <a href="{{ route('accounts.bank-loan.create') }}">
                      <i class="fa fa-circle-o"></i> Add Loan
                    </a>
                  </li>

                  <li class="@yield('bank_loan_list')">
                    <a href="{{ route('accounts.bank-loan.list') }}">
                      <i class="fa fa-circle-o"></i> Loan List
                    </a>
                  </li>

                  <li class="@yield('bank_loan_report')">
                    <a href="{{ route('accounts.bank-loan.report') }}">
                      <i class="fa fa-circle-o"></i> Loan Report
                    </a>
                  </li>
                </ul>
              </li>
              {{-- ============ End Bank Loan Submenu ============ --}}

            </ul>
            </li>
      <!-- End Bank Accounts (Bank, Expense, Ledger and Voucher share one list, so opening Accounts shows all of them) -->
     <!-- Start Expenses -->
        <li class="treeview @yield('expense_class')">
            <a href="#" style="text-decoration: none;">
              <i class="fa fa-dashboard"></i> <span>Expense</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>

            <ul class="treeview-menu">
              <li class="@yield('expense_add')">
                  <a href="{{ route('accounts.cost-add') }}">
                    <i class="fa fa-circle-o"></i> Add
                  </a>
              </li>
              <li class="@yield('expenses-voucher-list')">
                  <a href="{{ route('accounts.expenses-voucher-list') }}">
                    <i class="fa fa-circle-o"></i> Expense List
                  </a>
              </li>
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

              <li class="@yield('salary_payment_class')">
                <a href="{{ route('accounts.salary-payment') }}">
                  <i class="fa fa-circle-o"></i> Salary Payment
                </a>
              </li>
            </ul>
          </li>

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
          <!--   End Expenses -->
          <!-- Strat Contra Menu -->
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
                 <li class="@yield('expenses-voucher-list')">
                  <a href="{{ route('accounts.expenses-voucher-list') }}">
                    <i class="fa fa-circle-o"></i> Expenses List
                  </a>
                </li>
            </ul>
          <!--   End Expenses -->
        <!--   End Contra Menu -->
            </li>
        <li class=" @yield('ledger_class')">
            <a href="{{route('accounts.general_ledger')}}" style="text-decoration: none;">
              <i class="fa fa-circle-o"></i> <span>General Ledger</span>
            </a>
          </li>
          <li class=" @yield('acc_state_class')">
              <a href="{{route('accounts.account-statement')}}" style="text-decoration: none;">
                <i class="fa fa-circle-o"></i> <span>Account Statement</span>
              </a>
          </li>
          <li class="treeview @yield('accounts_reports_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-bar-chart"></i> <span>Reports</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{ route('accounts.reports.dashboard') }}">Accounts Dashboard</a></li>
                <li><a href="{{ route('accounts.reports.profit-loss') }}">Profit &amp; Loss</a></li>
                <li><a href="{{ route('accounts.reports.day-book') }}">Day Book</a></li>
                <li><a href="{{ route('accounts.reports.cash-book') }}">Cash &amp; Bank Book</a></li>
                <li><a href="{{ route('accounts.reports.aging') }}">Due Aging</a></li>
                <li><a href="{{ route('accounts.reports.expenses') }}">Expense Analysis</a></li>
                <li><a href="{{ route('accounts.reports.depreciation') }}">Depreciation</a></li>
              </ul>
          </li>
          <li class="treeview @yield('accounts_journal_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-book"></i> <span>Journal &amp; Books</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{ route('accounts.journal.coa') }}">Chart of Accounts</a></li>
                <li><a href="{{ route('accounts.journal.vouchers') }}">Journal Vouchers</a></li>
                <li><a href="{{ route('accounts.journal.book') }}">Journal Book</a></li>
                <li><a href="{{ route('accounts.journal.ledger') }}">Account Ledger</a></li>
                <li><a href="{{ route('accounts.journal.trial-balance') }}">Trial Balance</a></li>
                <li><a href="{{ route('accounts.journal.balance-sheet') }}">Balance Sheet</a></li>
                <li><a href="{{ route('accounts.control.cash-flow') }}">Cash Flow</a></li>
              </ul>
          </li>
          <li class="treeview @yield('accounts_control_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-percent"></i> <span>Tax &amp; Year End</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
              </a>
              <ul class="treeview-menu">
                <li><a href="{{ route('accounts.control.vat') }}">VAT</a></li>
                <li><a href="{{ route('accounts.control.budgets') }}">Budgets</a></li>
                <li><a href="{{ route('accounts.control.fiscal-years') }}">Fiscal Years</a></li>
                <li><a href="{{ route('accounts.control.audit') }}">Audit Trail</a></li>
                <li><a href="{{ route('accounts.control.settings') }}">Accounts Settings</a></li>
              </ul>
          </li>
      </ul>
    </li>
        @endif
        <!-- Accounts (main menu) END -->

            @if(check_or_permission([5]) == true)
            <li class="treeview @yield('money_class')">
	            <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Money Exchange</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                  <li class="treeview @yield('currency_class')">
                      <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Currency</span>
                        <span class="pull-right-container">
                          <i class="fa fa-angle-left pull-right"></i>
                        </span>
                      </a>
                        <ul class="treeview-menu">

                          <li class="treeview @yield('country')">
                            <a href="#" style="text-decoration: none;">
                            <i class="fa fa-dashboard"></i> <span>Country</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                          </a>
                          <ul class="treeview-menu">

                            {{-- <li class="@yield('add_pro_grp')">
                              <a href="#">
                                <i class="fa fa-circle-o"></i> Add Country
                              </a>
                            </li> --}}

                            <li class="@yield('country_list')">
                              <a href="{{ route('money-exchange.others.country-list') }}">
                                <i class="fa fa-circle-o"></i> Country List
                              </a>
                            </li>

                          </ul>
                          </li>

                          {{-- <li class="treeview @yield('inv_pro_type')">
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
                          </li> --}}

                          <li class="treeview @yield('currencies_class')">
                              <a href="#" style="text-decoration: none;">
                              <i class="fa fa-dashboard"></i> <span>Currency</span>
                              <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                              </span>
                            </a>
                            <ul class="treeview-menu">

                              {{-- <li class="@yield('currency_add')">
                                <a href="#">
                                  <i class="fa fa-circle-o"></i> Add Currency
                                </a>
                              </li> --}}

                              <li class="@yield('currency_list')">
                                <a href="{{ route('money-exchange.others.currencies-list') }}">
                                  <i class="fa fa-circle-o"></i> Currency List
                                </a>
                              </li>

                            </ul>
                            </li>

                        </ul>
                      </li>

                      {{-- <li class="treeview @yield('supplier_class')">
                          <a href="#" style="text-decoration: none;">
                            <i class="fa fa-dashboard"></i> <span>Supplier</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                          </a>
                            <ul class="treeview-menu">
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


                            </ul> --}}


                               {{--=================   Supplier Accounts Menu Start   ===================--}}

                            {{-- <ul class="treeview-menu">
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
                                </ul>


                          </li> --}}
                          {{--====================== End Supplier Accounts Menu======================--}}




                          <li class="treeview @yield('customer_class')">
                              <a href="#" style="text-decoration: none;">
                                <i class="fa fa-dashboard"></i> <span>Customer</span>
                                <span class="pull-right-container">
                                  <i class="fa fa-angle-left pull-right"></i>
                                </span>
                              </a>
                                <ul class="treeview-menu">
                                  <li class="treeview @yield('mex_customer_class')">
                                      <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Customer</span>
                                        <span class="pull-right-container">
                                          <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                      </a>

                                      <ul class="treeview-menu">

                                        <li class="@yield('customer_add')">
                                          <a href="{{ route('money-exchange.customer.create') }}">
                                            <i class="fa fa-circle-o"></i> Create
                                          </a>
                                        </li>


                                        <li class="@yield('customer_list')">
                                          <a href="{{ route('money-exchange.customer.customer-list') }}">
                                            <i class="fa fa-circle-o"></i> Customer List
                                          </a>
                                        </li>

                                      </ul>
                                      </li>



                                      <li class="treeview @yield('inv_customer_acc_class')">
                                        <a href="#" style="text-decoration: none;">
                                          <i class="fa fa-dashboard"></i> <span>Customer Accounts</span>
                                          <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                        </a>

                                        <ul class="treeview-menu">

                                          <li class="@yield('customer_deposit_withdraw')">
                                            <a href="#">
                                              <i class="fa fa-circle-o"></i> Deposit/Withdraw
                                            </a>
                                          </li>


                                          <li class="@yield('customer_payment')">
                                            <a href="#">
                                              <i class="fa fa-circle-o"></i> Payment Collection
                                            </a>
                                          </li>

                                          <li class="@yield('customer_payment_refund')">
                                            <a href="#">
                                              <i class="fa fa-circle-o"></i> Payment Refund
                                            </a>
                                          </li>
                                          <li class="@yield('customer_account_statement')">
                                            <a href="#">
                                              <i class="fa fa-circle-o"></i> Account Statements
                                            </a>
                                          </li>
                                        </ul>
                                        </li>
                                </ul>
                              </li>

                              <li class="treeview @yield('curr_inv_class')">
                                <a href="#" style="text-decoration: none;">
                                  <i class="fa fa-dashboard"></i> <span>Currency Inventory</span>
                                  <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                  </span>
                                </a>
                                  <ul class="treeview-menu">
                                    <li class="treeview @yield('curr_buy_class')">
                                        <a href="#" style="text-decoration: none;">
                                          <i class="fa fa-dashboard"></i> <span>Purchase Currency</span>
                                          <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                          </span>
                                        </a>

                                        <ul class="treeview-menu">

                                          <li class="@yield('curr_add')">
                                            <a href="{{ route('money-exchange.currency-inventory.currency-buy') }}">
                                              <i class="fa fa-circle-o"></i> Add Currency
                                            </a>
                                          </li>




                                        </ul>
                                        </li>

                                        <li class="@yield('sell_curr')">
                                          <a href="{{ route('money-exchange.currency-inventory.currency-sell') }}">
                                            <i class="fa fa-circle-o"></i> Sell Currency
                                          </a>
                                        </li>
                                  </ul>

                                </li>



                                <li class="treeview @yield('accounts_class')">
                                    <a href="#" style="text-decoration: none;">
                                      <i class="fa fa-dashboard"></i> <span>Accounts</span>
                                      <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                      </span>
                                    </a>
                                   <!--  Bank Accounts -->

                                      <ul class="treeview-menu">
                                        <li class="treeview @yield('bank_class')">
                                            <a href="#" style="text-decoration: none;">
                                              <i class="fa fa-dashboard"></i> <span>Bank</span>
                                              <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                              </span>
                                            </a>

                                            <ul class="treeview-menu">
                                              <li class="@yield('bank_add')">
                                                <a href="{{ route('money-exchange.accounts.add-bank') }}">
                                                  <i class="fa fa-circle-o"></i> Add Bank
                                                </a>
                                              </li>

                                              <li class="@yield('bank_list')">
                                                <a href="{{ route('money-exchange.accounts.bank-list') }}">
                                                  <i class="fa fa-circle-o"></i> Bank List
                                                </a>
                                              </li>

                                              <li class="@yield('bank_deposit_withdraw')">
                                                <a href="{{ route('money-exchange.accounts.bank-diposit-withdraw') }}">
                                                  <i class="fa fa-circle-o"></i> Diposit/Withdraw
                                                </a>
                                              </li>
                                            </ul>
                                            </li>
                                      
                                      <!-- End Bank Accounts -->
                                     <!-- Start Expenses -->
                                     
                                        <li class="treeview @yield('expense_class')">
                                            <a href="#" style="text-decoration: none;">
                                              <i class="fa fa-dashboard"></i> <span>Expense</span>
                                              <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                              </span>
                                            </a>

                                            <ul class="treeview-menu">
                                              <li class="@yield('expense_add')">
                                                  <a href="{{ route('money-exchange.accounts.cost-add') }}">
                                                    <i class="fa fa-circle-o"></i> Add
                                                  </a>
                                              </li>
                                              <li class="@yield('expense_category')">
                                                <a href="{{ route('money-exchange.accounts.expense-categories') }}">
                                                  <i class="fa fa-circle-o"></i> Expense Categories
                                                </a>
                                              </li>

                                              <li class="@yield('expenses')">
                                                <a href="{{ route('money-exchange.accounts.expenses') }}">
                                                  <i class="fa fa-circle-o"></i> Expenses
                                                </a>
                                              </li>
                                            </ul>
                                          </li>
                                        

                                        
                                            <li class="treeview @yield('ledger_class')">
                                                <a href="#" style="text-decoration: none;">
                                                  <i class="fa fa-dashboard"></i> <span>Ledger</span>
                                                  <span class="pull-right-container">
                                                    <i class="fa fa-angle-left pull-right"></i>
                                                  </span>
                                                </a>

                                                <ul class="treeview-menu">
                                                  <li class="@yield('ledger_add')">
                                                      <a href="{{ route('money-exchange.accounts.ledger-add') }}">
                                                        <i class="fa fa-circle-o"></i> Cash
                                                      </a>
                                                  </li>

                                                  <li class="@yield('ledger_category')">
                                                    <a href="{{ route('money-exchange.accounts.ledger-category') }}">
                                                      <i class="fa fa-circle-o"></i> Ledger Categories
                                                    </a>
                                                  </li>

                                                  <li class="@yield('ledger')">
                                                    <a href="{{ route('money-exchange.accounts.ledger') }}">
                                                      <i class="fa fa-circle-o"></i> Ledger
                                                    </a>
                                                  </li>
                                                </ul>
                                              </li>
                                            
                                          <!--   End Expenses -->
                                          <!-- Strat Contra Menu -->
                                          <!-- Start Expenses -->
                                     
                                        <li class="treeview @yield('voucher_class')">
                                            <a href="#" style="text-decoration: none;">
                                              <i class="fa fa-dashboard"></i> <span>Voucher</span>
                                              <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                              </span>
                                            </a>

                                            <ul class="treeview-menu">
                                              <li class="@yield('contra_create')">
                                                <a href="{{ route('money-exchange.accounts.create-contra') }}">
                                                  <i class="fa fa-circle-o"></i> Create Contra
                                                </a>
                                              </li>

                                              <li class="@yield('contra_list')">
                                                <a href="{{ route('money-exchange.accounts.contra-list') }}">
                                                  <i class="fa fa-circle-o"></i> Contra List
                                                </a>
                                              </li>

                                              <li class="@yield('expenses-voucher')">
                                                  <a href="{{ route('money-exchange.accounts.expenses-voucher') }}">
                                                    <i class="fa fa-circle-o"></i> Expenses Entry
                                                  </a>
                                                </li>
                                            </ul>

                                            </li>
                                        <li class=" @yield('general_ledger')">
                                            <a href="{{ route('money-exchange.accounts.general_ledger') }}" style="text-decoration: none;">
                                              <i class="fa fa-circle-o"></i> <span>General Ledger</span>
                                            </a>
                                          </li>
                                          <li class=" @yield('acc_state_class')">
                                              <a href="{{ route('money-exchange.accounts.account-statement') }}" style="text-decoration: none;">
                                                <i class="fa fa-circle-o"></i> <span>Account Statement</span>
                                              </a>
                                          </li>
                                      </ul>
                                    </li>


                                    <li class="treeview @yield('report_class')">
                                      <a href="#" style="text-decoration: none;">
                                        <i class="fa fa-dashboard"></i> <span>Reports</span>
                                        <span class="pull-right-container">
                                          <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                      </a>
                                        <ul class="treeview-menu">
                                              <li class="@yield('sell_reports')">
                                                <a href="{{ route('money-exchange.reports.sell-reports') }}">
                                                  <i class="fa fa-circle-o"></i> Sell Reports
                                                </a>
                                              </li>
                                              <li class="@yield('buy_reports')">
                                                  <a href="{{ route('money-exchange.reports.buy-reports') }}">
                                                    <i class="fa fa-circle-o"></i> Buy Reports
                                                  </a>
                                              </li>

                                              <li class="@yield('combined_reports')">
                                                  <a href="{{ route('money-exchange.reports.combined-reports') }}">
                                                    <i class="fa fa-circle-o"></i> Combined Reports
                                                  </a>
                                              </li>
                                        </ul>
                                      </li>

                                      <li class="treeview @yield('damage_class')">
                                          <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Damage/Crack</span>
                                            <span class="pull-right-container">
                                              <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                          </a>
                                            <ul class="treeview-menu">
                                                  <li class="@yield('add_damage')">
                                                    <a href="{{ route('money-exchange.damage.damage-add') }}">
                                                      <i class="fa fa-circle-o"></i> Add
                                                    </a>
                                                  </li>
                                                  <li class="@yield('damage_list')">
                                                      <a href="{{ route('money-exchange.damage.damage-list') }}">
                                                        <i class="fa fa-circle-o"></i> List
                                                      </a>
                                                  </li>
                                            </ul>

                                        </li>
                                        <li class="treeview @yield('return_class')">
                                          <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Return</span>
                                            <span class="pull-right-container">
                                              <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                          </a>
                                            <ul class="treeview-menu">
                                                  <li class="@yield('sale_return')">
                                                    <a href="#">
                                                      <i class="fa fa-circle-o"></i> Sale Return
                                                    </a>
                                                  </li>
                                                  <li class="@yield('buy_return')">
                                                      <a href="#">
                                                        <i class="fa fa-circle-o"></i> Buy Return
                                                      </a>
                                                  </li>
                                            </ul>

                                        </li>
                                        <li class="treeview @yield('profit_loss')">
                                          <a href="#" style="text-decoration: none;">
                                            <i class="fa fa-dashboard"></i> <span>Profit/Loss</span>
                                            <span class="pull-right-container">
                                              <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                          </a>
                                            <ul class="treeview-menu">
                                                  <li class="@yield('profit_class')">
                                                    <a href="{{ route('money-exchange.profit-loss.profit') }}">
                                                      <i class="fa fa-circle-o"></i> Profit Statement
                                                    </a>
                                                  </li>
                                                  <li class="@yield('loss_class')">
                                                      <a href="{{ route('money-exchange.profit-loss.loss') }}">
                                                        <i class="fa fa-circle-o"></i> Loss Statement
                                                      </a>
                                                  </li>
                                            </ul>

                                        </li>
                                      <li class="@yield('short_quantity')">
                                          <a href="#">
                                            <i class="fa fa-circle-o"></i> Short Quatity
                                          </a>
                                      </li>


		          </ul>
            
            
        </li>
        @endif
        <!-- Accounts END -->

        <!-- LC START -->
        <li class="treeview @yield('lc_class')">
            <a href="#">
                <i class="fa ti-bar-chart"></i><span> LC Management</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">

                <li class="treeview @yield('lc_file_class')">
                    <a href="#">
                        <i class="fa ti-book"></i><span> File Opening</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('lc_file_add_class')">
                            <a href="{{ route('lc.files.create') }}"> Add File</a>
                        </li>
                        <li class="@yield('lc_file_list_class')">
                            <a href="{{ route('lc.files.index') }}"> File Opening Details</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview @yield('lcm_class')">
                    <a href="#">
                        <i class="fa ti-dashboard"></i><span> LCM</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('lc_opening_class')">
                            <a href="{{ route('lc.opening') }}"> LC Opening</a>
                        </li>
                        <li class="@yield('lc_statement_class')">
                            <a href="{{ route('lc.statement') }}"> LC Statement</a>
                        </li>
                        <li class="@yield('lc_documents_class')">
                            <a href="{{ route('lc.documents') }}"> LC Documents</a>
                        </li>
                        <li class="@yield('lc_summary_class')">
                            <a href="{{ route('lc.summary') }}"> LC Summary</a>
                        </li>
                    </ul>
                </li>

                <li class="treeview @yield('lc_shipment_class')">
                    <a href="#">
                        <i class="fa ti-shopping-cart"></i><span> Shipment</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="@yield('lc_shipment_add_class')">
                            <a href="{{ route('lc.shipments.create') }}"> Add Shipment</a>
                        </li>
                        <li class="@yield('lc_shipment_list_class')">
                            <a href="{{ route('lc.shipments.index') }}"> Shipment Details</a>
                        </li>
                    </ul>
                </li>

                <li class="@yield('lc_debitors_class')">
                    <a href="{{ route('lc.debitors') }}"> Debitors Balance</a>
                </li>

                <li class="@yield('lc_settings_class')">
                    <a href="{{ route('lc.currencies') }}"> Currency Settings</a>
                </li>

            </ul>
        </li>
        <!-- LC END -->

        <!-- Report START -->
        <li class="treeview @yield('report_class')">
            <a href="#">
                <i class="ti-book"></i><span> Report</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                <li class="treeview @yield('customer_class')">
                    <a href="#">
                        <i class="metismenu-icon pe-7s-user"></i><span> Customer Relation</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="treeview @yield('add_employee_class')">
                            <a href="{{ url('/user_register') }}"> Weekly Summery</a>
                        </li>
                        <li class="treeview @yield('manage_employee_class')">
                            <a href="{{ url('/user_list') }}"> Monthly Summery</a>
                        </li>
                    </ul>
                </li>
                <li class="treeview @yield('today_attendance_class')">
                    <a href="{{ route('payroll.today-attendance-show') }}"> Purchase Report</a>
                </li>
                <li class="treeview @yield('sales_report_class')">
                    <a href="{{ route('reports.sell-reports') }}"> Sales Report</a>
                </li>
                <li class="treeview @yield('today_attendance_class')">
                    <a href="{{ route('payroll.today-attendance-show') }}"> Due Report</a>
                </li>
                <li class="treeview @yield('today_attendance_class')">
                    <a href="{{ route('payroll.today-attendance-show') }}"> TAX Report</a>
                </li>
                <li class="treeview @yield('today_attendance_class')">
                    <a href="{{ route('payroll.today-attendance-show') }}"> Profit/Loss Report</a>
                </li>
                  
                <li class="treeview @yield('today_attendance_class')">
                    <a href="{{ route('payroll.today-attendance-show') }}"> Closing Report</a>
                </li>
            </ul>
        </li>
        <!-- Report END -->

        <!-- SMS START -->
      <li class="treeview @yield('sms_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-comment"></i> <span>SMS</span>
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
        <!-- SMS END -->

        <!-- Quotation START -->
        <!--<li class="treeview @yield('quotation_class')">-->
        <!--    <a href="#">-->
        <!--        <i class="metismenu-icon pe-7s-user"></i><span> Quotation</span>-->
        <!--        <span class="pull-right-container">-->
        <!--            <i class="fa fa-angle-left pull-right"></i>-->
        <!--        </span>-->
        <!--    </a>-->
        <!--    <ul class="treeview-menu">-->
        <!--        <li class="treeview @yield('due_followup_class')">-->
        <!--            <a href="{{ route('payroll.today-attendance-show') }}"> Add Quotation</a>-->
        <!--        </li>-->
        <!--        <li class="treeview @yield('due_followup_class')">-->
        <!--            <a href="{{ route('payroll.today-attendance-show') }}"> Manage Quotation</a>-->
        <!--        </li>-->
        <!--    </ul>-->
        <!--</li>-->
        <!-- Quotation END -->

        <!-- System Settings START -->
        <li class="treeview @yield('system_settings_class')">
            <a href="{{ route('system-settings') }}">
                <i class="fa fa-gear"></i><span> System Settings</span>
            </a>
        </li>

        <li class="treeview @yield('theme_settings_class')">
            <a href="{{ route('theme-settings') }}">
                <i class="fa fa-paint-brush"></i><span> Theme Colors</span>
            </a>
        </li>

         <li class="treeview @yield('pc_settings_class')">
            <a href="{{ route('pc_setting.index') }}">
                <i class="fa fa-gear"></i><span> PC Settings</span>
            </a>
        </li>
        <!-- System Settings END -->

    </ul>
</div>