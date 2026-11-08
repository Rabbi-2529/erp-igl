@extends('layouts.master')

@section('title', 'Branch Activities')
@section('company_class', 'active')
@section('branch_class', 'active')
@section('activities_class', 'active')

@section('content')
<style>
.centered-form {
  max-width: 100%;
  margin: 40px auto;
  padding: 20px;
}
.branch-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}
.activity-item {
    border-left: 4px solid #28a745;
    padding-left: 15px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
.activity-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}
</style>

<div class="row justify-content-center" style="margin-top: 30px;">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>
                        <i class="fas fa-building"></i> Branch Activities
                    </h4>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Branch Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card branch-header shadow">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3 class="mb-1">{{ $branch->branch_name }}</h3>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar-day"></i> Activities for {{ $today }}
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <h2 class="mb-0">{{ $activities->count() }}</h2>
                                        <p class="mb-0">Total Activities</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="{{ route('company.hrm.branches-activities') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to All Branches
                        </a>
                    </div>
                </div>

                <!-- Activities List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-list-ol"></i> Activities List
                                </h5>
                            </div>
                            <div class="card-body">
                                @forelse($activities as $activity)
                                    <div class="activity-item">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5 class="text-primary">{{ $activity->w_title }}</h5>
                                                <p class="mb-2">{{ $activity->w_d }}</p>
                                                <div class="activity-details">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> 
                                                        <strong>{{ $activity->user->au_name ?? 'Unknown User' }}</strong> | 
                                                        <i class="fas fa-id-badge"></i> {{ $activity->user->au_user_id ?? 'N/A' }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> 
                                                        {{ $activity->created_at->format('h:i A') }} | 
                                                        <i class="fas fa-history"></i> 
                                                        {{ $activity->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <span class="badge 
                                                    @if($activity->status) 
                                                        badge-success 
                                                    @else 
                                                        badge-danger 
                                                    @endif badge-lg">
                                                    @if($activity->status)
                                                        <i class="fas fa-check"></i> Active
                                                    @else
                                                        <i class="fas fa-times"></i> Inactive
                                                    @endif
                                                </span>
                                                <br>
                                                <small class="text-muted mt-2 d-block">
                                                    Activity ID: {{ $activity->id }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @empty
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No Activities Found</h4>
                                        <p class="text-muted">There are no activities for this branch today.</p>
                                        <a href="{{ route('company.hrm.branches-activities') }}" class="btn btn-primary">
                                            <i class="fas fa-building"></i> View All Branches
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branch Information -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Branch Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Branch ID:</strong></td>
                                        <td>{{ $branch->branch_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Branch Name:</strong></td>
                                        <td>{{ $branch->branch_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Company ID:</strong></td>
                                        <td>{{ $branch->company_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Today's Date:</strong></td>
                                        <td>{{ $today }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Activity Statistics</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Total Activities:</strong></td>
                                        <td><span class="badge badge-primary">{{ $activities->count() }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Active Activities:</strong></td>
                                        <td>
                                            <span class="badge badge-success">
                                                {{ $activities->where('status', 1)->count() }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Inactive Activities:</strong></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $activities->where('status', 0)->count() }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>{{ now()->format('h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection