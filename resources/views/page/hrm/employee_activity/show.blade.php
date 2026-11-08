@extends('layouts.master')

@section('title', 'Employee Activity Details')

@section('human_resource_class', 'active')
@section('employee_activity', 'active')
@section('employee_activity_index', 'active')

@section('content')
<style>
    /* Custom Styles */
    .page-header {
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
        padding-bottom: 15px;
    }
    
    .page-header h1 {
        display: flex;
        align-items: center;
        font-size: 24px;
        margin: 0;
    }
    
    .page-header h1 small {
        font-size: 14px;
        margin-left: 10px;
    }
    
    .page-header-actions {
        margin-top: 10px;
    }
    
    .info-card {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 20px;
        background: #fff;
    }
    
    .info-card-header {
        background: #f8f9fa;
        padding: 10px 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .info-card-header h5 {
        margin: 0;
        font-size: 16px;
        color: #333;
    }
    
    .info-card-body {
        padding: 15px;
    }
    
    .info-item {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-label {
        font-weight: 600;
        color: #555;
        min-width: 120px;
    }
    
    .info-value {
        color: #333;
    }
    
    .description-content {
        padding: 15px;
        background: #f9f9f9;
        border-radius: 4px;
        border-left: 3px solid #3c8dbc;
    }
    
    .badge {
        padding: 5px 8px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .panel-heading.bg-primary {
        background-color: #3c8dbc !important;
    }
    
    .text-white {
        color: white;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa fa-tasks" aria-hidden="true"></i> Employee Activity Details
                <small class="text-muted">#{{ $activity->id }}</small>
            </h1>
            <div class="page-header-actions">
                <a href="{{ route('employee-activity.index') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="panel panel-bd lobidrag">
            <div class="panel-heading bg-primary">
                <div class="panel-title">
                    <h4 class="text-white">
                        <i class="fa fa-info-circle"></i> Activity Information
                    </h4>
                </div>
            </div>

            <div class="panel-body">
                <div class="row">
                    <!-- Basic Information Section -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-user"></i> Employee Details</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">{{ $activity->user->au_name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Employee ID:</span>
                                    <span class="info-value">{{ $activity->user->au_user_id ?? 'N/A' }}</span>
                                </div>
                                       <div class="info-item">
                                    <span class="info-label">Sell Amount:</span>
                                    <span class="info-value"> {{ $activity->sell_amount  ?? 'N/A'}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-calendar"></i> Activity Details</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Title:</span>
                                    <span class="info-value">{{ $activity->w_title }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date:</span>
                                    <span class="info-value">{{ \Carbon\Carbon::parse($activity->activity_date)->format('M d, Y') }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value">
                                        @if($activity->status == 0)
                                    <span class="label label-warning">Pending</span>
                                @elseif($activity->status == 1)
                                    <span class="label label-success">Accepted</span>
                                @elseif($activity->status == 2)
                                    <span class="label label-danger">Rejected</span>
                                @else
                                    <span class="label label-default">Unknown</span>
                                @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-align-left"></i> Working Description</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="description-content">
                                    {!! $activity->w_d !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-md-12 text-right">
                        @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            @if($activity->status == 0)
                                <a href="{{ route('employee-activity.accept', $activity->id) }}" 
                                   class="btn btn-success">
                                    <i class="fa fa-check"></i> Accept Activity
                                </a>
                            @else
                                <a href="{{ route('employee-activity.reject', $activity->id) }}" 
                                   class="btn btn-warning">
                                    <i class="fa fa-undo"></i> Revert to Pending
                                </a>
                            @endif
                        @endif
                        
                        <a href="{{ route('employee-activity.index') }}" class="btn btn-primary">
                            <i class="fa fa-list"></i> Back to Activity List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
