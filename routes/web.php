<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BranchRegistrationController;
use App\Http\Controllers\Company\Hrmcontroller;
use App\Http\Controllers\SellMonitoringController;
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgetForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendReset'])->name('password.send');

// CRM automatic lead form: public link, no login (6 sends a minute per visitor)
Route::get('lead-form/{token}', 'CrmLeadFormController@show')->name('lead-form.show')->where('token', '[A-Za-z0-9]{20,40}');
Route::post('lead-form/{token}', 'CrmLeadFormController@submit')->name('lead-form.submit')->where('token', '[A-Za-z0-9]{20,40}')->middleware('throttle:6,1');
Route::group(['middleware' => 'auth'],function(){
//Route::group([],function(){

//UserController


    Route::get('/index','UserController@home')->name('dashboard');
    Route::get('/branches', [BranchRegistrationController::class, 'index'])->name('branch.index');

// Company HRM Routes
   
Route::group([
    'prefix' => 'company/hrm',
    'as' => 'company.hrm.',
    'middleware' => ['web']
], function () {

    Route::get('dashboard', [HrmController::class, 'dashboard'])->name('dashboard');

    // Activities
    Route::get('branches-activities', [HrmController::class, 'showBranchesAndActivities'])->name('branches-activities');
    Route::get('today-activities', [HrmController::class, 'showTodayActivities'])->name('today-activities');
    Route::get('branch-activities/{branchId}', [HrmController::class, 'showBranchActivities'])->name('branch-activities');
    Route::get('activities-by-date', [HrmController::class, 'showActivitiesByDate'])->name('activities-by-date');
    Route::get('branch-activities-ajax/{branchId}', [HrmController::class, 'getBranchActivities'])->name('branch-activities-ajax');

    // Attendance
    Route::get('attendance/branches', [HrmController::class, 'allBranchesAttendance'])->name('attendance.branches');
    Route::get('attendance/branch/{branchId}', [HrmController::class, 'branchAttendanceDetail'])->name('attendance.branch.detail');
    Route::get('attendance/today', [HrmController::class, 'todayAllBranchesAttendance'])->name('attendance.today');
    
    // FIX THIS LINE - Remove the full prefix, just use the suffix
    Route::get('attendance/total-show', [HrmController::class, 'atTotalShow'])->name('attendance.total-show');
    
    // Reports
    Route::get('attendance/report/branch/{branchId}', [HrmController::class, 'downloadBranchAttendanceReport'])->name('attendance.report.branch');
    Route::get('attendance/report/company', [HrmController::class, 'downloadCompanyAttendanceReport'])->name('attendance.report.company');

    // Analytics
    Route::get('attendance/analytics', [HrmController::class, 'attendanceAnalytics'])->name('attendance.analytics');
    Route::get('attendance/employee-wise', [HrmController::class, 'employeeWiseAttendance'])->name('attendance.employee-wise');

    // AJAX
    Route::get('employee-details', [HrmController::class, 'getEmployeeAttendanceDetails'])->name('attendance.employee.details');

    // Export
    Route::get('export-today-pdf', [HrmController::class, 'exportTodayAttendancePDF'])->name('attendance.export-today-pdf');

    // Employee Attendance
    Route::get('employee/{employeeId}', [HrmController::class, 'employeeAttendanceDetail'])->name('attendance.employee-detail');
    Route::get('employee/{employeeId}/report', [HrmController::class, 'downloadEmployeeAttendanceReport'])->name('attendance.employee-report');
    
    Route::get('get-branch-employees', [HrmController::class, 'getBranchEmployees'])->name('get-branch-employees');
    
    // Fix these routes - they have /hrm/ in the path which is duplicate
    Route::get('employees/transfer', [Hrmcontroller::class, 'showEmployeesForTransfer'])->name('employees.transfer.list');
    Route::post('employees/transfer', [Hrmcontroller::class, 'transferEmployees'])->name('employees.transfer');
    Route::get('employees/transfer/history', [Hrmcontroller::class, 'transferHistory'])->name('employees.transfer.history');
    Route::post('employees/transfer/quick', [Hrmcontroller::class, 'quickTransferEmployee'])->name('employees.transfer.quick');

    // Employees
    Route::prefix('employees')->group(function () {
        Route::get('/', [HrmController::class, 'employeeList'])->name('employees.list');
        Route::get('create', [HrmController::class, 'createEmployee'])->name('employees.create');
        Route::post('store', [HrmController::class, 'storeEmployee'])->name('employees.store');
        Route::get('{employeeId}', [HrmController::class, 'employeeDetail'])->name('employees.detail');
        Route::get('{employeeId}/attendance-history', [HrmController::class, 'employeeAttendanceHistory'])->name('employees.attendance-history');
        Route::get('{employeeId}/edit', [HrmController::class, 'editEmployee'])->name('employees.edit');
        Route::put('{employeeId}/update', [HrmController::class, 'updateEmployee'])->name('employees.update');
        Route::post('{employeeId}/toggle-status', [HrmController::class, 'toggleEmployeeStatus'])->name('employees.toggle-status');
        Route::get('{employeeId}/download-profile', [HrmController::class, 'downloadEmployeeProfile'])->name('employees.download-profile');
    });

});
    Route::get('/branches/create', [BranchRegistrationController::class, 'create'])->name('branch.create');
    Route::post('/branches', [BranchRegistrationController::class, 'store'])->name('branch.store');
    Route::get('/branches/{branchId}/edit', [BranchRegistrationController::class, 'edit'])->name('branch.edit');
    Route::put('/branches/{branchId}', [BranchRegistrationController::class, 'update'])->name('branch.update');
    Route::delete('/branches/{branchId}', [BranchRegistrationController::class, 'destroy'])->name('branch.destroy');
    Route::get('/assign', [BranchRegistrationController::class, 'assignForm'])->name('branch.assignForm');
    Route::post('/assign', [BranchRegistrationController::class, 'assign'])->name('branch.assign');
    Route::get('/branch/assigned-users', [BranchRegistrationController::class, 'assignedUsers'])->name('branch.assignedUsers');
    Route::get('branch/toggle-user-status/{user}', [BranchRegistrationController::class, 'toggleUserStatus'])->name('branch.toggleUserStatus');
    Route::get('branch/toggle-branch-status/{branch}', [BranchRegistrationController::class, 'toggleBranchStatus'])->name('branch.toggleBranchStatus');
    
    Route::get('/provide_reason','SdsController@provide_reason');
    Route::get('/provide_reason_select','SdsController@reason_response')->name('reason_selected');
    Route::post('/provide_reason','SdsController@provide_reason_submit');
    Route::get('/create_team','UserController@create_team')->middleware('au_access:create_team');
    Route::post('/create_team',['uses' => 'UserController@create_team_insert']);
    Route::get('/team_update/{id}','UserController@team_update')->name('pages.team_update')->middleware('au_access:create_team');
    Route::post('/team_update_submit/{id}','UserController@team_update_submit')->name('pages.team_update_submit');
    Route::get('/user_register','UserController@user_register')->middleware('au_access:user_register');
    Route::post('/user_register',['uses' => 'UserController@user_register_insert']);
    Route::get('/team_leaders','UserController@team_leaders')->middleware('au_access:team_leader');
    Route::get('/team_leader_suspend/{id}','UserController@team_leader_suspend')->name('pages.team_leader_suspend');
    Route::get('/team_leader_active/{id}','UserController@team_leader_active')->name('pages.team_leader_active');

    Route::get('/head-of-sales','UserController@agm_list')->middleware('au_access:head_of_sales');
    Route::get('/agm_suspend/{id}','UserController@agm_suspend')->name('hos-suspend')->middleware('au_access:head_of_sales');
    Route::get('/agm_active/{id}','UserController@agm_active')->name('hos-active')->middleware('au_access:head_of_sales');
    Route::get('/team_list/{id}','UserController@team_list')->middleware('au_access:head_of_sales');
    Route::post('/team_list_submit/{id}','UserController@agm_team_submit')->name('team_list_submit');
    Route::get('/hos_team_list/{id}','UserController@agm_team_leaders')->middleware('HeadOfSales');

    Route::get('/user_list','UserController@user_list')->middleware('au_access:user');

// 
    Route::post('user-flexi-permission/{id}','UserController@userFlexiPermission')->name('user-flexi-permission');
    Route::get('/user_flexi_activate/{id}','UserController@user_flexi_activate')->name('user_flexi_activate');
    Route::get('/user_flexi_deactivate/{id}','UserController@user_flexi_deactivate')->name('user_flexi_deactivate');
// 

    Route::post('user-flexiload','UserController@userFlexiload')->name('user-flexiload');
    Route::get('user-flexiload-history','UserController@userFlexiHistory')->name('user-flexiload-history');
    Route::get('user-flexiload-company-history','UserController@userCompanyFlexiHistory')->name('user-flexiload-company-history');
// 
    Route::get('/user_list_activate/{id}','UserController@user_list_activate')->name('pages.user_list_activate');
    Route::get('/user_list_deactivate/{id}','UserController@user_list_deactivate')->name('pages.user_list_deactivate');
// 

    Route::get('/user_profile/{id}','UserController@user_profile')->name('pages.user_profile');
    Route::get('/user_profile/{id}','UserController@user_profile')->name('page.hrm.user_profile');
    // HRM Letters (Phase 1): letter types, letterhead PAD, signatories, access
    Route::get('hrm/letters/types', 'HrmLetterSetupController@types')->name('hrm.letters.types');
    Route::post('hrm/letters/types', 'HrmLetterSetupController@typeStore')->name('hrm.letters.types.store');
    Route::post('hrm/letters/types/{id}', 'HrmLetterSetupController@typeUpdate')->where('id', '[0-9]+')->name('hrm.letters.types.update');
    Route::get('hrm/letters/letterhead', 'HrmLetterSetupController@letterhead')->name('hrm.letters.letterhead');
    Route::post('hrm/letters/letterhead', 'HrmLetterSetupController@letterheadSave')->name('hrm.letters.letterhead.save');
    Route::post('hrm/letters/letterhead/reset', 'HrmLetterSetupController@letterheadReset')->name('hrm.letters.letterhead.reset');
    Route::get('hrm/letters/signatories', 'HrmLetterSetupController@signatories')->name('hrm.letters.signatories');
    Route::post('hrm/letters/signatories', 'HrmLetterSetupController@signatorySave')->name('hrm.letters.signatories.store');
    Route::post('hrm/letters/signatories/{id}', 'HrmLetterSetupController@signatorySave')->where('id', '[0-9]+')->name('hrm.letters.signatories.update');
    Route::get('hrm/letters/access', 'HrmLetterSetupController@access')->name('hrm.letters.access');
    Route::post('hrm/letters/access', 'HrmLetterSetupController@accessAdd')->name('hrm.letters.access.add');
    Route::post('hrm/letters/access/{id}/remove', 'HrmLetterSetupController@accessRemove')->where('id', '[0-9]+')->name('hrm.letters.access.remove');
    // HRM Letters (Phase 3): generate, edit draft, review, approve, list, my letters
    Route::get('hrm/letters', 'HrmLetterController@index')->name('hrm.letters.index');
    Route::get('hrm/letters/my', 'HrmLetterController@my')->name('hrm.letters.my');
    Route::get('hrm/letters/new', 'HrmLetterController@create')->name('hrm.letters.create');
    Route::post('hrm/letters', 'HrmLetterController@store')->name('hrm.letters.store');
    Route::get('hrm/letters/{id}', 'HrmLetterController@show')->where('id', '[0-9]+')->name('hrm.letters.show');
    Route::post('hrm/letters/{id}', 'HrmLetterController@update')->where('id', '[0-9]+')->name('hrm.letters.update');
    Route::post('hrm/letters/{id}/preview', 'HrmLetterController@preview')->where('id', '[0-9]+')->name('hrm.letters.preview');
    Route::post('hrm/letters/{id}/refill', 'HrmLetterController@refill')->where('id', '[0-9]+')->name('hrm.letters.refill');
    Route::post('hrm/letters/{id}/submit', 'HrmLetterController@submit')->where('id', '[0-9]+')->name('hrm.letters.submit');
    Route::post('hrm/letters/{id}/review', 'HrmLetterController@review')->where('id', '[0-9]+')->name('hrm.letters.review');
    Route::post('hrm/letters/{id}/approve', 'HrmLetterController@approve')->where('id', '[0-9]+')->name('hrm.letters.approve');
    Route::post('hrm/letters/{id}/reject', 'HrmLetterController@reject')->where('id', '[0-9]+')->name('hrm.letters.reject');
    Route::post('hrm/letters/{id}/revise', 'HrmLetterController@revise')->where('id', '[0-9]+')->name('hrm.letters.revise');
    Route::post('hrm/letters/{id}/cancel', 'HrmLetterController@cancel')->where('id', '[0-9]+')->name('hrm.letters.cancel');
    Route::get('hrm/letters/{id}/print', 'HrmLetterController@printView')->where('id', '[0-9]+')->name('hrm.letters.print');
    Route::post('hrm/letters/{id}/printed', 'HrmLetterController@printed')->where('id', '[0-9]+')->name('hrm.letters.printed');
    Route::post('hrm/letters/{id}/pdf', 'HrmLetterController@pdf')->where('id', '[0-9]+')->name('hrm.letters.pdf');
    // HRM Letters (Phase 2): templates
    Route::get('hrm/letters/templates', 'HrmLetterTemplateController@index')->name('hrm.letters.templates');
    Route::get('hrm/letters/templates/new', 'HrmLetterTemplateController@create')->name('hrm.letters.templates.create');
    Route::post('hrm/letters/templates', 'HrmLetterTemplateController@store')->name('hrm.letters.templates.store');
    Route::post('hrm/letters/templates/preview', 'HrmLetterTemplateController@preview')->name('hrm.letters.templates.preview');
    Route::get('hrm/letters/templates/{id}', 'HrmLetterTemplateController@edit')->where('id', '[0-9]+')->name('hrm.letters.templates.edit');
    Route::post('hrm/letters/templates/{id}', 'HrmLetterTemplateController@update')->where('id', '[0-9]+')->name('hrm.letters.templates.update');
    Route::post('hrm/letters/templates/{id}/default', 'HrmLetterTemplateController@makeDefault')->where('id', '[0-9]+')->name('hrm.letters.templates.default');
    Route::post('hrm/letters/templates/{id}/duplicate', 'HrmLetterTemplateController@duplicate')->where('id', '[0-9]+')->name('hrm.letters.templates.duplicate');
    Route::get('/admin_list/{id}','UserController@user_profile_status')->name('pages.admin_list');
    Route::get('/admin_list_activate/{id}','UserController@user_profile_status_activate')->name('pages.admin_list_activate');
//    Route::get('/user_edit/{id}','UserController@user_edit')->name('pages.user_edit');
    Route::get('/user_edit/{id}','UserController@user_edit');

    Route::get('team-leader-exist','UserController@teamLeaderExistAjax')->name('team-leader-exist');

    Route::post('/user_edit_update/{id}','UserController@user_update')->name('pages.user_edit');
    Route::post('/user_settings/{id}','UserController@userSettings');
    Route::get('/mobileno_exist','UserController@mobileno_exists');

//user profile update
    Route::get('/update-profile','UserController@profileUpdateStep_1')->name('profileUpdateStep_1');
    Route::post('/update-profile-step-1/{id}','UserController@user_update_step_1')->name('user_update_step_1');

    Route::get('/add-reference','UserController@addReferance')->name('addReferance');
    Route::post('/update-profile-step-2','UserController@user_update_step_2')->name('user_update_step_2');

    Route::get('/add-experience','UserController@addExperience')->name('addExperience');
    Route::post('/update-profile-step-3','UserController@user_update_step_3')->name('user_update_step_3');

    Route::get('/experience-day-count','UserController@experienceDayCount')->name('experienceDayCount');

//user experience update
    Route::get('/update-experience/{id}','UserController@experienceEdit')->name('experienceEdit');
    Route::post('/experienceUpdate/{id}','UserController@experienceUpdate')->name('experienceUpdate');

//user experience reference
    Route::get('/update-reference/{id}','UserController@referenceEdit')->name('referenceEdit');
    Route::post('/referenceUpdate/{id}','UserController@referenceUpdate')->name('referenceUpdate');

//user profile update 2
    Route::get('/my-information-update','UserController@myInfoUpdate_1')->name('myInfoUpdate_1');
    Route::post('/my-information-update-update','UserController@myInfoUpdate_1_update')->name('myInfoUpdate_1_update');
    Route::get('/my-bio-update','UserController@myBioUpdate_1')->name('myBioUpdate_1');
    Route::post('/my-bio-update-update','UserController@myBioUpdate_1_update')->name('myBioUpdate_1_update');



//today in time late time attendance
    Route::get('/today-intime-attendance','AttendanceController@todayIntimeAttendance')->name('todayIntimeAttendance');
    Route::get('/today-late-attendance','AttendanceController@todayLateAttendance')->name('todayLateAttendance');
//UserController End

   //Root
    //reason
    Route::get('root/reason','UserController@reason');
    Route::post('root/reason/store','UserController@reason_add');
    Route::post('root/reason/update','UserController@reason_update');
    Route::get('root/reason/delete/{id}','UserController@reason_delete');

    //mode
    Route::get('root/mode','UserController@mode');
    Route::post('root/mode/store','UserController@mode_add');
    Route::post('root/mode/update','UserController@mode_update');
    Route::get('root/mode/delete/{id}','UserController@mode_delete');


    // System Settings
    Route::get('system-settings','UserController@systemSettings')->name('system-settings');

    // Theme Colors (company admin): default FelnaTech theme, or the company's own colours
    Route::get('theme-settings','ThemeSettingController@edit')->name('theme-settings');
    Route::post('theme-settings','ThemeSettingController@update')->name('theme-settings.update');
    Route::post('theme-settings/reset','ThemeSettingController@reset')->name('theme-settings.reset');

    // CRM > Auto Lead Form (company admin)
    Route::get('crm/lead-form','CrmLeadFormController@settings')->name('crm.lead-form');
    Route::post('crm/lead-form','CrmLeadFormController@saveSettings')->name('crm.lead-form.save');
    Route::post('crm/lead-form/new-link','CrmLeadFormController@newLink')->name('crm.lead-form.new-link');
    // CRM > My Lead Link (every employee: customers from this link are theirs)
    Route::get('crm/my-lead-link','CrmLeadFormController@myLink')->name('crm.my-lead-link');
    Route::post('crm/my-lead-link/new','CrmLeadFormController@myNewLink')->name('crm.my-lead-link.new');
    
        
// pc Settings
    Route::get('/pc-setting', 'PcSettingController@index')->name('pc_setting.index');
    Route::post('/pc-setting/create', 'PcSettingController@passwordCreate')->name('pc_setting.create');
    Route::post('/pc-setting/edit/{id}', 'PcSettingController@passwordEdit')->name('pc_setting.edit');
    Route::delete('/pc-setting/delete/{id}', 'PcSettingController@passwordDelete')->name('pc_setting.delete');
    Route::post('/pc-setting/verify', 'PcSettingController@verifyPanelPassword')->name('pc_setting.verify');

    Route::group(['middleware' => 'au_company_access:crm'], function() {


        Route::get('/sell-monitoring', [SellMonitoringController::class, 'sellMonitoring'])->name('sell.monitoring');
        Route::get('/user-wise-sales', [SellMonitoringController::class, 'userWiseSales'])->name('user.wise.sales');
        Route::get('/daily-sales-report', [SellMonitoringController::class, 'dailySalesReport'])->name('daily.sales.report');
        Route::get('/sales-data', [SellMonitoringController::class, 'getSalesData'])->name('sales.data');
        Route::get('/export-sales-report', [SellMonitoringController::class, 'exportSalesReport'])->name('export.sales.report');
        //////////////////////////////
        Route::get('user/customer_list/{id}','SdsController@user_customer_list')->name('user.customer_list')
            ->middleware('au_access:crm_customer_entry_info');
        Route::get('/customer_list','SdsController@customer_list')->name('customer_list');
        /////////////////////////////

        //SdsController Start
        Route::get('/company_register','SdsController@company_register');
        Route::post('/company_register',['uses' => 'SdsController@company_register_insert']);
        Route::get('/admin_list','SdsController@admin_list');
        Route::get('/add_reason','SdsController@add_reason');
        Route::get('/reason_list','SdsController@reason_list')->name('show_reason_list');
        Route::post('/reason_edit','SdsController@reason_edit')->name('reason_edit');
        Route::get('/reason_delete/{id}','SdsController@reason_delete')->name('reason_delete');
        Route::post('/add_reason' ,['uses' => 'SdsController@add_reason_insert']);

        Route::get('/department','DepartmentController@department')->name('department');
        Route::post('/department-store','DepartmentController@departmentStore')->name('department-store');
        Route::post('/department-update','DepartmentController@departmentEdit')->name('department-update');
        Route::get('/department-delete/{id}','DepartmentController@departmentDelete')->name('department-delete');

        Route::get('/department-assign','DepartmentController@departmentAssign')->name('department-assign');
        Route::post('/department-assign-store','DepartmentController@departmentAssignStore')->name('department-assign-store');
        Route::get('/department-assign-edit/{id}','DepartmentController@departmentAssignEdit')->name('department-assign-edit');
        Route::post('/department-assign-update/{id}','DepartmentController@departmentAssignUpdate')->name('department-assign-update');

        Route::get('/add_category','SdsController@add_category');
        Route::post('/add_category',['uses' => 'SdsController@add_category_insert']);
        Route::post('/catagory_edit','SdsController@catagory_edit')->name('catagory_edit');
        Route::get('/catagory_delete/{id}','SdsController@catagory_delete')->name('catagory_delete');
        Route::get('/create_customer','SdsController@create_customer')->middleware('au_access:crm_create_customer');
        Route::get('/failed-submissions', 'SdsController@failed_submissions');
        Route::get('/search-customers-by-mobile', 'SdsController@searchCustomersByMobile');
        Route::post('/create_customer',['uses' => 'SdsController@create_customer_submit']);
        Route::get('/create_customer/show_reason_by_ajax',['uses' => 'SdsController@show_reason_by_ajax']);

        Route::get('/customer_list','SdsController@customer_list')->middleware('au_access:crm_my_customer_list');


        Route::get('/client_feedback/{id}','SdsController@client_feedback')->name('pages.client_feedback')->where(['id' => '[0-9]+']);
        Route::get('/client_feedback_oms/{id}','SdsController@client_feedback_oms')->name('oms_client');
        Route::get('/client_feedback_modal/{id}','SdsController@smsNotify')->name('pages.sms_notification');
        Route::post('/client_feedback/{id}',['uses' => 'SdsController@client_feedback_submit'])->where(['id' => '[0-9]+']);

        Route::get('/client_feedback_edit/{id}','SdsController@client_feedback_edit')->name('pages.client_feedback_edit');
        Route::post('/client_feedback_edit/{id}','SdsController@client_feedback_edit_update')->name('client_feedback_edit');
        Route::post('/client_feedback/send-feedback/{id}','SdsController@send_notification');
        Route::post('/client_feedback/visit/{id}','SdsController@visit_notify');
        Route::post('/check_mobile_unique', 'SdsController@checkMobileNumberUnique')->name('check_mobile_unique');



        Route::get('/total_customer_list', 'SdsController@total_customer_list')->middleware('au_access:crm_total_customer_list')->name('total_customer_list');


// load free


        Route::get('/total_customer_list_L','SdsController@total_customer_list_L')->middleware('au_access:crm_total_customer_list');



// excle part here......

        Route::get('total_customer_list_export','SdsController@get_Totallcustomer_data')->name('total_customer_list.export');



        Route::get('/admin_customer/{id}','SdsController@admin_created')->middleware('au_access:crm_admin_created_customer');
        Route::get('/order_customer','SdsController@order_customer');
                Route::get('/order_customer', 'SdsController@order_customer')
                    ->name('order_customer') 
                    ; 

        Route::post('/update_order', 'SdsController@updateOrder')->middleware('au_access:crm_ordered_customer');
        Route::get('/order-update-form','SdsController@orderUpdateForm')->name('order-update-form');
        Route::post('/update-order-submit','SdsController@updateOrderSubmit')->name('update-order-submit');
        Route::get('/visited_customer','SdsController@visited_customer');
        Route::get('/members_list/{id}','SdsController@members_list')->name('members_list')->middleware('au_access:crm_team_member_list');
        Route::get('/followup','SdsController@followup')->middleware('au_access:crm_followup_customer');
        Route::get('/non_followup','SdsController@non_followup')->middleware('au_access:crm_non_followup_customer');
        Route::get('/search_findus','SdsController@search_findus')->middleware('au_access:crm_search_find_us');
        Route::get('/search_reason','SdsController@search_reason')->middleware('au_access:crm_search_reason');
        Route::get('/search_result','SdsController@search_result')->middleware('au_access:crm_search_result');
        Route::get('/search_feedback','SdsController@search_feedback')->middleware('au_access:crm_search_feedback');
        Route::get('/todays_customer_list' , 'SdsController@todays_customer_list')->middleware('au_access:crm_customer_entry_info');
        Route::get('/customer_list_monthly/{id}','SdsController@customer_list_monthly')->name('customer_list_monthly')->middleware('au_access:crm_customer_entry_info');
        Route::get('/customer_list_today/{id}','SdsController@customer_list_today')->name('customer_list_today')->middleware('au_access:crm_customer_entry_info');
        Route::get('/find_us','SdsController@find_us');
        Route::post('/find_us',['uses' => 'SdsController@find_us_submit']);
        Route::post('/find_us_edit','SdsController@find_us_edit')->name('find_edit');
        Route::get('/find_delete/{id}','SdsController@find_up_delete')->name('find_delete');
        Route::get('/weekly_summery','SdsController@weekly_summery')->middleware('au_access:crm_weekly_summery');
        Route::get('/monthly_summery','SdsController@monthly_summery')->middleware('au_access:crm_monthly_summery');
        Route::get('/mobile_num_exist','SdsController@mobile_num_exist')->name('ajax.check_mobile_num_exists');

        // Employee Sell Report
        Route::get('/search_weekly_sell','SdsController@search_weekly_sell')->middleware('au_access:crm_monthly_summery');
        Route::get('/search_monthly_sell','SdsController@search_monthly_sell')->middleware('au_access:crm_monthly_summery');
        Route::get('/employees_weekly_sell','SdsController@employees_weekly_sell')->middleware('au_access:crm_monthly_summery');
        Route::get('/employees_monthly_sell','SdsController@employees_monthly_sell')->middleware('au_access:crm_monthly_summery');
        Route::get('/employee_yearly_sell','SdsController@employee_yearly_sell')->middleware('au_access:crm_monthly_summery');

        Route::get('/sales-visit','SalesVisitController@salesVisit')->name('sales-visit');
        Route::get('/sales-visit-report','SalesVisitController@monthReport')->name('sales-visit-report');

        Route::post('/sales-visit', 'SalesVisitController@store')->name('sales-visit-store');
        Route::delete('/sales-visits/{id}', 'SalesVisitController@destroy')->name('sales-visits-destroy');

        Route::get('/my-visit','SalesVisitController@mySalesVisit')->name('my-visit');
        Route::get('/my-visit-report','SalesVisitController@mySalesVisitReport')->name('my-visit-report');
        
        //SdsController End
    });


    Route::group(['middleware' => 'au_company_access:sms'], function() {
        //Sms Controller Start
        Route::get('/sms_setup', 'SmsController@sms_setup')->middleware('au_access:sms_setup')->name('sms_setup');
        Route::post('/sms_setup','SmsController@sms_setup_submit');
        Route::get('/sms_campaign' , 'SmsController@sms_campaign')->middleware('au_access:sms_campaign');
        Route::post('/sms_campaign' , 'SmsController@sms_campaign_send');
        Route::get('/sms_temp_edit' , 'SmsController@sms_temp_edit')->middleware('au_access:sms_template');
        Route::post('/sms_temp_edit' , ['uses' => 'SmsController@create_template']);
        Route::get('/sms_temp_edit/{id}','SmsController@temp_del')->name('pages.sms_temp_edit_del');
        Route::get('/sms_temp_modify/{id}','SmsController@sms_temp_modify')->name('pages.sms_temp_modify');
        Route::post('/sms_temp_modify/{id}','SmsController@sms_temp_modify_edit');
        //Sms Controller End
    });

    Route::group(['prefix' => 'comission' , 'as' => 'comission.'],function(){
        Route::get('sell-comission','SdsController@sell_comission')->name('sell-comission');
    });
// ===== FIXED ASSETS ROUTES =====
// Asset Categories Routes
Route::group(['prefix' => 'fixed-assets', 'as' => 'assets.', 'middleware' => 'au_company_access:inventory'], function () {
    // Categories
    Route::get('categories', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'index'])->name('categories');
    Route::post('categories', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{id}', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{id}', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('categories/status/{id}/{status}', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'changeStatus'])->name('categories.status');
    Route::get('categories-ajax', [App\Http\Controllers\Inventory\AssetCategoryController::class, 'getCategoriesAjax'])->name('categories.ajax');
    
    // Assets
    Route::get('/', [App\Http\Controllers\Inventory\AssetController::class, 'index'])->name('index');
    Route::get('create', [App\Http\Controllers\Inventory\AssetController::class, 'create'])->name('create');
    Route::post('store', [App\Http\Controllers\Inventory\AssetController::class, 'store'])->name('store');
    Route::get('{id}', [App\Http\Controllers\Inventory\AssetController::class, 'show'])->name('show');
    Route::get('{id}/edit', [App\Http\Controllers\Inventory\AssetController::class, 'edit'])->name('edit');
    Route::put('{id}', [App\Http\Controllers\Inventory\AssetController::class, 'update'])->name('update');
    Route::delete('{id}', [App\Http\Controllers\Inventory\AssetController::class, 'destroy'])->name('destroy');
    Route::get('status/{id}/{status}', [App\Http\Controllers\Inventory\AssetController::class, 'changeStatus'])->name('status');
    Route::get('download-pdf', [App\Http\Controllers\Inventory\AssetController::class, 'downloadPDF'])->name('download.pdf');
});
    Route::group(['prefix' => 'projects' , 'as' => 'projects.' , 'middleware' => 'Projects'],function(){
        Route::get('add','ProjectsController@add')->name('add-project');
        Route::get('invoice-ajax','ProjectsController@show_invoice_ajax')->name('invoice-ajax');
        Route::get('invoice-ajax-amount','ProjectsController@show_invoice_amount_ajax')->name('invoice-ajax-amount');
        Route::get('department-ajax','ProjectsController@departmentUserAjax')->name('department-ajax');
        Route::get('all-user-ajax2','ProjectsController@allUserAjax2');
        Route::post('store','ProjectsController@store')->name('store-project');

        Route::get('payments','ProjectsController@projectsPayments')->name('payments-project');
        Route::get('rest-amount','ProjectsController@projectAmountAjax')->name('rest-amount');
        Route::post('payments-store','ProjectsController@projectsPaymentsStore')->name('payments-project-store');

        Route::get('details','ProjectsController@details')->name('details-project');
        Route::get('details/{id}','ProjectsController@showDetails')->name('detailsById-project');

        Route::get('project-update/{id}','ProjectsController@projectsUpdate')->name('project-update');
        Route::get('invoice-ajax-update','ProjectsController@show_update_invoice_ajax')->name('invoice-ajax-update');
        Route::post('invoice-update/{id}','ProjectsController@updateInvoice')->name('invoice-update');
    });

    Route::group(['middleware' => 'au_company_access:oms'], function() {
        //OmsController Start
        Route::get('/oms_convence_users','OmsController@oms_convence_users')->middleware('au_access:oms_conveyance_users');
        Route::post('/oms_convence_users',['uses' => 'OmsController@oms_pay_convence']);
        Route::get('/oms_convence_details/{id}','OmsController@oms_convence_details');
        Route::get('/convence_add_new_bill','OmsController@convence_add_new_bill')->middleware('au_access:oms_conveyance_add_new_bill');
      Route::get('/convence_get_customer', 'OmsController@convence_get_customer')
    ->name('convence_get_customer')
    ->middleware('au_access:oms_conveyance_add_new_bill');
        Route::post('/convence_add_new_bill',['uses' => 'OmsController@convence_add_new_bill_submit']);
        Route::get('/convence_pending_bill','OmsController@convence_pending_bill')->middleware('au_access:oms_conveyance_pending_bill');
        Route::get('/convence_my_pending_bill','OmsController@my_pending_bill')->middleware('au_access:oms_my_pending_bill');
        Route::post('/convence_reject_bill_update/{id}','OmsController@convence_reject_bill_update')->name('pages.oms_update_bill');
        Route::get('/fetch-qb-data', 'OmsController@fetchQbData')->name('oms.fetchQbData');
        Route::post('/convence_pending_bill_reject/{id}','OmsController@convence_reject_update')->name('pages.convence_pending_bill_reject');
        Route::get('/convence_pending_bill_confirm/{id}','OmsController@convence_confirm_update')->name('pages.convence_pending_bill_confirm');
                Route::get('/convence_verification_accept/{id}','OmsController@convence_verification_accept')->name('pages.convence_verification_accept');
        Route::post('/convence_verification_reject/{id}','OmsController@convence_verification_reject')->name('pages.convence_verification_reject');
        Route::get('/convence_reject_bill','OmsController@convence_reject_bill')->middleware('au_access:oms_conveyance_reject_bill');
        Route::get('/convence_paid_bill','OmsController@convence_paid_bill')->middleware('au_access:oms_conveyance_paid_bill');
        Route::get('/oms_lunch_users','OmsController@oms_lunch_users')->middleware('au_access:oms_lunch_users');
        Route::post('/oms_lunch_users',['uses' => 'OmsController@oms_pay_lunch']);
        Route::get('/oms_lunch_details/{id}','OmsController@oms_lunch_details');
        Route::get('/lunch_add_new_bill','OmsController@lunch_add_new_bill')->middleware('au_access:oms_lunch_add_new_bill');
        Route::post('/lunch_add_new_bill',['uses' => 'OmsController@lunch_add_new_bill_submit']);
        Route::get('/lunch_pending_bill','OmsController@lunch_pending_bill')->middleware('au_access:oms_lunch_pending_bill');
        Route::get('/lunch_my_pending_bill','OmsController@lunch_my_pending_bill')->middleware('au_access:oms_lunch_my_pending_bill');
        Route::get('/lunch_pending_bill_confirm/{id}','OmsController@lunch_pending_confirm')->name('pages.lunch_pending_bill_confirm');
        Route::get('/lunch_pending_bill_reject/{id}','OmsController@lunch_pending_reject')->name('pages.lunch_pending_bill_reject');
        Route::post('/lunch_reject_update/{id}','OmsController@lunch_reject_update')->name('pages.oms_lunch_update_bill');
        Route::get('/lunch_reject_bill','OmsController@lunch_reject_bill')->middleware('au_access:oms_lunch_reject_bill');
        Route::get('/lunch_paid_bill','OmsController@lunch_paid_bill')->middleware('au_access:oms_lunch_paid_bill');
        Route::get('/oms_update_bill/{id}','OmsController@oms_update_bill');
        Route::get('/oms_lunch_update_bill/{id}','OmsController@oms_lunch_update_bill');
        Route::get('/oms_user_amount','OmsController@oms_convence_bill_users')->name('users_amount');
        Route::get('/oms_user_lunch_amount','OmsController@oms_lunch_bill_users')->name('users_lunch_amount');
        //Oms Controller End

        //Oms Office Lunch Start
        Route::get('/today_office_lunch','OfficeLunchController@officeLunchToday')->name('today_office_lunch');
        Route::get('/lunch-user','OfficeLunchController@officeLunchUser')->name('lunch-user');
        Route::post('lunch-mark','OfficeLunchController@lunchMark')->name('lunch-mark');
        Route::post('lunch-mark-admin','OfficeLunchController@lunchMarkAdmin')->name('lunch-mark-admin');
        Route::post('cancel-lunch','OfficeLunchController@lunchCancel')->name('cancel-lunch');
        Route::post('cancel-lunch-admin','OfficeLunchController@lunchCancelAdmin')->name('cancel-lunch-admin');
        Route::post('manual-lunch-entry','OfficeLunchController@manualLunchEntry')->name('manual-lunch-entry');

        Route::get('/accounts','OmsController@accounts')->name('accounts');
        Route::get('/accounts_details/{id}','OmsController@accounts_details')->name('accounts_details');
        Route::get('/accounts_user_amount','OmsController@accounts_bill_users')->name('accounts_user_amount');
        Route::get('/accounts_bank_balance','OmsController@accounts_bank_balance')->name('accounts_bank_balance');
        Route::post('/pay_bills','OmsController@pay_bills')->name('pay_bills');
    });


//Dashboard Controller Start
    Route::get('/todays_followup','DashboardController@todays_followup');
    Route::get('/given_followup','DashboardController@given_followup');
    Route::get('/followup_list','DashboardController@followup_list');
    Route::get('/due-follow-up','DashboardController@due_followup')->name('due-follow-up');
    Route::get('/user-follow-up','DashboardController@userFollowUp')->name('user-follow-up');
    Route::get('/user-follow-up-list/{id}/{date}','DashboardController@userFollowUpList')->name('user-follow-up-list');
    Route::get('/user-follow-up-duplicate/{id}','DashboardController@userFollowUpDuplicate')->name('user-follow-up-duplicate');
    Route::get('/page_permission/{id}','DashboardController@page_permission')->name('permission');
    Route::post('/page_permission/{id}','DashboardController@page_permission_submit')->name('permission');
    Route::get('/page_permission_com/{id}','DashboardController@page_permission_com')->name('com-permission');
    Route::post('/page_permission_com/{id}','DashboardController@page_permission_com_submit')->name('com-permission-submit');
    Route::get('/permission_user','DashboardController@permission_user_type');
//Dashboard Controller End

    Route::get('proposal','SdsController@proposal');
    Route::get('proposal-print','SdsController@createPdf');

    Route::group(['middleware' => 'au_company_access:inventory'], function() {
        //Inventory Controller Start
        Route::group(['prefix' => 'product','as' => 'inventory.'],function(){
            Route::get('product_add','Inventory\InvProductController@product_add')->name('add_product');
            Route::post('/check-unique-product', 'Inventory\InvProductController@check_unique_product')->name('inventory.check_unique_product');
            Route::get('product_type_ajax','Inventory\InvProductController@show_pro_grp_by_ajax')->name('product_submit_ajax');
            Route::post('product_detail_add','Inventory\InvProductController@product_detail_submit')->name('product_detail_submit');
            Route::get('product_list','Inventory\InvProductController@product_list')->name('product_list');
            Route::get('product_list/{id}','Inventory\InvProductController@product_detail_show')->name('product_show');
            Route::get('product_delete/{id}','Inventory\InvProductController@product_delete')->name('product_delete');
            Route::get('product_edit_page/{id}','Inventory\InvProductController@product_edit_page')->name('product_edit_page');
            Route::post('product_edit/{id}','Inventory\InvProductController@pro_edit')->name('product_edit');
            Route::get('group_add','Inventory\InvProductController@product_group')->name('add_group');
            Route::post('group_add','Inventory\InvProductController@product_group_submit')->name('add_group_submit');
            Route::get('group_list','Inventory\InvProductController@product_group_list')->name('group_list');
            Route::get('group_show/{id}','Inventory\InvProductController@product_group_show')->name('group_show');
            Route::get('group_delete/{id}','Inventory\InvProductController@pro_grp_del')->name('group_delete');
            Route::get('group_edit_page/{id}','Inventory\InvProductController@pro_grp_edit_page')->name('group_edit_page');
            Route::post('group_edit/{id}','Inventory\InvProductController@pro_grp_edit')->name('group_edit');
            Route::get('product_type','Inventory\InvProductController@product_type_add')->name('type_add');
            Route::post('product_type_submit','Inventory\InvProductController@product_type_submit')->name('type_submit');
            Route::get('product_type_show/{id}','Inventory\InvProductController@product_type_show')->name('type_show');
            Route::get('product_type_delete/{id}','Inventory\InvProductController@product_type_delete')->name('type_delete');
            Route::get('product_type_edit_page/{id}','Inventory\InvProductController@pro_type_edit_page')->name('type_edit_page');
            Route::post('product_type_edit/{id}','Inventory\InvProductController@pro_type_edit')->name('type_edit');
            Route::get('product_type_list','Inventory\InvProductController@product_type_list')->name('type_list');
  

            Route::get('short_quantity','Inventory\InvProductController@product_short_notify')->name('short');
            Route::get('warranty-claim','Inventory\InvProductController@warranty_claim')->name('warranty-claim');
            Route::get('warranty-claim-ajax','Inventory\InvProductController@warranty_claim_ajax')->name('warranty-claim-ajax');
            Route::get('sell-invoice-show-ajax','Inventory\InvProductController@sellInvoiceShowAjax')->name('sell-invoice-show-ajax');
            Route::get('buy-invoice-show-ajax','Inventory\InvProductController@buyInvoiceShowAjax')->name('buy-invoice-show-ajax');

        });

        Route::group(['prefix' => 'supplier' , 'as' => 'inventory.'],function(){
            Route::get('supplier_add','Inventory\InvSupplierController@add_supplier')->name('add');
            Route::post('supplier_add','Inventory\InvSupplierController@add_supplier_submit')->name('submit');
            Route::get('supplier_list','Inventory\InvSupplierController@supplier_list')->name('list');
            Route::get('supplier_list/{id}','Inventory\InvSupplierController@supplier_list_show')->name('list_show');
            Route::get('supplier_delete/{id}','Inventory\InvSupplierController@sup_del')->name('supplier_delete');
            Route::get('supplier_edit_page/{id}','Inventory\InvSupplierController@sup_edit_page')->name('supplier_show');
            Route::post('supplier_edit/{id}','Inventory\InvSupplierController@sup_edit')->name('supplier_edit');
            Route::get('supplier_product_add','Inventory\InvSupplierController@supplier_product_add')->name('pro_add');
            Route::post('supplier_product_add','Inventory\InvSupplierController@supplier_product_submit')->name('sup_pro_submit');
            Route::get('supplier_pro_show/{id}','Inventory\InvSupplierController@supplier_product_detail')->name('sup_pro_show');
            Route::get('supplier_pro_del/{id}','Inventory\InvSupplierController@supplier_product_delete')->name('sup_pro_del');
            Route::get('supplier_pro/{id}','Inventory\InvSupplierController@supplier_pro_edit_page')->name('sup_pro_edit');
            Route::post('supplier_pro_edit/{id}','Inventory\InvSupplierController@supplier_pro_edit')->name('edit_pro');
            Route::get('supplier_product_list','Inventory\InvSupplierController@supplier_product_list')->name('pro_list');



            // ================= Supplier Accounts Routes List Start=============

            Route::group(['prefix' => 'supplier' , 'as' => 'supplier.'],function(){
                Route::get('accounts.deposit-withdraw','Inventory\SupplierAccountsController@depositWithdrawForm')->name('accounts.deposit-withdraw');
                Route::post('accounts.deposit-withdraw','Inventory\SupplierAccountsController@depositWithdrawStore')->name('accounts.deposit-withdraw');
                Route::get('accounts.payment','Inventory\SupplierAccountsController@supplierPaymentForm')->name('accounts.payment');
                Route::post('accounts.payment','Inventory\SupplierAccountsController@supplierPaymentStore')->name('accounts.payment');
                Route::get('accounts.payment-collection','Inventory\SupplierAccountsController@supplierPaymentCollectionForm')->name('accounts.payment-collection');
                Route::post('accounts.payment-collection','Inventory\SupplierAccountsController@supplierPaymentCollectionStore')->name('accounts.payment-collection');
                Route::get('accounts.account-statement','Inventory\SupplierAccountsController@showAccountStatement')->name('accounts.account-statement');
                Route::get('accounts.account-statement-details/{supplier_id}','Inventory\SupplierAccountsController@showAccountStatementDetails')->name('accounts.account-statement-details');

                Route::get('accounts.download-supplier-account-statement-details/{supplier_id}','Inventory\SupplierAccountsController@downloadAccountStatementDetails')->name('accounts.download-supplier-account-statement-details');
 Route::get('ajax-load-supplier-balance','Inventory\SupplierAccountsController@ajaxLoadSupplierBalance')->name('ajax-load-supplier-balance');
    Route::get('ajax-load-bank-balance','Inventory\SupplierAccountsController@ajaxLoadBankBalance')->name('ajax-load-bank-balance');

                Route::get('accounts.supplier-voucher-details','Inventory\SupplierAccountsController@loadAjaxInvoiceDetails')->name('accounts.supplier-voucher-details');

                //=========18-11-19================
                Route::get('accounts.today-payment','Inventory\SupplierAccountsController@supplierTodayPaymentReport')->name('accounts.today-payment');
            });
            // ================= Supplier Accounts Routes List End=============

        });

        Route::group(['prefix' => 'customer' , 'as' => 'customer.'],function(){
            Route::get('customer_add_page','Inventory\InvCustomerController@customer_add_page')->name('customer_add');
            Route::post('customer_add_submit','Inventory\InvCustomerController@customer_add_submit')->name('customer_submit');
            Route::get('customer_list','Inventory\InvCustomerController@customer_list')->name('customer_list');
            Route::get('customer_detail/{id}','Inventory\InvCustomerController@customer_detail')->name('customer_detail');
            Route::get('customer_delete/{id}','Inventory\InvCustomerController@customer_delete')->name('customer_delete');
            Route::get('customer_edit_page/{id}','Inventory\InvCustomerController@customer_edit_page')->name('customer_edit_page');
            Route::post('customer_edit/{id}','Inventory\InvCustomerController@customer_edit')->name('customer_edit');
            Route::get('customer_exist','Inventory\InvCustomerController@customer_exist')->name('customer_exist');



            //==============Inventory Customer Account Routes Start======================

            Route::group(['prefix' => 'accounts' , 'as' => 'accounts.'],function(){
                Route::get('deposit-withdraw','Inventory\CustomerAccountsController@depositWithdrawForm')->name('deposit-withdraw');
                Route::post('deposit-withdraw','Inventory\CustomerAccountsController@depositWithdrawStore')->name('deposit-withdraw');
                Route::get('payment','Inventory\CustomerAccountsController@customerPaymentForm')->name('payment');
                Route::post('payment','Inventory\CustomerAccountsController@customerPaymentStore')->name('payment');
                Route::get('payment-refund','Inventory\CustomerAccountsController@customerPaymentRedfundForm')->name('payment-refund');
                 Route::get('download-account-statement-pdf','Inventory\CustomerAccountsController@downloadAccountStatementPdf')->name('download-account-statement-pdf');
                Route::post('payment-refund','Inventory\CustomerAccountsController@customerPaymentRedfundStore')->name('payment-refund');
                Route::get('account-statement','Inventory\CustomerAccountsController@showAccountStatement')->name('account-statement');

                Route::get('account-statement-details/{customer_id}','Inventory\CustomerAccountsController@showAccountStatementDetails')->name('account-statement-details');
                 Route::get('accounts.cusotmer-voucher-details','Inventory\CustomerAccountsController@loadAjaxInvoiceDetails')->name('accounts.cusotmer-voucher-details');

                //=====================17-11-19 ================

                Route::get('general_ledger_download','Inventory\BankAccountController@downLoadGeneralLedger')->name('general_ledger_download');

                //=====================17-11-19 ================

//  ====================18-11-19 ===============



                Route::get('download-customer-account-statement-details/{customer_id}','Inventory\CustomerAccountsController@downloadAccountStatementDetails')->name('download-customer-account-statement-details');


                

                //===========18-11-19 ============

                Route::get('today-payment','Inventory\CustomerAccountsController@customerTodayPaymentReport')->name('today-payment');
            });

        //==============Inventory Customer Account Routes End======================

        });

        Route::group(['prefix' => 'product_inventory' , 'as' => 'buy.'],function(){
            Route::get('buy_product','Inventory\ProductInventoryController@buy_product_add')->name('buy_add');
            Route::post('buy_product_add','Inventory\ProductInventoryController@buy_product_submit')->name('buy_submit');
            Route::get('buy-product-new','Inventory\ProductInventoryController@buy_product_new')->name('buy-product-new')->middleware('au_access:purchase_product');
            Route::get('buy_product/add-to-cart','Inventory\InventoryPurchaseCartController@addToCart')->name('buy-add-to-cart');
            Route::get('buy_product/add-to-cart-warrenty-product','Inventory\InventoryPurchaseCartController@addToCartWarrentyProduct')->name('buy-add-to-cart-warrenty-product');
            Route::get('buy_product/add-warrenty-product-sl-no','Inventory\InventoryPurchaseCartController@addWarrentyProductSlNo')->name('buy-add-warrenty-product-sl-no');
            Route::get('buy_product/remove-warrenty-product-sl','Inventory\InventoryPurchaseCartController@removeWarrentyProductSlNo')->name('buy-remove-warrenty-product-sl');
            Route::get('buy_product/get-cart','Inventory\InventoryPurchaseCartController@getCartContent')->name('buy-get-cart');
            Route::get('buy_product/cart-remove','Inventory\InventoryPurchaseCartController@removecart')->name('buy-remove-cart');
            Route::get('buy_product/cart-update','Inventory\InventoryPurchaseCartController@updatecart')->name('buy-update-cart');
            Route::post('buy_product/cart-submit','Inventory\InventoryPurchaseCartController@cartSubmit')->name('buy-cart-submit');
            Route::get('buy_prodect/buy_product_invoice','Inventory\InventoryPurchaseCartController@invTemporaryBuy')->name('buy-temporary');
            Route::get('available_list','Inventory\ProductInventoryController@available_list')->name('available');
  // NEW PURCHASE MANAGEMENT ROUTES
            Route::prefix('purchases')->group(function() {
                // Purchase list
                Route::get('list', 'Inventory\ProductInventoryController@product_purchase_list')
                    ->name('purchase.list')
                    ;
                    
                // View purchase details
                Route::get('view/{id}', 'Inventory\ProductInventoryController@view_product_purchase')
                    ->name('purchase.view')
                    ;
                    
                // Edit purchase form
                Route::get('edit/{id}', 'Inventory\ProductInventoryController@edit_product_purchase')
                    ->name('purchase.edit')
                    ;
                    
                // Update purchase
                Route::post('update/{id}', 'Inventory\ProductInventoryController@update_product_purchase')
                    ->name('purchase.update')
                    ;
                    
                // Delete purchase
                Route::delete('delete/{id}', 'Inventory\ProductInventoryController@delete_product_purchase')
                    ->name('purchase.delete')
                    ;
            });
            Route::get('sell_product','Inventory\ProductInventoryController@sell_product')->name('pro_sell')->middleware('au_access:sell_product');
            Route::get('sell_product/add-to-cart','Inventory\InventoryCartController@addToCart')->name('add-to-cart');
            Route::get('sell_product/add-to-cart-warrenty-product','Inventory\InventoryCartController@addToCartWarrentyProduct')->name('add-to-cart-warrenty-product');
            Route::get('sell_product/add-warrenty-product-sl-no','Inventory\InventoryCartController@addWarrentyProductSlNo')->name('add-warrenty-product-sl-no');
            Route::get('sell_product/remove-warrenty-product-sl','Inventory\InventoryCartController@removeWarrentyProductSlNo')->name('remove-warrenty-product-sl');
            Route::get('sell_product/get-cart','Inventory\InventoryCartController@getCartContent')->name('get-cart');
            Route::get('sell_product/cart-remove','Inventory\InventoryCartController@removecart')->name('remove-cart');
            Route::get('sell_product/cart-update','Inventory\InventoryCartController@updatecart')->name('update-cart');
            Route::post('sell_product/car-submit','Inventory\InventoryCartController@cartSubmit')->name('car-submit');
            Route::get('sell_product/sell_product_invoice','Inventory\InventoryCartController@invTemporaryProduct')->name('sell_temp_invoice');


            Route::get('sell_service_charge','Inventory\InventoryCartController@addServiceCharges')->name('add-to-cart-service-charge');


            Route::get('product_type_ajax','Inventory\ProductInventoryController@show_pro_type_ajax')->name('type_submit_ajax');
            Route::get('product_search_ajax','Inventory\ProductInventoryController@product_search_ajax')->name('product-search');

        });

        Route::group(['prefix' => 'reports', 'as' => 'reports.'],function(){
            Route::get('buy_invoice','Inventory\ReportsController@buy_invoice_pdf')->name('buy_pdf');
            Route::get('buy_invoice_pdf','Inventory\ReportsController@buy_invoice_pdf_generate')->name('pdf-generate');
            
            Route::get('sell_invoice_pdf','Inventory\ReportsController@sell_invoice_pdf_generate')->name('sell-pdf-generate');
            Route::get('sell_reports','Inventory\ReportsController@sell_reports')->name('sell-reports');
            Route::get('sell_reports_confirm','Inventory\ReportsController@sell_confirm_reports')->name('sell-confirm_reports');
            Route::get('sell_report_ajax','Inventory\ReportsController@sell_report_ajax')->name('sell-reports-ajax');
            Route::get('buy_reports','Inventory\ReportsController@buy_reports')->name('buy-reports');
            Route::get('buy_reports_confirm','Inventory\ReportsController@buy_reports_confirm')->name('buy-report-confirm');
            Route::get('combined_reports','Inventory\ReportsController@combinedReports')->name('combined-reports');
            
            Route::get('buy_reports_ajax','Inventory\ReportsController@buy_reports_ajax')->name('buy-reports-ajax');
            Route::get('buy_pdf/{invoice}','Inventory\ReportsController@buy_reports_pdf')->name('buy-pdf');
            Route::get('sell_pdf/{invoice}','Inventory\ReportsController@sell_reports_pdf')->name('sell-pdf');
            Route::get('sell_statement_download','Inventory\ReportsController@sellReportsDownload')->name('sell-reports-download');
            Route::get('sell_confirm/{invoice}','Inventory\ReportsController@sell_confirm')->name('sell-confirm');
            Route::get('buy_confirm/{invoice}','Inventory\ReportsController@buy_confirm')->name('buy-confirm');
            Route::get('buy_statement_download','Inventory\ReportsController@BuyReportsDownload')->name('buy-reports-download');

            Route::get('sell_print/{invoice}','Inventory\ReportsController@sell_reports_pdf')->name('sell-print');
            Route::get('buy_print/{invoice}','Inventory\ReportsController@buy_reports_pdf')->name('buy-print');

            Route::get('buy_return_print/{invoice}','Inventory\ReportsController@buyReturnInvoicePrint')->name('buy-return-print');
            Route::get('sell_return_print/{invoice}','Inventory\ReportsController@sellReturnInvoicePrint')->name('sell-return-print');
            
             Route::get('stock-report', 'Inventory\ReportsController@stockReport')->name('stock.report');
    Route::get('stock-report/download', 'Inventory\ReportsController@stockReportDownload')->name('stock.report.download');


        });

        Route::group(['prefix' => 'sell' , 'as' => 'sell_edit.'],function(){
            Route::get('sell-edit/{invoice}','Inventory\ProductSellEditController@sell_product_edit')->name('sell-pro-edit');
            Route::get('sell_edit/add-to-cart','Inventory\ProductSellEditController@addToCart')->name('add-to-cart');
            Route::get('sell_edit/add-to-cart-warrenty-product','Inventory\ProductSellEditController@addToCartWarrentyProduct')->name('add-to-cart-warrenty-product');
            Route::get('sell_edit/add-warrenty-product-sl-no','Inventory\ProductSellEditController@addWarrentyProductSlNo')->name('add-warrenty-product-sl-no');
            Route::get('sell_edit/remove-warrenty-product-sl','Inventory\ProductSellEditController@removeWarrentyProductSlNo')->name('remove-warrenty-product-sl');
            Route::get('sell_edit/get-cart','Inventory\ProductSellEditController@getCartContent')->name('get-cart');
            Route::get('sell_edit/cart-remove','Inventory\ProductSellEditController@removecart')->name('remove-cart');
            Route::get('sell_edit/cart-update','Inventory\ProductSellEditController@updatecart')->name('update-cart');
            Route::get('sell_edit/sell-confirm','Inventory\ProductSellEditController@sellEditConfirmFormShow')->name('sll-confirm');
            Route::post('sell_edit/cart-submit','Inventory\ProductSellEditController@cartSubmit')->name('cart-submit');

            Route::get('sell_edit_service_charge','Inventory\ProductSellEditController@addServiceCharges')->name('sell-edit-service-charge');


            Route::get('sell_edit_again/{invoice}','Inventory\ProductSellEditController@sell_edit_again')->name('sell-edit-again');

        });

        Route::group(['prefix' => 'buy' , 'as' => 'buy_edit.'],function(){
            Route::get('buy-edit/{invoice}','Inventory\ProductBuyEditController@buy_product_edit')->name('buy-edit-pro');

            Route::get('buy_edit/add-to-cart','Inventory\ProductBuyEditController@addToCart')->name('add-to-cart');
            Route::get('buy_edit/add-to-cart-warrenty-product','Inventory\ProductBuyEditController@addToCartWarrentyProduct')->name('add-to-cart-warrenty-product');
            //Buy_edit_new
            Route::get('buy_edit/update-warranty-product','Inventory\ProductBuyEditController@updateWarrantyProduct')->name('update-warranty-product');
            Route::get('buy_edit/add-warrenty-product-sl-no','Inventory\ProductBuyEditController@addWarrentyProductSlNo')->name('add-warrenty-product-sl-no');
            Route::get('buy_edit/remove-warrenty-product-sl','Inventory\ProductBuyEditController@removeWarrentyProductSlNo')->name('remove-warrenty-product-sl');
            Route::get('buy_edit/get-cart','Inventory\ProductBuyEditController@getCartContent')->name('get-cart');
            Route::get('buy_edit/cart-remove','Inventory\ProductBuyEditController@removecart')->name('remove-cart');
            Route::get('buy_edit/cart-update','Inventory\ProductBuyEditController@updatecart')->name('update-cart');
            Route::get('buy_edit/buy-confirm','Inventory\ProductBuyEditController@buyEditConfirmFormShow')->name('buy-confirm');
            Route::post('buy_edit/cart-submit','Inventory\ProductBuyEditController@cartSubmit')->name('cart-submit');


            Route::get('buy_edit_again/{invoice}','Inventory\ProductBuyEditController@sell_edit_again')->name('sell-edit-again');
        });

        Route::group(['prefix' => 'return' , 'as' => 'product_return.'],function(){
            Route::get('sell_return','Inventory\ProductReturnController@sale_return')->name('sell-return-view');
            Route::get('sell_return_ajax','Inventory\ProductReturnController@sale_product_ajax')->name('sale-return-ajax');

            Route::get('sell/add-to-cart','Inventory\ProductSellReturnController@addToCart')->name('sell.add-to-cart');
            Route::get('sell/add-to-cart-warrenty-product','Inventory\ProductSellReturnController@addToCartWarrentyProduct')->name('sell.add-to-cart-warrenty-product');
            Route::get('sell/add-warrenty-product-sl-no','Inventory\ProductSellReturnController@addWarrentyProductSlNo')->name('sell.add-warrenty-product-sl-no');
            Route::get('sell/remove-warrenty-product-sl','Inventory\ProductSellReturnController@removeWarrentyProductSlNo')->name('sell.remove-warrenty-product-sl');
            Route::get('sell/get-cart','Inventory\ProductSellReturnController@getCartContent')->name('sell.get-cart');
            Route::get('sell/cart-remove','Inventory\ProductSellReturnController@removecart')->name('sell.remove-cart');
            Route::get('sell/sell-confirm','Inventory\ProductSellReturnController@invTemporaryProduct')->name('sell.sell-confirm');
            Route::post('sell/cart-submit','Inventory\ProductSellReturnController@cartSubmit')->name('sell.cart-submit');


            Route::get('buy_return','Inventory\ProductReturnController@buy_return')->name('buy-return-view');
            Route::get('buy_return_ajax','Inventory\ProductReturnController@buy_product_ajax')->name('buy-return-ajax');

            Route::get('buy/add-to-cart','Inventory\ProductBuyReturnController@addToCart')->name('buy.add-to-cart');
            Route::get('buy/add-to-cart-warrenty-product','Inventory\ProductBuyReturnController@addToCartWarrentyProduct')->name('buy.add-to-cart-warrenty-product');
            Route::get('buy/add-warrenty-product-sl-no','Inventory\ProductBuyReturnController@addWarrentyProductSlNo')->name('buy.add-warrenty-product-sl-no');
            Route::get('buy/remove-warrenty-product-sl','Inventory\ProductBuyReturnController@removeWarrentyProductSlNo')->name('buy.remove-warrenty-product-sl');
            Route::get('buy/get-cart','Inventory\ProductBuyReturnController@getCartContent')->name('buy.get-cart');
            Route::get('buy/cart-remove','Inventory\ProductBuyReturnController@removecart')->name('buy.remove-cart');
            Route::get('buy/buy-confirm','Inventory\ProductBuyReturnController@invTemporaryProduct')->name('buy.buy-confirm');
            Route::post('buy/cart-submit','Inventory\ProductBuyReturnController@cartSubmit')->name('buy.cart-submit');
        });

        Route::group(['prefix' => 'damage' , 'as' => 'pro_damage.'],function(){
            Route::get('add','Inventory\ProductInventoryController@damage_add')->name('damage-add');
            Route::get('pro_name_ajax','Inventory\ProductInventoryController@show_pro_name_ajax')->name('pro_name');
            Route::post('pro_damage_submit','Inventory\ProductInventoryController@damage_add_submit')->name('damage-submit');
            Route::get('pro_damage_list','Inventory\ProductInventoryController@damage_list')->name('damage-list');
        });

        Route::group(['prefix' => 'profit_loss' , 'as' => 'profit_loss.'],function(){
            Route::get('profit','Inventory\ProfitLossController@profit_statement')->name('profit');
            Route::get('loss','Inventory\ProfitLossController@loss_statement')->name('loss');
        });


        //=============Inventory Account Controller Start================
        Route::group(['prefix' => 'accounts','as' => 'accounts.'],  function(){
                     Route::group(['prefix' => 'bank-loan', 'as' => 'bank-loan.'], function () {
        Route::get('list', 'Inventory\BankLoanController@index')->name('list');
        Route::get('create', 'Inventory\BankLoanController@create')->name('create');
        Route::post('store', 'Inventory\BankLoanController@store')->name('store');
        Route::get('details/{id}', 'Inventory\BankLoanController@show')->name('details');
        Route::get('edit/{id}', 'Inventory\BankLoanController@edit')->name('edit');
        Route::post('update/{id}', 'Inventory\BankLoanController@update')->name('update');
        Route::delete('delete/{id}', 'Inventory\BankLoanController@destroy')->name('delete');
        Route::get('payment/{id}', 'Inventory\BankLoanController@showPaymentForm')->name('payment-form');
        Route::post('payment/{id}', 'Inventory\BankLoanController@storePayment')->name('payment-store');
        Route::get('transfer/{id}', 'Inventory\BankLoanController@showTransferForm')->name('transfer-form');
        Route::post('transfer/{id}', 'Inventory\BankLoanController@storeTransfer')->name('transfer-store');
        Route::get('ajax-calculate-emi', 'Inventory\BankLoanController@ajaxCalculateEMI')->name('ajax-calculate-emi');
        Route::get('report', 'Inventory\BankLoanController@report')->name('report');
    });
            Route::get('add-bank','Inventory\BankAccountController@addBank')->name('add-bank');
            Route::post('add-bank','Inventory\BankAccountController@storeBank')->name('add-bank');
            Route::get('bank-list','Inventory\BankAccountController@showBankList')->name('bank-list');
            Route::get('delete-bank/{id}','Inventory\BankAccountController@deleteBank')->name('delete-bank');
            Route::get('update-bank/{id}','Inventory\BankAccountController@editBank')->name('update-bank');
            Route::post('update-bank/{id}','Inventory\BankAccountController@updateBank')->name('update-bank');
            Route::get('bank-diposit-withdraw','Inventory\BankAccountController@bankDepositWithdrawShowForm')->name('bank-diposit-withdraw');
            Route::get('bank-statement-detail/{id}','Inventory\BankAccountController@bankStatementDetails')->name('bank-statement-detail');

            Route::post('bank-diposit-withdraw','Inventory\BankAccountController@bankDepositWithdrawStore')->name('bank-diposit-withdraw');
            Route::get('expense-categories','Inventory\ExpenseCategoryController@showExpenseCategories')->name('expense-categories');
            Route::get('expenses','Inventory\ExpenseCategoryController@showExpenses')->name('expenses');
            Route::post('expense-categories','Inventory\ExpenseCategoryController@storeExpenseCategory')->name('expense-categories');
            //Riaz's Created
            Route::get('expense_add','Inventory\ExpenseCategoryController@add_expense_form')->name('cost-add');
            Route::post('expense_cost_add','Inventory\ExpenseCategoryController@add_expense_cost')->name('expense-cost-add');
            Route::get('expense_category_edit/{cat_id}','Inventory\ExpenseCategoryController@expense_category_edit')->name('expense-category-edit');
            Route::post('expense_category_update/{id}','Inventory\ExpenseCategoryController@expense_category_update')->name('expense-category-update');
            //

            //ledger
            Route::get('ledger','Inventory\InvLedgerController@ledger_insert_form')->name('ledger');
            Route::get('ledger-categories','Inventory\InvLedgerController@ledger_categories')->name('ledger-category');
            Route::post('ledger-category-insert','Inventory\InvLedgerController@insert_ledger_category')->name('ledger-category-insert');
            Route::get('ledger-category-update','Inventory\InvLedgerController@ledger_category_edit_ajax')->name('ledger-category-update');
            Route::post('ledger-category-update-submit','Inventory\InvLedgerController@ledger_category_update')->name('ledger-category-update-submit');
            Route::post('ledger-insert','Inventory\InvLedgerController@ledger_insert')->name('ledger-insert');
            Route::get('ledger-categories-load-ajax','Inventory\InvLedgerController@showAjaxLoadedLedgerCategory')->name('ledger-categories-load-ajax');
            Route::get('ledger-add','Inventory\InvLedgerController@ledger_add')->name('ledger-add');
            Route::post('ledger-data','Inventory\InvLedgerController@insert_ledger_data')->name('ledger-data');
            Route::get('ledger_cash_in_edit','Inventory\InvLedgerController@ledger_cash_in_ajax')->name('ledger-cash-in-edit');
            Route::get('ledger_cash_out_edit','Inventory\InvLedgerController@ledger_cash_out_ajax')->name('ledger-cash-out-edit');
            Route::post('update-ledger-data','Inventory\InvLedgerController@update_ledger_data')->name('update-ledger-data');
            Route::post('update-ledger-cash-out-data','Inventory\InvLedgerController@update_ledger_cash_out_data')->name('update-ledger-cash-out-data');
            Route::get('ledger-bank','Inventory\InvLedgerController@bank_ledger')->name('bank');
            Route::get('ledger-bank-balance','Inventory\InvLedgerController@ajaxBankBalance')->name('ledger-bank-balance');
            //
            Route::post('expenses','Inventory\ExpenseCategoryController@storeExpenses')->name('expenses');
            Route::get('ajax-load_expense','Inventory\ExpenseCategoryController@ajaxLoadExpense')->name('ajax-load_expense');
            Route::get('create-contra','Inventory\BankAccountController@showContraForm')->name('create-contra');
            Route::get('contra-list','Inventory\BankAccountController@showContraList')->name('contra-list');
            
            Route::get('contra/{id}/edit', 'Inventory\BankAccountController@editContra')->name('edit-contra');
            Route::put('contra/{id}', 'Inventory\BankAccountController@updateContra')->name('update-contra');
    
            Route::post('create-contra','Inventory\BankAccountController@createContra')->name('create-contra');

            Route::get('general_ledger','Inventory\BankAccountController@showGeneralLedgerForm')->name('general_ledger');
            Route::post('general_ledger','Inventory\BankAccountController@showGeneralLedgerData')->name('general_ledger');


            //=====================17-11-19 ================

            Route::get('general_ledger_download','Inventory\BankAccountController@downLoadGeneralLedger')->name('general_ledger_download');

            //=====================17-11-19 ================

//  ====================18-11-19 ===============
            Route::get('account-statement','Inventory\BankAccountController@showAccountStatementForm')->name('account-statement');
               Route::get('account-statement-data', 'Inventory\BankAccountController@showAccountStatementData')->name('account-statement-data');
            Route::post('account-statement','Inventory\BankAccountController@showAccountStatementData')->name('account-statement');

            // ==================/18-11-19==================


            //=======================20-11-19===================
            Route::get('account-statement-download','Inventory\BankAccountController@downloadAccountStatement')->name('account-statement-download');
//================/20-11-19================
            // Routes for loading ajax for balance query

            Route::get('ajax-load-bank-balance','Inventory\BankAccountController@ajaxLoadBankBalance')->name('ajax-load-bank-balance');

            Route::get('ajax-load-supplier-balance','Inventory\BankAccountController@ajaxLoadSupplierBalance')->name('ajax-load-supplier-balance');

            Route::get('ajax-load-customer-balance','Inventory\BankAccountController@ajaxLoadCustomerBalance')->name('ajax-load-customer-balance');


            //============== Expense Voucher =============
            Route::get('expenses-voucher','Inventory\BankAccountController@expensesVoucherForm')->name('expenses-voucher');
            Route::post('expenses-voucher','Inventory\BankAccountController@expensesVoucherStore')->name('expenses-voucher');


            // Expense Voucher List
            Route::get('expenses-voucher-list', 'Inventory\BankAccountController@expensesVoucherList')->name('expenses-voucher-list');
            // Accounts > Reports (read-only: dashboard, profit & loss, day book, cash & bank book, due aging, expenses)
            Route::get('reports/dashboard', 'Inventory\AccountsReportController@dashboard')->name('reports.dashboard');
            Route::get('reports/profit-loss', 'Inventory\AccountsReportController@profitLoss')->name('reports.profit-loss');
            Route::get('reports/day-book', 'Inventory\AccountsReportController@dayBook')->name('reports.day-book');
            Route::get('reports/cash-book', 'Inventory\AccountsReportController@cashBook')->name('reports.cash-book');
            Route::get('reports/due-aging', 'Inventory\AccountsReportController@aging')->name('reports.aging');
            Route::get('reports/expenses', 'Inventory\AccountsReportController@expenses')->name('reports.expenses');
            Route::get('reports/depreciation', 'Inventory\AccountsReportController@depreciation')->name('reports.depreciation');
            // Accounts Phase 2: salary payment, petty cash, cheque register, bank reconciliation
            Route::get('salary-payment', 'Inventory\SalaryPaymentController@index')->name('salary-payment');
            Route::post('salary-payment', 'Inventory\SalaryPaymentController@pay')->name('salary-payment.pay');
            Route::get('petty-cash', 'Inventory\PettyCashController@index')->name('petty-cash');
            Route::post('petty-cash/create', 'Inventory\PettyCashController@create')->name('petty-cash.create');
            Route::post('petty-cash/move', 'Inventory\PettyCashController@move')->name('petty-cash.move');
            Route::get('cheques', 'Inventory\ChequeController@index')->name('cheques');
            Route::post('cheques', 'Inventory\ChequeController@store')->name('cheques.store');
            Route::post('cheques/{id}/clear', 'Inventory\ChequeController@clear')->name('cheques.clear');
            Route::post('cheques/{id}/bounce', 'Inventory\ChequeController@bounce')->name('cheques.bounce');
            Route::post('cheques/{id}/cancel', 'Inventory\ChequeController@cancel')->name('cheques.cancel');
            Route::get('bank-reconciliation', 'Inventory\BankReconController@index')->name('bank-reconciliation');
            Route::post('bank-reconciliation/mark', 'Inventory\BankReconController@mark')->name('bank-reconciliation.mark');
            // Accounts Phase 3a: chart of accounts, journal vouchers, journal book, account ledger, trial balance, balance sheet
            Route::get('journal/chart-of-accounts', 'Inventory\JournalController@coa')->name('journal.coa');
            Route::post('journal/chart-of-accounts', 'Inventory\JournalController@coaStore')->name('journal.coa.store');
            Route::post('journal/chart-of-accounts/{id}', 'Inventory\JournalController@coaUpdate')->where('id', '[0-9]+')->name('journal.coa.update');
            Route::get('journal/vouchers', 'Inventory\JournalController@vouchers')->name('journal.vouchers');
            Route::get('journal/vouchers/new', 'Inventory\JournalController@voucherCreate')->name('journal.vouchers.create');
            Route::post('journal/vouchers', 'Inventory\JournalController@voucherStore')->name('journal.vouchers.store');
            Route::get('journal/vouchers/{id}', 'Inventory\JournalController@voucherShow')->where('id', '[0-9]+')->name('journal.vouchers.show');
            Route::post('journal/vouchers/{id}/cancel', 'Inventory\JournalController@voucherCancel')->where('id', '[0-9]+')->name('journal.vouchers.cancel');
            Route::get('journal/book', 'Inventory\JournalController@book')->name('journal.book');
            Route::get('journal/account-ledger', 'Inventory\JournalController@ledger')->name('journal.ledger');
            Route::get('journal/trial-balance', 'Inventory\JournalController@trialBalance')->name('journal.trial-balance');
            Route::get('journal/balance-sheet', 'Inventory\JournalController@balanceSheet')->name('journal.balance-sheet');
            Route::post('journal/vouchers/{id}/approve', 'Inventory\JournalController@voucherApprove')->where('id', '[0-9]+')->name('journal.vouchers.approve');
            Route::post('journal/vouchers/{id}/reject', 'Inventory\JournalController@voucherReject')->where('id', '[0-9]+')->name('journal.vouchers.reject');
            // Accounts Phase 3b: settings, VAT, cash flow, fiscal years, budgets, audit trail
            Route::get('control/settings', 'Inventory\AccountsControlController@settings')->name('control.settings');
            Route::post('control/settings', 'Inventory\AccountsControlController@settingsSave')->name('control.settings.save');
            Route::get('control/vat', 'Inventory\AccountsControlController@vat')->name('control.vat');
            Route::post('control/vat/pay', 'Inventory\AccountsControlController@vatPay')->name('control.vat.pay');
            Route::get('control/cash-flow', 'Inventory\AccountsControlController@cashFlow')->name('control.cash-flow');
            Route::get('control/fiscal-years', 'Inventory\AccountsControlController@fiscalYears')->name('control.fiscal-years');
            Route::post('control/fiscal-years/close', 'Inventory\AccountsControlController@closeYear')->name('control.fiscal-years.close');
            Route::post('control/fiscal-years/reopen', 'Inventory\AccountsControlController@reopenYear')->name('control.fiscal-years.reopen');
            Route::get('control/budgets', 'Inventory\AccountsControlController@budgets')->name('control.budgets');
            Route::post('control/budgets', 'Inventory\AccountsControlController@budgetsSave')->name('control.budgets.save');
            Route::get('control/audit-trail', 'Inventory\AccountsControlController@audit')->name('control.audit');
            
            // Expense Voucher Edit
            Route::get('expenses-voucher/{id}/edit', 'Inventory\BankAccountController@expensesVoucherEdit')->name('expenses-voucher-edit');
            
            // Expense Voucher Update
            Route::post('expenses-voucher/{id}/update', 'Inventory\BankAccountController@expensesVoucherUpdate')->name('expenses-voucher-update');


            //==================== 16-11-19 =================

            Route::get('expense-categories-load-ajax','Inventory\ExpenseCategoryController@showAjaxLoadedExpensesCategory')->name('expense-categories-load-ajax');

            Route::get('expense-load-ajax','Inventory\ExpenseCategoryController@showAjaxLoadedExpenses')->name('expense-load-ajax');

        });
        //Inventory Account End

        //============== LC Management (import Letter of Credit) =============
        Route::group(['prefix' => 'lc', 'as' => 'lc.'], function () {
            Route::get('currencies', 'Lc\LcSettingController@currencies')->name('currencies');
            Route::post('currencies', 'Lc\LcSettingController@storeCurrency')->name('currencies.store');
            Route::post('currencies/{id}', 'Lc\LcSettingController@updateCurrency')->name('currencies.update');

            Route::get('files', 'Lc\LcFileController@index')->name('files.index');
            Route::get('files/create', 'Lc\LcFileController@create')->name('files.create');
            Route::post('files', 'Lc\LcFileController@store')->name('files.store');
            Route::get('files/{id}', 'Lc\LcFileController@show')->name('files.show');
            Route::get('files/{id}/edit', 'Lc\LcFileController@edit')->name('files.edit');
            Route::post('files/{id}', 'Lc\LcFileController@update')->name('files.update');
            Route::post('files/{id}/cancel', 'Lc\LcFileController@cancel')->name('files.cancel');

            Route::get('opening', 'Lc\LcController@openForm')->name('opening');
            Route::post('opening', 'Lc\LcController@openStore')->name('opening.store');
            Route::get('statement', 'Lc\LcController@statement')->name('statement');
            Route::post('statement/{lc}/costs', 'Lc\LcController@storeCost')->name('costs.store');
            Route::post('costs/{id}/delete', 'Lc\LcController@deleteCost')->name('costs.delete');
            Route::get('summary', 'Lc\LcController@summary')->name('summary');
            Route::get('debitors', 'Lc\LcController@debitors')->name('debitors');

            Route::get('documents', 'Lc\LcDocumentController@index')->name('documents');
            Route::post('documents', 'Lc\LcDocumentController@store')->name('documents.store');
            Route::get('documents/{id}/download', 'Lc\LcDocumentController@download')->name('documents.download');
            Route::post('documents/{id}/delete', 'Lc\LcDocumentController@delete')->name('documents.delete');

            Route::get('shipments', 'Lc\LcShipmentController@index')->name('shipments.index');
            Route::get('shipments/create', 'Lc\LcShipmentController@create')->name('shipments.create');
            Route::post('shipments', 'Lc\LcShipmentController@store')->name('shipments.store');
            Route::get('shipments/{id}', 'Lc\LcShipmentController@show')->name('shipments.show');
            Route::post('shipments/{id}/receive', 'Lc\LcShipmentController@receive')->name('shipments.receive');
            Route::post('shipments/{id}/delete', 'Lc\LcShipmentController@delete')->name('shipments.delete');
        });
        //============== LC Management End =============

    });

    Route::group(['prefix' => 'money-exchange' , 'as' => 'money-exchange.' , 'middleware' => 'MoneyExchange'],function(){
        Route::group(['prefix' => 'customer' , 'as' => 'customer.'],function(){
            Route::get('create','MoneyExchange\CustomerController@customer_add')->name('create');
            Route::post('customer-submit','MoneyExchange\CustomerController@customer_add_submit')->name('submit');
            Route::get('customer-list','MoneyExchange\CustomerController@customer_list')->name('customer-list');
            Route::post('customer-edit/{id}','MoneyExchange\CustomerController@customer_edit_submit')->name('edit');
            Route::get('customer-delete/{id}','MoneyExchange\CustomerController@customer_delete')->name('delete');
        });
        Route::group(['prefix' => 'others' , 'as' => 'others.'],function(){
            Route::get('country','MoneyExchange\OthersController@country')->name('country-list');
            Route::get('currencies','MoneyExchange\OthersController@currency')->name('currencies-list');
        });
        Route::group(['prefix' => 'currency-inventory' , 'as' => 'currency-inventory.'],function(){
            Route::get('currency-buy','MoneyExchange\CurrencyInventoryController@buy_currency_new')->name('currency-buy');
            Route::get('currency-sell','MoneyExchange\CurrencyInventoryController@sell_currency_new')->name('currency-sell');
            Route::get('currency-submit-ajax','MoneyExchange\CurrencyInventoryController@currency_submit_ajax')->name('currency-submit-ajax');
            Route::get('currency-search-ajax','MoneyExchange\CurrencyInventoryController@currency_search_ajax')->name('currency-search');

            Route::get('buy_currency/add-to-cart','MoneyExchange\CurrencyPurchaseCartController@addToCart')->name('buy-add-to-cart');
            Route::get('buy_product/add-to-cart-warrenty-product','Inventory\InventoryPurchaseCartController@addToCartWarrentyProduct')->name('buy-add-to-cart-warrenty-product');
            Route::get('buy_product/add-warrenty-product-sl-no','Inventory\InventoryPurchaseCartController@addWarrentyProductSlNo')->name('buy-add-warrenty-product-sl-no');
            Route::get('buy_product/remove-warrenty-product-sl','Inventory\InventoryPurchaseCartController@removeWarrentyProductSlNo')->name('buy-remove-warrenty-product-sl');
            Route::get('buy_currency/get-cart','MoneyExchange\CurrencyPurchaseCartController@getCartContent')->name('buy-get-cart');
            Route::get('buy_currency/cart-remove','MoneyExchange\CurrencyPurchaseCartController@removecart')->name('buy-remove-cart');
            Route::get('buy_product/cart-update','Inventory\InventoryPurchaseCartController@updatecart')->name('buy-update-cart');
            Route::post('buy_product/cart-submit','MoneyExchange\CurrencyPurchaseCartController@cartSubmit')->name('buy-cart-submit');
            Route::get('buy_prodect/buy_product_invoice','MoneyExchange\CurrencyPurchaseCartController@invTemporaryBuy')->name('buy-temporary');
            Route::get('available_list','Inventory\ProductInventoryController@available_list')->name('available');

            Route::get('sell_product','Inventory\ProductInventoryController@sell_product')->name('pro_sell');
            Route::get('sell_product/add-to-cart','MoneyExchange\CurrencyCartController@addToCart')->name('add-to-cart');
            Route::get('sell_product/add-to-cart-warrenty-product','Inventory\InventoryCartController@addToCartWarrentyProduct')->name('add-to-cart-warrenty-product');
            Route::get('sell_product/add-warrenty-product-sl-no','Inventory\InventoryCartController@addWarrentyProductSlNo')->name('add-warrenty-product-sl-no');
            Route::get('sell_product/remove-warrenty-product-sl','Inventory\InventoryCartController@removeWarrentyProductSlNo')->name('remove-warrenty-product-sl');
            Route::get('sell_product/get-cart','MoneyExchange\CurrencyCartController@getCartContent')->name('get-cart');
            Route::get('sell_product/cart-remove','MoneyExchange\CurrencyCartController@removecart')->name('remove-cart');
            Route::get('sell_product/cart-update','MoneyExchange\CurrencyCartController@updatecart')->name('update-cart');
            Route::post('sell_product/car-submit','MoneyExchange\CurrencyCartController@cartSubmit')->name('car-submit');
            Route::get('sell_product/sell_product_invoice','MoneyExchange\CurrencyCartController@invTemporaryProduct')->name('sell_temp_invoice');
        });
        Route::group(['prefix' => 'reports', 'as' => 'reports.'],function(){
            Route::get('buy_invoice','Inventory\ReportsController@buy_invoice_pdf')->name('buy_pdf');
            Route::get('buy_invoice_pdf','Inventory\ReportsController@buy_invoice_pdf_generate')->name('pdf-generate');
            Route::get('sell_invoice_pdf','Inventory\ReportsController@sell_invoice_pdf_generate')->name('sell-pdf-generate');
            Route::get('sell_reports','MoneyExchange\ReportsController@sell_reports')->name('sell-reports');
            Route::get('sell_reports_confirm','Inventory\ReportsController@sell_confirm_reports')->name('sell-confirm_reports');
            Route::get('sell_report_ajax','MoneyExchange\ReportsController@sell_report_ajax')->name('sell-reports-ajax');
            Route::get('buy_reports','MoneyExchange\ReportsController@buy_reports')->name('buy-reports');
            Route::get('buy_reports_confirm','Inventory\ReportsController@buy_reports_confirm')->name('buy-report-confirm');
            Route::get('combined_reports','MoneyExchange\ReportsController@combinedReports')->name('combined-reports');
            Route::get('buy_reports_ajax','Inventory\ReportsController@buy_reports_ajax')->name('buy-reports-ajax');
            Route::get('buy_pdf/{invoice}','Inventory\ReportsController@buy_reports_pdf')->name('buy-pdf');
            Route::get('sell_pdf/{invoice}','MoneyExchange\ReportsController@sell_reports_pdf')->name('sell-pdf');
            Route::get('sell_statement_download','Inventory\ReportsController@sellReportsDownload')->name('sell-reports-download');
            Route::get('sell_confirm/{invoice}','Inventory\ReportsController@sell_confirm')->name('sell-confirm');
            Route::get('buy_confirm/{invoice}','Inventory\ReportsController@buy_confirm')->name('buy-confirm');
            Route::get('buy_statement_download','Inventory\ReportsController@BuyReportsDownload')->name('buy-reports-download');

            Route::get('sell_print/{invoice}','MoneyExchange\ReportsController@sell_reports_pdf')->name('sell-print');
            Route::get('buy_print/{invoice}','MoneyExchange\ReportsController@buy_reports_pdf')->name('buy-print');

            Route::get('buy_return_print/{invoice}','Inventory\ReportsController@buyReturnInvoicePrint')->name('buy-return-print');
            Route::get('sell_return_print/{invoice}','Inventory\ReportsController@sellReturnInvoicePrint')->name('sell-return-print');


        });
        Route::group(['prefix' => 'damage' , 'as' => 'damage.'],function(){
            Route::get('add','MoneyExchange\CurrencyInventoryController@damage_add')->name('damage-add');
            Route::get('damage-ajax','MoneyExchange\CurrencyInventoryController@show_note_by_ajax')->name('damage-ajax');
            Route::get('list','MoneyExchange\CurrencyInventoryController@damage_list')->name('damage-list');
        });
        Route::group(['prefix' => 'profit-loss' , 'as' => 'profit-loss.'],function(){
            Route::get('profit','MoneyExchange\ProfitLossController@profit')->name('profit');
            Route::get('loss','MoneyExchange\ProfitLossController@loss')->name('loss');
        });

        Route::group(['prefix' => 'accounts','as' => 'accounts.'],  function(){
            Route::get('add-bank','MoneyExchange\BankAccountController@addBank')->name('add-bank');
            Route::post('add-bank','MoneyExchange\BankAccountController@storeBank')->name('add-bank');
            Route::get('bank-list','MoneyExchange\BankAccountController@showBankList')->name('bank-list');
            Route::get('delete-bank/{id}','Inventory\BankAccountController@deleteBank')->name('delete-bank');
            Route::get('update-bank/{id}','MoneyExchange\BankAccountController@editBank')->name('update-bank');
            Route::post('update-bank/{id}','MoneyExchange\BankAccountController@updateBank')->name('update-bank');
            Route::get('bank-diposit-withdraw','MoneyExchange\BankAccountController@bankDepositWithdrawShowForm')->name('bank-diposit-withdraw');
            Route::get('bank-statement-detail/{id}','MoneyExchange\BankAccountController@bankStatementDetails')->name('bank-statement-detail');

            Route::post('bank-diposit-withdraw','MoneyExchange\BankAccountController@bankDepositWithdrawStore')->name('bank-diposit-withdraw');
            Route::get('expense-categories','MoneyExchange\ExpenseCategoryController@showExpenseCategories')->name('expense-categories');
            Route::get('expenses','MoneyExchange\ExpenseCategoryController@showExpenses')->name('expenses');
            Route::post('expense-categories','Inventory\ExpenseCategoryController@storeExpenseCategory')->name('expense-categories');
            //Riaz's Created
            Route::get('cost-add','MoneyExchange\ExpenseCategoryController@add_expense_form')->name('cost-add');
            Route::post('expense_cost_add','Inventory\ExpenseCategoryController@add_expense_cost')->name('expense-cost-add');
            Route::get('expense_category_edit/{cat_id}','Inventory\ExpenseCategoryController@expense_category_edit')->name('expense-category-edit');
            Route::post('expense_category_update/{id}','Inventory\ExpenseCategoryController@expense_category_update')->name('expense-category-update');
            //

            //ledger
            Route::get('ledger','MoneyExchange\CurrencyLedgerController@ledger_insert_form')->name('ledger');
            Route::get('ledger-categories','MoneyExchange\CurrencyLedgerController@ledger_categories')->name('ledger-category');
            Route::post('ledger-category-insert','Inventory\InvLedgerController@insert_ledger_category')->name('ledger-category-insert');
            Route::get('ledger-category-update','Inventory\InvLedgerController@ledger_category_edit_ajax')->name('ledger-category-update');
            Route::post('ledger-category-update-submit','Inventory\InvLedgerController@ledger_category_update')->name('ledger-category-update-submit');
            Route::post('ledger-insert','Inventory\InvLedgerController@ledger_insert')->name('ledger-insert');
            Route::get('ledger-categories-load-ajax','Inventory\InvLedgerController@showAjaxLoadedLedgerCategory')->name('ledger-categories-load-ajax');
            Route::get('ledger-add','MoneyExchange\CurrencyLedgerController@ledger_add')->name('ledger-add');
            Route::post('ledger-data','Inventory\InvLedgerController@insert_ledger_data')->name('ledger-data');
            Route::get('ledger_cash_in_edit','Inventory\InvLedgerController@ledger_cash_in_ajax')->name('ledger-cash-in-edit');
            Route::get('ledger_cash_out_edit','Inventory\InvLedgerController@ledger_cash_out_ajax')->name('ledger-cash-out-edit');
            Route::post('update-ledger-data','Inventory\InvLedgerController@update_ledger_data')->name('update-ledger-data');
            Route::post('update-ledger-cash-out-data','Inventory\InvLedgerController@update_ledger_cash_out_data')->name('update-ledger-cash-out-data');
            Route::get('ledger-bank','Inventory\InvLedgerController@bank_ledger')->name('bank');
            Route::get('ledger-bank-balance','Inventory\InvLedgerController@ajaxBankBalance')->name('ledger-bank-balance');
            //
            Route::post('expenses','Inventory\ExpenseCategoryController@storeExpenses')->name('expenses');
            Route::get('ajax-load_expense','Inventory\ExpenseCategoryController@ajaxLoadExpense')->name('ajax-load_expense');
            Route::get('create-contra','MoneyExchange\BankAccountController@showContraForm')->name('create-contra');
            Route::get('contra-list','MoneyExchange\BankAccountController@showContraList')->name('contra-list');
            Route::post('create-contra','Inventory\BankAccountController@createContra')->name('create-contra');

            Route::get('general_ledger','MoneyExchange\BankAccountController@showGeneralLedgerForm')->name('general_ledger');
            Route::post('general_ledger','MoneyExchange\BankAccountController@showGeneralLedgerData')->name('general_ledger');


            //=====================17-11-19 ================

            Route::get('general_ledger_download','MoneyExchange\BankAccountController@downLoadGeneralLedger')->name('general_ledger_download');

            //=====================17-11-19 ================

//  ====================18-11-19 ===============
            Route::get('account-statement','MoneyExchange\BankAccountController@showAccountStatementForm')->name('account-statement');
            Route::post('account-statement','MoneyExchange\BankAccountController@showAccountStatementData')->name('account-statement');

            // ==================/18-11-19==================


            //=======================20-11-19===================
            Route::get('account-statement-download','Inventory\BankAccountController@downloadAccountStatement')->name('account-statement-download');
//================/20-11-19================
            // Routes for loading ajax for balance query

            Route::get('ajax-load-bank-balance','Inventory\BankAccountController@ajaxLoadBankBalance')->name('ajax-load-bank-balance');

            Route::get('ajax-load-supplier-balance','Inventory\BankAccountController@ajaxLoadSupplierBalance')->name('ajax-load-supplier-balance');

            Route::get('ajax-load-customer-balance','Inventory\BankAccountController@ajaxLoadCustomerBalance')->name('ajax-load-customer-balance');


            //============== Expense Voucher =============
            Route::get('expenses-voucher','MoneyExchange\BankAccountController@expensesVoucherForm')->name('expenses-voucher');
            Route::post('expenses-voucher','MoneyExchange\BankAccountController@expensesVoucherStore')->name('expenses-voucher');

            //==================== 16-11-19 =================

            Route::get('expense-categories-load-ajax','Inventory\ExpenseCategoryController@showAjaxLoadedExpensesCategory')->name('expense-categories-load-ajax');

            Route::get('expense-load-ajax','Inventory\ExpenseCategoryController@showAjaxLoadedExpenses')->name('expense-load-ajax');

        });
    });

    Route::group(['as' => 'payroll.' , 'prefix' => 'payroll'],function(){
        Route::get('/employee-list','UserController@employee_list')->name('employee_list');
        Route::get('/employee_dob', 'UserController@employee_dob')->name('employee_date_of_birth');
        Route::get('/employee_bg', 'UserController@employee_bg')->name('employee_blood_group');
        Route::get('/blood-donates', 'UserController@getBloodDonationUsers')->name('blood_donates.index');
        Route::get('/blood-donates/create',  'UserController@bloodDonate')->name('blood_donates.create');
        Route::post('/blood-donates', 'UserController@storeBloodDonation')->name('blood_donates.store');
        Route::get('/blood-donates/{id}/edit', 'UserController@editBloodDonation')->name('blood_donates.edit');
        Route::put('/blood-donates/{id}','UserController@updateBloodDonation')->name('blood_donates.update');
        Route::delete('/blood-donates/{id}',  'UserController@deleteBloodDonation')->name('blood_donates.delete');
        Route::get('/today_birth', 'UserController@daodayBirthDate');
        Route::get('/employee-details/{id}','UserController@employee_details')->name('employee_details');
        Route::post('check-in','AttendanceController@attendanceCheckIn')->name('check-in');
        Route::post('check-out','AttendanceController@attendanceCheckOut')->name('check-out');
        Route::get('show','AttendanceController@attendanceShow')->name('attendance-show');
        Route::get('total-show','AttendanceController@attendanceTotalShow')->name('total-show');
         Route::get('/get-employee-details', 'AttendanceController@getEmployeeDetails')->name('get-employee-details');
        Route::get('attendance-print','AttendanceController@detailsDownloadPrint')->name('attendance-print');
        //today attendance show route

        Route::get('today-attendance','AttendanceController@todayAttendanceShow')->name('today-attendance-show');
        Route::get('today-attendance-print','AttendanceController@todayAttendancePrint')->name('today-attendance-print');
        Route::get('office-holiday','AttendanceController@officeWeekendHoliday')->name('office-holiday');
        Route::post('add-weekend','AttendanceController@addWeekends')->name('add-weekend');
        Route::get('add-holiday','AttendanceController@officeHoliday')->name('add-holiday');
        Route::get('/get-existing-holidays', 'AttendanceController@getExistingHolidays')->name('getExistingHolidays');
        Route::post('holidays', 'AttendanceController@storeHolidays')->name('holidays');


        Route::get('attendance-ajax','AttendanceController@attendanceDetailsAjax')->name('attendance-ajax');

        Route::group(['as' => 'shift.' , 'prefix' => 'shift'],function(){
            Route::get('add','AttendanceController@addShift')->name('add');
            Route::post('add-shift','AttendanceController@addShiftStore')->name('add-shift');

            Route::get('list','AttendanceController@shiftList')->name('list');
            Route::get('shift-ajax','AttendanceController@shiftAjaxUpdate')->name('shift-ajax');
            Route::post('shift-update/{id}','AttendanceController@shiftUpdate')->name('shift-update');

            Route::post('update-shift/{id}','AttendanceController@updateShiftStore')->name('update-shift');
            Route::get('edit-shift/{id}','AttendanceController@editShift')->name('editShift');
        });
    });
    //Inventory Controller End
    //leave management route
    Route::group(['as' => 'leave.' , 'prefix' => 'leave'],function(){
        Route::get('leave-type-list','LeaveControlle@leaveTypeList')->name('leaveTypeList');
        Route::get('leave-type-add','LeaveControlle@leaveTypeadd')->name('leaveTypeadd');
        Route::post('leave-type-store','LeaveControlle@leaveTypeStore')->name('leaveTypeStore');
        Route::get('leave-type-delete/{id}','LeaveControlle@leaveTypeDelete')->name('leaveTypeDelete');

        Route::get('end-date-ajax','LeaveControlle@getEndDate')->name('end-date-ajax');

        Route::get('leave-request-search','LeaveControlle@leaveRequestSearchByStatus')->name('leaveRequestSearchByStatus');

        Route::get('leave-request-delete/{id}','LeaveControlle@leaveRequestProcessDelete')->name('leaveRequestProcessDelete');


    });
});
Auth::routes();

