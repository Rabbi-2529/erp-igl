@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Transfer Employees')
@section('human_resource_class', 'active')
@section('employee_management_class', 'active')
@section('transfer_employees_class', 'active')

@section('content')
<style>
    .employee-checkbox {
        transform: scale(1.3);
        cursor: pointer;
    }
    .employee-transfer-card {
        border-radius: 12px;
        border: 1px solid #e0e6ef;
        margin-bottom: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        overflow: hidden;
        position: relative;
    }
    .employee-transfer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 123, 255, 0.15);
        border-color: #c3dafe;
    }
    .employee-transfer-card.selected {
        background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
        border-color: #3b82f6;
        border-left: 5px solid #3b82f6;
    }
    .employee-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .employee-details {
        flex-grow: 1;
    }
    .user-type-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .transfer-summary-bar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }
    .branch-selector-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #e0e6ef;
    }
    .filter-card {
        background: #f8fafc;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #e2e8f0;
    }
    .transfer-action-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 20px;
        box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }
    .transfer-action-bar.active {
        transform: translateY(0);
    }
    .badge-count {
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        margin-left: 8px;
    }
    .gradient-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    .gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    .employee-info-row {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    .employee-info-row i {
        width: 20px;
        color: #6b7280;
        margin-right: 8px;
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        background: white;
        border-radius: 12px;
        border: 2px dashed #d1d5db;
    }
    .empty-state-icon {
        font-size: 80px;
        color: #d1d5db;
        margin-bottom: 20px;
    }
    .pagination-wrapper {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-top: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .form-control-custom {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        transition: all 0.3s;
        padding: 10px 15px;
    }
    .form-control-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>

<section class="content">
    <section class="content-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 20px; color: white; margin-bottom: 30px;">
        <h1 style="margin: 0;">
            <i class="fa fa-exchange-alt"></i> Transfer Employees
            <small style="color: rgba(255,255,255,0.9); font-size: 16px;">Move employees between branches seamlessly</small>
        </h1>
        <ol class="breadcrumb" style="background: rgba(255,255,255,0.1); border-radius: 20px; padding: 10px 20px; margin-top: 10px;">
            <li><a href="{{ url('/dashboard') }}" style="color: white;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('company.hrm.employees.list') }}" style="color: rgba(255,255,255,0.9);">Employee List</a></li>
            <li class="active" style="color: white;">Transfer Employees</li>
        </ol>
    </section>

    <div class="row">
        <div class="col-md-12">
            <!-- Transfer Summary Bar -->
            <div class="transfer-summary-bar">
                <div class="row">
                    <div class="col-md-8">
                        <h3 style="margin: 0; color: white;">
                            <i class="fa fa-random"></i> Employee Transfer Portal
                        </h3>
                        <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9);">
                            Select employees and transfer them to another branch with ease
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('company.hrm.employees.transfer.history') }}" 
                           class="btn btn-outline-light btn-lg" 
                           style="border-radius: 25px; padding: 8px 25px;">
                            <i class="fa fa-history"></i> View History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Branch Selection Card -->
            <div class="branch-selector-card">
                <form id="transferForm" action="{{ route('company.hrm.employees.transfer') }}" method="POST">
                    @csrf
                    <h4 style="color: #374151; margin-bottom: 25px; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px;">
                        <i class="fa fa-map-marker-alt text-primary"></i> Transfer Destination
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_branch_id" style="font-weight: 600; color: #4b5563; margin-bottom: 8px;">
                                    <i class="fa fa-building text-primary"></i> Destination Branch
                                </label>
                                <select name="new_branch_id" id="new_branch_id" 
                                        class="form-control form-control-custom" 
                                        style="height: 50px;" required>
                                    <option value="">-- Select Destination Branch --</option>
                                    @foreach($allBranches as $branch)
                                        <option value="{{ $branch->branch_id }}">
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="display: block; margin-top: 8px;">
                                    <i class="fa fa-info-circle"></i> All selected employees will be transferred to this branch
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="transfer_date" style="font-weight: 600; color: #4b5563; margin-bottom: 8px;">
                                    <i class="fa fa-calendar-alt text-primary"></i> Transfer Date
                                </label>
                                <input type="date" name="transfer_date" id="transfer_date" 
                                       class="form-control form-control-custom" 
                                       style="height: 50px;"
                                       value="{{ Carbon::today()->format('Y-m-d') }}"
                                       min="{{ Carbon::today()->format('Y-m-d') }}"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="transfer_reason" style="font-weight: 600; color: #4b5563; margin-bottom: 8px;">
                                    <i class="fa fa-clipboard text-primary"></i> Reason for Transfer
                                </label>
                                <textarea name="transfer_reason" id="transfer_reason" 
                                          class="form-control form-control-custom" 
                                          rows="3" 
                                          placeholder="Briefly describe the reason for transferring these employees..."
                                          style="resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filters Card -->
            <div class="filter-card">
                <h5 style="color: #374151; margin-bottom: 20px; font-weight: 600;">
                    <i class="fa fa-filter text-primary"></i> Filter Employees
                </h5>
                <form method="GET" action="{{ route('company.hrm.employees.transfer.list') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #6b7280; font-size: 0.9rem;">Branch</label>
                                <select name="branch_id" id="filter_branch_id" class="form-control form-control-custom">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branch_id == $branch->branch_id ? 'selected' : '' }}>
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #6b7280; font-size: 0.9rem;">Department</label>
                                <select name="department_id" id="department_id" class="form-control form-control-custom">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ $department_id == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #6b7280; font-size: 0.9rem;">Employee Type</label>
                                <select name="user_type" id="user_type" class="form-control form-control-custom">
                                    <option value="">All Types</option>
                                    <option value="4" {{ $user_type == '4' ? 'selected' : '' }}>Admin</option>
                                    <option value="5" {{ $user_type == '5' ? 'selected' : '' }}>Team Leader</option>
                                    <option value="6" {{ $user_type == '6' ? 'selected' : '' }}>Employee</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-weight: 500; color: #6b7280; font-size: 0.9rem;">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" id="search" class="form-control form-control-custom" 
                                           placeholder="Search employees..." value="{{ $search }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary" style="border-radius: 0 8px 8px 0;">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Employee List Section -->
            <div class="row">
                <div class="col-md-12">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h4 style="margin: 0; color: #374151;">
                            <i class="fa fa-users text-primary"></i> Select Employees
                            <span class="badge badge-primary" id="selectedCount" 
                                  style="font-size: 14px; padding: 5px 12px; border-radius: 20px; margin-left: 10px;">
                                0 selected
                            </span>
                        </h4>
                        
                        <div class="form-check" style="margin: 0;">
                            <input type="checkbox" class="form-check-input" 
                                   id="selectAllEmployees" onchange="selectAllEmployees()"
                                   style="transform: scale(1.3);">
                            <label class="form-check-label" for="selectAllEmployees" 
                                   style="font-weight: 500; color: #6b7280; cursor: pointer;">
                                Select All on This Page
                            </label>
                        </div>
                    </div>
                    
                    @if($employees->count() > 0)
                        <div class="row" id="employeeGrid">
                            @foreach($employees as $employee)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card employee-transfer-card" 
                                         id="employeeCard{{ $employee->au_id }}"
                                         onclick="toggleEmployeeSelection({{ $employee->au_id }})"
                                         style="cursor: pointer;">
                                        <div class="card-body" style="padding: 20px;">
                                            <div style="display: flex; align-items: flex-start;">
                                                <div class="employee-avatar">
                                                    {{ strtoupper(substr($employee->au_name, 0, 1)) }}
                                                </div>
                                                
                                                <div class="employee-details">
                                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                                        <div>
                                                            <h5 style="margin: 0 0 5px 0; color: #1f2937; font-weight: 600;">
                                                                {{ $employee->au_name }}
                                                            </h5>
                                                            <span class="user-type-badge 
                                                                @if($employee->au_user_type == 5) badge-warning
                                                                @elseif($employee->au_user_type == 6) badge-info
                                                                @else badge-secondary @endif">
                                                                @if($employee->au_user_type == 5)
                                                                    <i class="fa fa-crown"></i> Team Leader
                                                                @elseif($employee->au_user_type == 6)
                                                                    <i class="fa fa-user"></i> Employee
                                                                @else
                                                                    <i class="fa fa-user-shield"></i> Admin
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <input type="checkbox" 
                                                               class="employee-checkbox" 
                                                               name="employee_ids[]" 
                                                               value="{{ $employee->au_id }}"
                                                               id="emp{{ $employee->au_id }}"
                                                               onchange="updateSelectionCount()"
                                                               style="margin-top: 5px;">
                                                    </div>
                                                    
                                                    <div class="employee-info-row">
                                                        <i class="fa fa-id-card"></i>
                                                        <span style="color: #6b7280; font-size: 0.9rem;">
                                                            {{ $employee->au_user_id }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="employee-info-row">
                                                        <i class="fa fa-building"></i>
                                                        <span style="color: #6b7280; font-size: 0.9rem;">
                                                            {{ $employee->branch->branch_name ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                    
                                                    @if($employee->department)
                                                        <div class="employee-info-row">
                                                            <i class="fa fa-sitemap"></i>
                                                            <span style="color: #6b7280; font-size: 0.9rem;">
                                                                {{ $employee->department->name }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="employee-info-row">
                                                        <i class="fa fa-phone"></i>
                                                        <span style="color: #6b7280; font-size: 0.9rem;">
                                                            {{ $employee->au_mobile }}
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="employee-info-row">
                                                        <i class="fa fa-envelope"></i>
                                                        <span style="color: #6b7280; font-size: 0.9rem; word-break: break-all;">
                                                            {{ $employee->au_email }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-wrapper">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p style="margin: 0; color: #6b7280;">
                                        Showing <strong>{{ $employees->firstItem() ?? 0 }}</strong> to 
                                        <strong>{{ $employees->lastItem() ?? 0 }}</strong> of 
                                        <strong>{{ $employees->total() }}</strong> employees
                                    </p>
                                </div>
                                <div>
                                    {{ $employees->links() }}
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fa fa-users-slash"></i>
                            </div>
                            <h3 style="color: #6b7280; margin-bottom: 15px;">No Employees Found</h3>
                            <p style="color: #9ca3af; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">
                                No employees match your current filters. Try adjusting your search criteria or clear the filters to see all employees.
                            </p>
                            <a href="{{ route('company.hrm.employees.transfer.list') }}" 
                               class="btn btn-primary btn-lg" style="border-radius: 25px; padding: 10px 30px;">
                                <i class="fa fa-redo"></i> Clear Filters
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transfer Action Bar -->
<div class="transfer-action-bar" id="transferActionBar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div style="display: flex; align-items: center;">
                    <div style="background: #3b82f6; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="fa fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; color: #1f2937; font-weight: 600;" id="selectionSummary">
                            Ready to Transfer
                        </h5>
                        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 0.9rem;">
                            <span id="selectedEmployeesText">0 employees</span> selected for transfer
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <button type="button" class="btn btn-secondary btn-lg" 
                        onclick="clearSelections()" 
                        style="border-radius: 25px; padding: 10px 25px; margin-right: 10px;">
                    <i class="fa fa-times"></i> Clear All
                </button>
                <button type="button" class="btn btn-primary btn-lg gradient-primary" 
                        onclick="validateAndSubmit()" 
                        id="transferBtn"
                        style="border-radius: 25px; padding: 10px 30px; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);"
                        disabled>
                    <i class="fa fa-paper-plane"></i> Transfer 
                    <span class="badge-count" id="transferCount">0</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedEmployees = new Set();
let isActionBarVisible = false;

function toggleEmployeeSelection(employeeId) {
    const checkbox = document.getElementById('emp' + employeeId);
    const card = document.getElementById('employeeCard' + employeeId);
    
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        selectedEmployees.add(employeeId);
        card.classList.add('selected');
        card.style.boxShadow = '0 15px 35px rgba(59, 130, 246, 0.2)';
    } else {
        selectedEmployees.delete(employeeId);
        card.classList.remove('selected');
        card.style.boxShadow = '';
    }
    
    updateSelectionCount();
    toggleActionBar();
}

function selectAllEmployees() {
    const selectAll = document.getElementById('selectAllEmployees');
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        const employeeId = checkbox.value;
        const card = document.getElementById('employeeCard' + employeeId);
        
        if (selectAll.checked) {
            selectedEmployees.add(employeeId);
            if (card) {
                card.classList.add('selected');
                card.style.boxShadow = '0 15px 35px rgba(59, 130, 246, 0.2)';
            }
        } else {
            selectedEmployees.delete(employeeId);
            if (card) {
                card.classList.remove('selected');
                card.style.boxShadow = '';
            }
        }
    });
    
    updateSelectionCount();
    toggleActionBar();
}

function clearSelections() {
    const checkboxes = document.querySelectorAll('.employee-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        const employeeId = checkbox.value;
        const card = document.getElementById('employeeCard' + employeeId);
        
        selectedEmployees.delete(employeeId);
        if (card) {
            card.classList.remove('selected');
            card.style.boxShadow = '';
        }
    });
    
    document.getElementById('selectAllEmployees').checked = false;
    updateSelectionCount();
    toggleActionBar();
}

function updateSelectionCount() {
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    const count = checkboxes.length;
    
    // Update counters
    document.getElementById('selectedCount').textContent = count + ' selected';
    document.getElementById('transferCount').textContent = count;
    document.getElementById('selectedEmployeesText').textContent = 
        count + ' employee' + (count !== 1 ? 's' : '');
    
    // Update summary text
    let summaryText = '';
    if (count === 0) {
        summaryText = 'No employees selected';
    } else if (count === 1) {
        summaryText = '1 employee ready for transfer';
    } else {
        summaryText = count + ' employees ready for transfer';
    }
    document.getElementById('selectionSummary').textContent = summaryText;
    
    // Enable/disable transfer button
    const transferBtn = document.getElementById('transferBtn');
    const branchSelect = document.getElementById('new_branch_id');
    
    if (count > 0 && branchSelect.value) {
        transferBtn.disabled = false;
        transferBtn.style.opacity = '1';
    } else {
        transferBtn.disabled = true;
        transferBtn.style.opacity = '0.7';
    }
}

function toggleActionBar() {
    const actionBar = document.getElementById('transferActionBar');
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    
    if (checkboxes.length > 0 && !isActionBarVisible) {
        actionBar.classList.add('active');
        isActionBarVisible = true;
    } else if (checkboxes.length === 0 && isActionBarVisible) {
        actionBar.classList.remove('active');
        isActionBarVisible = false;
    }
}

function validateAndSubmit() {
    const branchId = document.getElementById('new_branch_id').value;
    const transferDate = document.getElementById('transfer_date').value;
    const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
    const branchName = document.getElementById('new_branch_id').options[document.getElementById('new_branch_id').selectedIndex].text;
    
    if (checkboxes.length === 0) {
        showNotification('Please select at least one employee to transfer.', 'warning');
        return;
    }
    
    if (!branchId) {
        showNotification('Please select a destination branch.', 'warning');
        return;
    }
    
    if (!transferDate) {
        showNotification('Please select a transfer date.', 'warning');
        return;
    }
    
    // Show confirmation modal
    Swal.fire({
        title: 'Confirm Transfer',
        html: `<div style="text-align: left; padding: 20px;">
                <p>You are about to transfer <strong>${checkboxes.length} employee${checkboxes.length !== 1 ? 's' : ''}</strong> to:</p>
                <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p style="margin: 0; color: #0369a1; font-weight: 600;">
                        <i class="fa fa-building"></i> ${branchName}
                    </p>
                    <p style="margin: 5px 0 0 0; color: #64748b;">
                        <i class="fa fa-calendar"></i> ${transferDate}
                    </p>
                </div>
                <p>This action cannot be undone. Do you want to proceed?</p>
              </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Transfer Now',
        cancelButtonText: 'Cancel',
        width: 600,
        padding: '2em',
        customClass: {
            popup: 'border-radius-20'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing Transfer...',
                text: 'Please wait while we transfer the employees',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            document.getElementById('transferForm').submit();
        }
    });
}

function showNotification(message, type = 'info') {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: colors[type] || colors.info,
        stopOnFocus: true,
    }).showToast();
}

// Update transfer button when branch is selected
document.getElementById('new_branch_id').addEventListener('change', function() {
    updateSelectionCount();
});

// Initialize date input
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transfer_date').min = today;
    
    // Add smooth scrolling to action bar
    document.querySelectorAll('.employee-transfer-card').forEach(card => {
        card.style.transition = 'all 0.3s ease';
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('success') }}", 'success');
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('warning') }}", 'warning');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("{{ session('error') }}", 'error');
    });
</script>
@endif

<style>
.swal2-popup.border-radius-20 {
    border-radius: 20px !important;
}
</style>

@endsection