@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Employee List')
@section('human_resource_class', 'active')
@section('employee_management_class', 'active')
@section('employee_list_class', 'active')

@section('content')
<style>
.employee-card {
    border: 1px solid #e3e3e3;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #fff;
    transition: all 0.3s ease;
}
.employee-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.status-badge {
    font-size: 0.8rem;
    padding: 4px 8px;
}
.user-type-badge {
    font-size: 0.7rem;
    padding: 3px 6px;
}
</style>

<section class="content">
    <section class="content-header">
        <h1>Employee Management</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Employee List</li>
        </ol>
    </section>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalEmployees }}</h3>
                    <p>Total Employees</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
                <a href="{{ route('company.hrm.employees.list') }}" class="small-box-footer">View All <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $activeEmployees }}</h3>
                    <p>Active Employees</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <a href="{{ route('company.hrm.employees.list') }}?status=active" class="small-box-footer">View Active <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $teamLeaders }}</h3>
                    <p>Team Leaders</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-secret"></i>
                </div>
                <a href="{{ route('company.hrm.employees.list') }}?user_type=5" class="small-box-footer">View Team Leaders <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $regularUsers }}</h3>
                    <p>Regular Users</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user"></i>
                </div>
                <a href="{{ route('company.hrm.employees.list') }}?user_type=6" class="small-box-footer">View Users <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Filter Employees</h3>
         
        </div>
        <div class="box-body">
            <form action="{{ route('company.hrm.employees.list') }}" method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="branch_id" class="mr-1">Branch:</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->branch_id }}" {{ $branch_id == $branch->branch_id ? 'selected' : '' }}>
                            {{ $branch->branch_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="department_id" class="mr-1">Department:</label>
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $department_id == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="user_type" class="mr-1">User Type:</label>
                    <select name="user_type" id="user_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="4" {{ $user_type == '4' ? 'selected' : '' }}>Admin</option>
                        <option value="5" {{ $user_type == '5' ? 'selected' : '' }}>Team Leader</option>
                        <option value="6" {{ $user_type == '6' ? 'selected' : '' }}>User</option>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="status" class="mr-1">Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                 <div class="form-group mt-3" style="margin-top:22px;">
                <button type="submit" class="btn btn-primary ">
                    <i class="fa fa-search"></i> Filter
                </button>
                <a href="{{ route('company.hrm.employees.list') }}" class="btn btn-default">
                    <i class="fa fa-refresh"></i> Reset
                </a>
                </div>
            </form>
        </div>
    </div>

   <!-- Employees Table -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Employee List ({{ $employees->count() }} employees found)</h3>
        </div>
        <div class="box-body table-responsive">
            @if($employees->count() > 0)
                      <table class="table table-bordered table-striped table-hover" id="employee-activity-table">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Mobile</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $index => $employee)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $employee->au_user_id ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                            
                                        <div>
                                            <strong>{{ $employee->au_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $employee->au_email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $employee->branch->branch_name ?? 'N/A' }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $employee->au_mobile ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge user-type-badge 
                                        @if($employee->au_user_type == 4) badge-danger
                                        @elseif($employee->au_user_type == 5) badge-warning
                                        @else badge-info @endif">
                                        @if($employee->au_user_type == 4) Admin
                                        @elseif($employee->au_user_type == 5) Team Leader
                                        @else User
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="badge status-badge badge-{{ $employee->au_status ? 'success' : 'danger' }}">
                                        {{ $employee->au_status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <div class="btn-group">
                                        <a href="{{ route('company.hrm.employees.detail', $employee->au_id) }}" 
                                           class="btn btn-xs btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('company.hrm.employees.edit', $employee->au_id) }}" 
                                           class="btn btn-xs btn-primary" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('company.hrm.employees.toggle-status', $employee->au_id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-{{ $employee->au_status ? 'warning' : 'success' }}"
                                                    title="{{ $employee->au_status ? 'Deactivate' : 'Activate' }}"
                                                    onclick="return confirm('Are you sure you want to {{ $employee->au_status ? 'deactivate' : 'activate' }} this employee?')">
                                                <i class="fa fa-{{ $employee->au_status ? 'times' : 'check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" class="text-center">
                                <small class="text-muted">
                                    Showing {{ $employees->count() }} employees
                                    @if($branch_id || $department_id || $user_type || $status != 'active')
                                        with applied filters
                                    @endif
                                </small>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-users fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Employees Found</h4>
                    <p class="text-muted">No employees match the selected criteria.</p>
                    <a href="{{ route('company.hrm.employees.list') }}" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> Reset Filters
                    </a>
                    <a href="{{ route('company.hrm.employees.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Add New Employee
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@section('custom_script')
<script>
$(document).ready(function() {
    var table = $('#employee-activity-table').DataTable();

    // Initialize datepicker
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
});

// Show full description in modal
$('#viewDescriptionModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var title = button.data('title');
    var user = button.data('user');
    var branch = button.data('branch');
    var date = button.data('date');
    var description = button.data('description');
    
    var modal = $(this);
    modal.find('#activityTitle').text(title);
    modal.find('#activityUser').text(user);
    modal.find('#activityBranch').text(branch);
    modal.find('#activityDate').text(date);
    modal.find('#activityDescription').text(description);
});
</script>
@endsection