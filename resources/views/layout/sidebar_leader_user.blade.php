@php
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
<aside class="main-sidebar" >
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ config('app.custom_image_path') }}{{ Auth::user()->au_company_img }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ Auth::user()->au_name }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- search form -->
      <br>
      @if(check_or_permission([62]) == true)
    <a class="btn btn-info d-block buy-sell-button" href="{{ route('buy.pro_sell') }}" style="margin-bottom:3px;">Sell</a>
    @endif
    @if(check_or_permission([61]) == true)
    <a class="btn btn-danger d-block buy-sell-button" href="{{ route('buy.buy-product-new') }}">Buy</a>
    @endif
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">
        <form id="search-form" method="get" action="{{ route('search') }}">
          <div class="input-group">
              <input type="number" class="form-control" name="q" placeholder="Search Clients" id="search-input">
              <span class="input-group-btn" id="search-result">
              </span>
          </div>
        </form>
      </li>
        <li class="">
          <a href="{{ url('/index')}}" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        
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

        @if(check_or_permission([74]) == true)
        <li class="treeview @yield('agm_class')">
          <a href="#" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>Head Of Sales</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              <li class="@yield('agm_team_list')"><a href="{{ url('/hos_team_list',['id' => Auth::user()->au_id])}}"><i class="fa fa-circle-o"></i> Team Leader/Manager List</a></li>
          </ul>
        </li>
        @endif


        @if(check_or_permission([11,12,13,14,15,16,17,18,19,20,21,22,23,24,25]) == true)
        <li class="treeview @yield('sds_menu_class')">
          <a href="#" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>CRM</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            @if(check_or_permission([11,12,13,14,15]) == true)
            <li class="treeview @yield('customer_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Customer</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                @if(in_array('11', $user_accesses))
		            <li class="@yield('create_customer_class')">
                  <a href="{{ url('/create_customer')}}">
                    <i class="fa fa-circle-o"></i> Create Customer
                  </a>
                </li>
                @endif
                @if(in_array('12', $user_accesses))
		            <li class="@yield('my_customer_list_class')">
                  <a href="{{ url('/customer_list')}}">
                    <i class="fa fa-circle-o"></i> My Customer List
                  </a>
                </li>
                @endif
                @if(in_array('13', $user_accesses))
		            <li class="@yield('total_customer_list_class')">
                  <a href="{{ url('/total_customer_list')}}">
                    <i class="fa fa-circle-o"></i> Total Customer List
                  </a>
                </li>
                @endif
                @if(in_array('14', $user_accesses))
		            <li class="@yield('order_customer_class')">
                  <a href="{{ url('/order_customer')}}">
                    <i class="fa fa-circle-o"></i>Ordered Customers
                  </a>
                </li>
                @endif
                @if(in_array('15', $user_accesses))
		            <li class="@yield('admins_created_class')">
                  <a href="{{ url('/admin_customer' ,['id' => Auth::user()->au_id]) }}">
                    <i class="fa fa-circle-o"></i> Admin's Created
                  </a>
                </li>
                @endif
		          </ul>
            </li>
            @endif
            @if (Auth::user()->au_user_type != 7)
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
                  <a href="{{ url('/members_list',['id' => Auth::user()->au_team_id]) }}">
                    <i class="fa fa-circle-o"></i> Team Member List
                  </a>
                </li>
                @endif
		          </ul>
            </li>
            @endif
            @endif

            @if(check_or_permission([20,21]) == true)
            <li class="treeview @yield('followup_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Followup Customer</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                @if(in_array('20', $user_accesses))
		            <li class="@yield('follow_cus_class')">
                  <a href="{{ url('/followup')}}">
                    <i class="fa fa-circle-o"></i> Followup
                  </a>
                </li>
                @endif
                @if(in_array('21', $user_accesses))
		            <li class="@yield('nonfollow_cus_class')">
                  <a href="{{ url('/non_followup')}}">
                    <i class="fa fa-circle-o"></i> Non Followup
                  </a>
                </li>
                @endif
		        <li class="@yield('due_follow_up')">
                  <a href="{{ url('/due-follow-up')}}">
                    <i class="fa fa-circle-o"></i> Due Followup
                  </a>
                </li>
		          </ul>
            </li>
            @endif

            @if(check_or_permission([22,23,24,25]) == true)
            <li class="treeview @yield('intelligent_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Intelligence Search</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                @if(in_array('22', $user_accesses))
		            <li class="@yield('search_find_class')">
                  <a href="{{ url('/search_findus')}}">
                    <i class="fa fa-circle-o"></i> Search By Find Us
                  </a>
                </li>
                @endif
                @if(in_array('23', $user_accesses))
		            <li class="@yield('search_reason_class')">
                  <a href="{{ url('/search_reason')}}">
                    <i class="fa fa-circle-o"></i> Search By Reason
                  </a>
                </li>
                @endif
                @if(in_array('24', $user_accesses))
		            <li class="@yield('search_result_class')">
                  <a href="{{ url('/search_result')}}">
                    <i class="fa fa-circle-o"></i> Search By Result
                  </a>
                </li>
                @endif
                @if(in_array('25', $user_accesses))
		            <li class="@yield('search_feedback_class')">
                  <a href="{{ url('/search_feedback')}}">
                    <i class="fa fa-circle-o"></i> Search By Feedback
                  </a>
                </li>
                @endif
		          </ul>
            </li>
            @endif

            @if(in_array('16', $user_accesses))
            <li class="@yield('today_customer_list_class')">
              <a href="{{ url('/todays_customer_list')}}">
                <i class="fa fa-circle-o"></i> Customer Entry Info
              </a>
            </li>
            @endif
            @if(in_array('17', $user_accesses))
            <li class="@yield('weekly_summery_class')">
              <a href="{{ url('/weekly_summery') }}">
                <i class="fa fa-circle-o"></i> Weekly Summery
              </a>
            </li>
            @endif
            @if(in_array('18', $user_accesses))
            <li class="@yield('monthly_summery_class')">
              <a href="{{ url('/monthly_summery') }}">
                <i class="fa fa-circle-o"></i> Monthly Summery
              </a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        <!-- end crm -->

        <!-- Start OMS -->
        @if(check_or_permission([26,27,28,29,30,31,32,33,34,35,36,44,45]) == true)
        <li class="treeview @yield('oms_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>OMS</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                @if(check_or_permission([26,27,28,29,30,44]) == true)
		            <li class="treeview @yield('oms_convence_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>Conveyance</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>

			          <ul class="treeview-menu">
                  @if(in_array('26', $user_accesses))
			            <li class="@yield('convence_users')">
                    <a href="{{ url('/oms_convence_users')}}">
                      <i class="fa fa-circle-o"></i> {{ (Auth::user()->au_user_type == 4)? 'Users' : 'My Bill Details' }}
                    </a>
                  </li>
                  @endif
                  @if(in_array('27', $user_accesses))
			            <li class="@yield('convence_add_new_bill')">
                    <a href="{{ url('/convence_add_new_bill')}}">
                      <i class="fa fa-circle-o"></i> Add New Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('44', $user_accesses))
			            <li class="@yield('convence_my_pending_bill')">
                    <a href="{{ url('/convence_my_pending_bill') }}">
                      <i class="fa fa-circle-o"></i> My Pending Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('28', $user_accesses))
			            <li class="@yield('convence_pending_bill')">
                    <a href="{{ url('/convence_pending_bill')}}">
                      <i class="fa fa-circle-o"></i> Pending Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('29', $user_accesses))
			            <li class="@yield('convence_reject_bill')">
                    <a href="{{ url('/convence_reject_bill')}}">
                      <i class="fa fa-circle-o"></i> Reject Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('30', $user_accesses))
			            <li class="@yield('convence_paid_bill')">
                    <a href="{{ url('/convence_paid_bill')}}">
                      <i class="fa fa-circle-o"></i> Paid/Final Bill
                    </a>
                  </li>
                  @endif
			          </ul>
		            </li>
                @endif

                @if(check_or_permission([31,32,33,34,35,45]) == true)
		            <li class="treeview @yield('oms_lunch_class')">
		            	<a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>Meal</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>
			          <ul class="treeview-menu">
                  @if(in_array('31', $user_accesses))
			            <li class="@yield('lunch_users')">
                    <a href="{{ url('/oms_lunch_users')}}">
                      <i class="fa fa-circle-o"></i> {{ (Auth::user()->au_user_type == 4)? 'Users' : 'My Bill Details' }}
                    </a>
                  </li>
                  @endif
                  @if(in_array('32', $user_accesses))
			            <li class="@yield('lunch_add_new_bill')">
                    <a href="{{ url('/lunch_add_new_bill')}}">
                      <i class="fa fa-circle-o"></i> Add New Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('45', $user_accesses))
			            <li class="@yield('lunch_my_pending_bill')">
                    <a href="{{ url('/lunch_my_pending_bill') }}">
                      <i class="fa fa-circle-o"></i> My Pending Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('33', $user_accesses))
			            <li class="@yield('lunch_pending_bill')">
                    <a href="{{ url('/lunch_pending_bill')}}">
                      <i class="fa fa-circle-o"></i> Pending Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('34', $user_accesses))
			            <li class="@yield('lunch_reject_bill')">
                    <a href="{{ url('/lunch_reject_bill')}}">
                      <i class="fa fa-circle-o"></i> Reject Bill
                    </a>
                  </li>
                  @endif
                  @if(in_array('35', $user_accesses))
			            <li class="@yield('lunch_paid_bill')">
                    <a href="{{ url('/lunch_paid_bill')}}">
                      <i class="fa fa-circle-o"></i> Paid/Final Bill
                    </a>
                  </li>
                  @endif
			          </ul>
		            </li>
                @endif

                @if(in_array('36', $user_accesses))
                <li class="@yield('accounts_class')">
                  <a href="{{ url('/accounts') }}">
                    <i class="fa fa-circle-o"></i> Accounts
                  </a>
                </li>
                @endif
		          </ul>
            </li>
            @endif
            <!-- End OMS -->
            <!--start Invenory-->
            @if(check_or_permission([54,55,56,57,58,59,60,61,62,63,64,65,66,67]) == true)
        <li class="treeview @yield('inventory_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Inventory</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
                  @if(check_or_permission([54,55,56]) == true)
                  <li class="treeview @yield('product_class')">
                      <a href="#" style="text-decoration: none;">
                        <i class="fa fa-dashboard"></i> <span>Product</span>
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
                            </ul>


                               {{--=================   Supplier Accounts Menu Start   ===================--}}

                            <ul class="treeview-menu">
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
                                      </ul>
                                      <!-- End Bank Accounts -->
                                     <!-- Start Expenses -->
                                     <ul class="treeview-menu">
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
                                        </ul>

                                        <ul class="treeview-menu">
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
                                            </ul>
                                     <ul class="treeview-menu">
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

            @if(check_or_permission([82,83,84,87,91]) == true)
            <li class="treeview @yield('payroll_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-money"></i> <span>Payroll</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>
			          <ul class="treeview-menu">
                  @if(in_array('82', $user_accesses))
			            <li class="@yield('attendance_class')">
                    <a href="{{ route('payroll.attendance-show') }}">
                      <i class="fa fa-circle-o"></i> My Attendance
                    </a>
                  </li>
                  @endif
                  @if(in_array('91', $user_accesses))
			            <li class="@yield('attendance_total_class')">
                    <a href="{{ route('payroll.total-show') }}">
                      <i class="fa fa-circle-o"></i> Total Attendance
                    </a>
                  </li>
                  @endif
                  @if(in_array('87', $user_accesses))
                  <li class="@yield('today_attendance_class')">
                        <a href="{{ route('payroll.today-attendance-show') }}">
                          <i class="fa fa-circle-o"></i> Today Attendance
                        </a>
                    </li>
                    @endif
                    @if(check_or_permission([83,84]) == true)
                    <li class="treeview @yield('shift_management')">
                        <a href="#" style="text-decoration: none;">
                          <i class="fa fa-dashboard"></i> <span>Shift Management</span>
                          <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                          </span>
                        </a>
                          <ul class="treeview-menu">
                                <li class="@yield('shift_add')">
                                  <a href="{{ route('payroll.shift.add') }}">
                                    <i class="fa fa-circle-o"></i> Add Shift
                                  </a>
                                </li>
                                <li class="@yield('list_class')">
                                    <a href="{{ route('payroll.shift.list') }}">
                                      <i class="fa fa-circle-o"></i> Shift List
                                    </a>
                                </li>
                          </ul>

                      </li>
                      @endif

                    <li class="treeview @yield('leave')">
                    <a href="#" style="text-decoration: none;">
                      <i class="fa fa-dashboard"></i> <span>Leave</span>
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                      <ul class="treeview-menu">
                        <li class="@yield('leave_request')">
                          <a href="{{ route('leaveRequest') }}">
                            <i class="fa fa-circle-o"></i> Leave Request
                          </a>
                        </li>
                        <li class="@yield('leave_aplication')">
                          <a href="{{ route('leaveApplication') }}">
                            <i class="fa fa-circle-o"></i> Leave Application
                          </a>
                        </li>
                      </ul>

                  </li>
			          </ul>
		        </li>
            @endif


    </section>
    </aside>