Route::get('leave-application','LeaveControlle@leaveApplication')->name('leaveApplication')->middleware('auth');
Route::post('leave-application-submit','LeaveControlle@leaveApplicationSubmit')->name('leaveApplicationSubmit')->middleware('auth');
Route::get('leave-request','LeaveControlle@leaveRequest')->name('leaveRequest')->middleware('auth');

//leave request processing

Route::get('leave-processing/{id}','LeaveControlle@leaveRequestProcess')->name('leaveRequestProcess')->middleware('auth');
Route::post('leave-accepting/{id}','LeaveControlle@leaveAccepting')->name('leaveAccepting')->middleware('auth');


Route::get('/', 'UserController@home')->name('home')->middleware('auth');
Route::get('/search', 'SearchController@search')->name('search');
Route::get('/top-selling-products', 'DashboardController@getTopSellingProducts')->name('topSellingProducts');
// Route::get('/index', 'HomeController@dashboard')->name('dashboard');
// Route::get('/followup', 'HomeController@dashboard2')->name('followup');
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');


//division district ajax
Route::get('distic_list_by_divison_id','UserController@distic_list_by_divison_id');
Route::get('thana_list_by_district_id','UserController@thana_list_by_district_id');


Route::post('send-otp','UserController@send_otp')->name('send-otp');
Route::post('change-name','UserController@changeName');

