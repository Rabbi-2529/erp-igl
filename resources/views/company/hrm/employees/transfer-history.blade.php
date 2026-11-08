@php use Carbon\Carbon; @endphp
@extends('layouts.master')

@section('title', 'Transfer History')
@section('human_resource_class', 'active')
@section('employee_management_class', 'active')
@section('transfer_history_class', 'active')

@section('content')
<style>
    .history-card {
        border-left: 4px solid #17a2b8;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .history-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .badge-transfer {
        background: linear-gradient(45deg, #17a2b8, #20c997);
        color: white;
    }
</style>

<section class="content">
    <section class="content-header">
        <h1>Employee Transfer History</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li><a href="{{ route('company.hrm.employees.transfer.list') }}">Transfer Employees</a></li>
            <li class="active">Transfer History</li>
        </ol>
    </section>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Transfer Records</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('company.hrm.employees.transfer.history') }}" class="form-inline mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="branch_id">Branch:</label>
                                    <select name="branch_id" id="branch_id" class="form-control input-sm">
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
                                    <label for="employee_id">Employee:</label>
                                    <select name="employee_id" id="employee_id" class="form-control input-sm">
                                        <option value="">All Employees</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->au_id }}" {{ $employee_id == $emp->au_id ? 'selected' : '' }}>
                                                {{ $emp->au_name }} ({{ $emp->au_user_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">From Date:</label>
                                    <input type="date" name="start_date" id="start_date" 
                                           class="form-control input-sm" value="{{ $start_date }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">To Date:</label>
                                    <input type="date" name="end_date" id="end_date" 
                                           class="form-control input-sm" value="{{ $end_date }}">
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fa fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- History List -->
                    @if($transferHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transfer Date</th>
                                        <th>Reason</th>
                                        <th>Transferred By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transferHistory as $record)
                                        <tr class="history-card">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $record->au_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $record->au_user_id }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-light">
                                                    <i class="fa fa-building"></i> {{ $record->old_branch_name }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-transfer">
                                                    <i class="fa fa-building"></i> {{ $record->new_branch_name }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ Carbon::parse($record->transfer_date)->format('d M, Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($record->transfer_reason)
                                                    {{ Str::limit($record->transfer_reason, 50) }}
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Carbon::parse($record->created_at)->format('d M, Y h:i A') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    {{ Carbon::parse($record->created_at)->diffForHumans() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="text-center">
                            {{ $transferHistory->links() }}
                        </div>

                        <!-- Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon"><i class="fa fa-exchange"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Transfers</span>
                                        <span class="info-box-number">{{ $transferHistory->total() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Unique Employees</span>
                                        <span class="info-box-number">
                                            {{ $transferHistory->unique('employee_id')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon"><i class="fa fa-building"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Branches Involved</span>
                                        <span class="info-box-number">
                                            {{ $transferHistory->unique('new_branch_id')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-danger">
                                    <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">This Month</span>
                                        <span class="info-box-number">
                                            {{ $transferHistory->where('transfer_date', '>=', Carbon::now()->startOfMonth())->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-3x"></i>
                            <h3>No transfer history found</h3>
                            <p>No employee transfers have been recorded yet.</p>
                            <a href="{{ route('company.hrm.employees.transfer.list') }}" class="btn btn-primary">
                                <i class="fa fa-exchange"></i> Transfer Employees
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Date range picker
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
        
        endDate.addEventListener('change', function() {
            startDate.max = this.value;
        });
    }
});
</script>
@endsection