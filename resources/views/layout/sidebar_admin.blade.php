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
      @if(check_or_permission([4]) == true)
      <a class="btn btn-info d-block buy-sell-button" href="{{ route('buy.pro_sell') }}" style="margin-bottom:3px;">Sell</a>
      <a class="btn btn-danger d-block buy-sell-button" href="{{ route('buy.buy-product-new') }}">Buy</a>
      @endif
      @if(check_or_permission([5]) == true)
      <a class="btn btn-info d-block buy-sell-button" href="{{ route('money-exchange.currency-inventory.currency-sell') }}" style="margin-top:3px; font-size:18px;">Money Exchange Sell</a>
      <a class="btn btn-danger d-block buy-sell-button" href="{{ route('money-exchange.currency-inventory.currency-buy') }}" style="margin-top:3px; font-size:18px;">Money Exchange Buy</a>
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
        <li class="treeview @yield('user_register_class')">
          <a href="#" style="text-decoration: none;">
            <i class="fa fa-user"></i> <span>User Registration</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="@yield('user_register_create')"><a href="{{ url('/create_team')}}"><i class="fa fa-circle-o"></i> Create Team</a></li>
            <li class="@yield('user_register_user_reg')"><a href="{{ url('/user_register')}}"><i class="fa fa-circle-o"></i> User Register</a></li>
            <li class="@yield('user_register_agm')"><a href="{{ url('/head-of-sales')}}"><i class="fa fa-circle-o"></i> Head Of Sales</a></li>
            <li class="@yield('user_register_team_leader')"><a href="{{ url('/team_leaders')}}"><i class="fa fa-circle-o"></i> Team Leader/Manager</a></li>
            <li class="@yield('user_register_user_list')"><a href="{{ url('/user_list')}}"><i class="fa fa-circle-o"></i> User</a></li>
          </ul>
        </li>
        @if(check_or_permission([1]) == true)
        <li class="treeview @yield('sds_menu_class')">
          <a href="#" style="text-decoration: none;">
            <i class="fa fa-dashboard"></i> <span>CRM</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="treeview @yield('customer_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Customer</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
		            <li class="@yield('create_customer_class')"><a href="{{ url('/create_customer')}}"><i class="fa fa-circle-o"></i> Create Customer</a></li>
		            <li class="@yield('my_customer_list_class')"><a href="{{ url('/customer_list')}}"><i class="fa fa-circle-o"></i> My Customer List</a></li>
		            <li class="@yield('total_customer_list_class')"><a href="{{ url('/total_customer_list')}}"><i class="fa fa-circle-o"></i> Total Customer List</a></li>
		            <li class="@yield('order_customer_class')"><a href="{{ url('/order_customer')}}"><i class="fa fa-circle-o"></i>
                Ordered Customers</a></li>
 <!--            @if( Auth::user()->au_user_type == 4 )
		            <li><a href="{{ url('/visited_customer')}}"><i class="fa fa-circle-o"></i> Visited Customers</a></li>
            @endif -->
		          </ul>
            </li>
            <li class="@yield('today_customer_list_class')"><a href="{{ url('/todays_customer_list')}}"><i class="fa fa-circle-o"></i> Customer Entry Info</a></li>
            <li class="@yield('weekly_summery_class')"><a href="{{ url('/weekly_summery') }}"><i class="fa fa-circle-o"></i> Weekly Summery</a></li>
            <li class="@yield('monthly_summery_class')"><a href="{{ url('/monthly_summery') }}"><i class="fa fa-circle-o"></i> Monthly Summery</a></li>
{{--            <li class="@yield('monthly_summery_class')"><a href="{{ url('/monthly_sell_summery') }}"><i class="fa fa-circle-o"></i> Monthly Sell Summery</a></li>--}}
            <li class="@yield('due_follow_up')"><a href="{{ url('/due-follow-up') }}"><i class="fa fa-circle-o"></i> Due Follow Up</a></li>
              <li class="@yield('search_weekly_sell_class')">  <a href="{{ url('/search_weekly_sell')}}"> <i class="fa fa-circle-o"></i> Search by Weekly Sell </a></li>
              <li class="@yield('search_monthly_sell_class')">  <a href="{{ url('/search_monthly_sell')}}"> <i class="fa fa-circle-o"></i> Search by Monthly Sell </a></li>
              <li class="@yield('search_')">  <a href="{{ url('/employees_monthly_sell')}}"> <i class="fa fa-circle-o"></i> Monthly Sell Summery </a></li>
            {{--<li class="@yield('user_follow_up')"><a href="{{ url('/user-follow-up') }}"><i class="fa fa-circle-o"></i> User Follow Up</a></li>--}}


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
          </ul>
        </li>
        @endif

        <!-- end crm -->



        @if(check_or_permission([2]) == true)
        <!-- Start OMS -->
        <li class="treeview @yield('oms_class')">
	          <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>OMS</span>
	            <span class="pull-right-container">
	              <i class="fa fa-angle-left pull-right"></i>
	            </span>
	          </a>
		          <ul class="treeview-menu">
		            <li class="treeview @yield('oms_convence_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>Conveyance</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>

			          <ul class="treeview-menu">
			            <li class="@yield('convence_users')"><a href="{{ url('/oms_convence_users')}}"><i class="fa fa-circle-o"></i> Users</a></li>
			            <li class="@yield('convence_pending_bill')"><a href="{{ url('/convence_pending_bill')}}"><i class="fa fa-circle-o"></i> Pending Bill</a></li>
			            <li class="@yield('convence_reject_bill')"><a href="{{ url('/convence_reject_bill')}}"><i class="fa fa-circle-o"></i> Reject Bill</a></li>
			          </ul>
		            </li>
		            <li class="treeview @yield('oms_lunch_class')">
		            	<a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>Meal</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>
			          <ul class="treeview-menu">
			            <li class="@yield('lunch_users')"><a href="{{ url('/oms_lunch_users')}}"><i class="fa fa-circle-o"></i> Users</a></li>
			            <li class="@yield('lunch_pending_bill')"><a href="{{ url('/lunch_pending_bill')}}"><i class="fa fa-circle-o"></i> Pending Bill</a></li>
			            <li class="@yield('lunch_reject_bill')"><a href="{{ url('/lunch_reject_bill')}}"><i class="fa fa-circle-o"></i> Reject Bill</a></li>
			          </ul>
		            </li>
		            <li class="@yield('accounts_class')">
		                <a href="{{ url('/accounts') }}">
		                    <i class="fa fa-circle-o"></i> Accounts
		                </a>
		            </li>
		          </ul>
            </li>
            @endif
            <!-- End OMS -->

            @if(check_or_permission([3]) == true)
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
            @endif


            @if (Auth::user()->au_user_type == 4)
            <li class="treeview @yield('comission_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-dashboard"></i> <span>Comission</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="@yield('sell_comission_class')"><a href="{{ route('comission.sell-comission') }}"><i class="fa fa-circle-o"></i> Sell Comission</a></li>
              </ul>
          </li>
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


            @if(check_or_permission([4]) == true)
            {{-- ======== Start Inventory Menu ======== --}}
            <li class="treeview @yield('inventory_class')">
	            <a href="#" style="text-decoration: none;">
	            <i class="fa fa-dashboard"></i> <span>Inventory</span>
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
                            </ul>


                               {{--=================   Supplier Accounts Menu Start   ===================--}}

                            <ul class="treeview-menu">
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
                                      </ul>
                                      <!-- End Bank Accounts -->
                                     <!-- Start Expenses -->
                                     <ul class="treeview-menu">
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
                                        </ul>

                                        <ul class="treeview-menu">
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
                                                  <!--<li class="@yield('ledger_bank')">-->
                                                  <!--  <a href="{{ route('accounts.bank') }}">-->
                                                  <!--    <i class="fa fa-circle-o"></i> Bank-->
                                                  <!--  </a>-->
                                                  <!--</li>-->
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
                                            </ul>
                                          <!--   End Expenses -->
                                          <!-- Strat Contra Menu -->
                                          <!-- Start Expenses -->
                                     <ul class="treeview-menu">
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
                                          <!--   End Expenses -->
                                        <!--   End Contra Menu -->

                                        <!--=============  General Ledger ===============-->

                                        <!--============= End General Ledger ===============-->
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
                                      </ul>
                                      <!-- End Bank Accounts -->
                                     <!-- Start Expenses -->
                                     <ul class="treeview-menu">
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
                                        </ul>

                                        <ul class="treeview-menu">
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
                                            </ul>
                                          <!--   End Expenses -->
                                          <!-- Strat Contra Menu -->
                                          <!-- Start Expenses -->
                                     <ul class="treeview-menu">
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

            @if (check_or_permission([8]) == true)
            <li class="treeview @yield('payroll_class')">
              <a href="#" style="text-decoration: none;">
                <i class="fa fa-money"></i> <span>Payroll</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
                <ul class="treeview-menu">
                    <li class="@yield('employee_list')">
                        <a href="{{ route('payroll.employee_list') }}">
                          <i class="fa fa-circle-o"></i> Employee List
                        </a>
                      </li>
                      <li class="@yield('attendance_class')">
                        <a href="{{ route('payroll.attendance-show') }}">
                          <i class="fa fa-circle-o"></i> Attendance
                        </a>
                      </li>
                      <li class="@yield('employee_dob')">
                        <a href="{{ route('payroll.employee_date_of_birth') }}">
                          <i class="fa fa-circle-o"></i> Employee DOB
                        </a>
                      </li>
                      <li class="@yield('today_attendance_class')">
                        <a href="{{ route('payroll.today-attendance-show') }}">
                          <i class="fa fa-circle-o"></i> Today Attendance
                        </a>
                      </li>
                      <li class="@yield('office_holiday')">
                        <a href="{{ route('payroll.office-holiday') }}">
                          <i class="fa fa-circle-o"></i> Office Holiday & Weekend
                        </a>
                      </li>
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
                      {{-- leave management --}}

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



             <li class="treeview @yield('proposal_class')">
			          <a href="#" style="text-decoration: none;">
			            <i class="fa fa-dashboard"></i> <span>Proposal</span>
			            <span class="pull-right-container">
			              <i class="fa fa-angle-left pull-right"></i>
			            </span>
			          </a>
			          <ul class="treeview-menu">
			            <li class="@yield('proposal_letter')"><a href="{{ url('/proposal')}}"><i class="fa fa-circle-o"></i> Proposal Letter</a></li>
			            <li class=""><a href=""><i class="fa fa-circle-o"></i> Quotation</a></li>

			          </ul>
            </li>
        </section>
    </aside>