// Deactivate user who inactive last 5 days
Route::get('cron_user_deactivate','UserController@cron_user_deactivate');

Route::post('change-password','UserController@change_password');


//fines

Route::get('/fines', 'FineController@index')->middleware('au_access:fines')->name('fines.index');
Route::get('/fines/create', 'FineController@create')->middleware('au_access:fines')->name('inventory.accounts.fines.create');
Route::post('/fines', 'FineController@store')->middleware('au_access:fines')->name('fines.store');
Route::get('/fines/{fine}/edit', 'FineController@edit')->middleware('au_access:fines')->name('fines.edit');
Route::put('/fines/{fine}', 'FineController@update')->middleware('au_access:fines')->name('fines.update');
Route::delete('/fines/{fine}', 'FineController@destroy')->middleware('au_access:fines')->name('fines.destroy');


// Route for listing all employee fines
Route::get('employee_fines', 'EmployeeFineController@index')->middleware('au_access:employee_fines')->name('employee_fines.index');

// Route for showing the form to create a new employee fine
Route::get('employee_fines/create', 'EmployeeFineController@create')->middleware('au_access:employee_fines')->name('inventory.accounts.employee_fines.create');

// Route for storing a new employee fine
Route::post('employee_fines', 'EmployeeFineController@store')->middleware('au_access:employee_fines')->name('employee_fines.store');

// Route for showing the form to edit an existing employee fine
Route::get('employee_fines/{id}/edit', 'EmployeeFineController@edit')->middleware('au_access:employee_fines')->name('employee_fines.edit');

// Route for updating an existing employee fine
Route::put('employee_fines/{id}', 'EmployeeFineController@update')->middleware('au_access:employee_fines')->name('employee_fines.update');

// Route for deleting an existing employee fine
Route::delete('employee_fines/{id}', 'EmployeeFineController@destroy')->middleware('au_access:employee_fines')->name('employee_fines.destroy');
Route::get('get_customer', 'OmsController@convence_get_customer')
->name('get_customer');


// Route for displaying a listing of the salary records
Route::get('salary-records', 'SalaryRecordController@salary_advanced_create')
    ->middleware('au_access:payroll')
    ->name('salary-records.salary_advanced_create');

// Route for showing the form to create a new salary record


// Route for storing a new salary record
Route::post('salary-records', 'SalaryRecordController@salary_advanced_store')
    ->middleware('au_access:payroll')
    ->name('salary-records.salary_advanced_store');

// Route for displaying a specific salary record
Route::get('salary-records/{id}', 'SalaryRecordController@salary_advanced_show')
    ->middleware('au_access:payroll')
    ->name('salary-records.salary_advanced_show');

// Route for showing the form to edit a specific salary record
Route::get('salary-records/{id}/edit', 'SalaryRecordController@salary_advanced_edit')
    ->middleware('au_access:payroll')
    ->name('salary-records.salary_advanced_edit');

// Route for updating a specific salary record
Route::put('salary-records/{id}', 'SalaryRecordController@salary_advanced_update')
    ->middleware('au_access:payroll')
    ->name('salary-records.salary_advanced_update');

// Route for deleting a specific salary record
Route::delete('salary-records/{id}', 'SalaryRecordController@destroy')
    ->middleware('au_access:payroll')
    ->name('salary-records.destroy');

Route::get('/salary/generate', 'SalaryController@showSalaryGenerationForm')
    ->middleware('au_access:payroll') // Ensure only users with the 'payroll' access can view the form
    ->name('salary-generate.form');

// Route to handle the salary generation process
Route::post('/salary/generate', 'SalaryController@generate')
    ->middleware('au_access:payroll') 
    ->name('salary-generate.generate');


// Route to approve salary
Route::get('/salary/approve/{monthYear}', 'SalaryController@approveSalary')->name('salary-approval');


// Route to view salary chart
Route::get('/salary/chart/{month}/{year}', 'SalaryController@viewSalaryChart')
    ->name('salary-chart');


Route::get('/salary/print/{month}/{year}', 'SalaryController@printSalaryChart')
    ->name('salary-print');
    




Route::resource('quotations', QuotationController::class);

Route::post('/quotation/add-to-cart', [QuotationController::class, 'addToCart'])->name('quotation.add-to-cart');
Route::get('/quotation/cart', [QuotationController::class, 'viewCart'])->name('quotation.view-cart');
Route::post('/quotation/remove-from-cart', [QuotationController::class, 'removeFromCart'])->name('quotation.remove-from-cart');
Route::post('/quotation/update-cart', [QuotationController::class, 'updateCart'])->name('quotation.update-cart');
Route::post('/quotation/clear-cart', [QuotationController::class, 'clearCart'])->name('quotation.clear-cart');
Route::get('quotations/{id}/print', [QuotationController::class, 'printQuotation'])->name('quotations.print');


// Employee Activity Routes
Route::get('hrm/employee-activity', 'EmployeeActivityController@index')->name('employee-activity.index');
Route::get('hrm/employee-activity/today-activity', 'EmployeeActivityController@daily_activity')->name('employee-activity.today-activity');
Route::get('hrm/employee-activity/total-activity', 'EmployeeActivityController@total_activity')->name('employee-activity.total-activity');
Route::get('hrm/employee-activity/create', 'EmployeeActivityController@create')->name('employee-activity.create');
Route::post('hrm/employee-activity', 'EmployeeActivityController@store')->name('employee-activity.store');
Route::get('hrm/employee-activity/{id}', 'EmployeeActivityController@show')->name('employee-activity.show');
Route::get('hrm/employee-activity/edit/{id}', 'EmployeeActivityController@edit')->name('employee-activity.edit');
Route::put('hrm/employee-activity/update/{id}', 'EmployeeActivityController@update')->name('employee-activity.update');
Route::delete('hrm/employee-activity/{id}', 'EmployeeActivityController@destroy')->name('employee-activity.destroy');
Route::get('hrm/employee-activity/accept/{id}', 'EmployeeActivityController@accept')->name('employee-activity.accept');
Route::get('hrm/employee-activity/reject/{id}', 'EmployeeActivityController@reject')->name('employee-activity.reject');




