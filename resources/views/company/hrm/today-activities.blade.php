@extends('layouts.master')

@section('title', 'Today\'s Activities')
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
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline-item {
    position: relative;
    padding: 15px 20px;
    margin-bottom: 10px;
    background: #fff;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.timeline-item:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.activity-title {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}
.activity-meta {
    font-size: 0.85rem;
    color: #6c757d;
}
.activity-content {
    margin: 10px 0;
}
.activity-links {
    margin: 10px 0;
}
.activity-link {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 5px;
    padding: 4px 8px;
    background: #e9ecef;
    border-radius: 4px;
    text-decoration: none;
    color: #495057;
    font-size: 0.85rem;
}
.activity-link:hover {
    background: #007bff;
    color: white;
}
.activity-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 8px;
    margin-top: 8px;
    font-size: 0.8rem;
    color: #6c757d;
}
.date-header {
    background: #f8f9fa;
    padding: 10px 15px;
    margin: 20px 0 10px 0;
    border-radius: 6px;
    font-weight: bold;
    color: #495057;
    border-left: 4px solid #28a745;
}
.filter-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
</style>

<div class="row justify-content-center" style="margin-top: 30px;">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>
                        <i class="fas fa-tasks"></i> Today's Activities - {{ $today }}
                        <span class="badge badge-primary ml-2">{{ $todayActivities->count() }} Activities</span>
                    </h4>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Filter Section -->
                <div class="filter-card">
                    <form action="{{ route('company.hrm.activities-by-date') }}" method="GET" class="form-inline">
                        <div class="form-group mr-3 mb-2">
                            <label for="start_date" class="mr-2"><strong>From:</strong></label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $today }}">
                        </div>
                        <div class="form-group mr-3 mb-2">
                            <label for="end_date" class="mr-2"><strong>To:</strong></label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $today }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mb-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('company.hrm.today-activities') }}" class="btn btn-secondary btn-sm ml-2 mb-2">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </form>
                </div>

                <!-- Today's Date Header -->
                <div class="date-header">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Front:</strong> {{ date('m/d/Y') }}<br>
                            <strong>Tel:</strong> {{ date('m/d/Y') }}<br>
                            <strong>Post:</strong>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Last updated: {{ now()->format('h:i A') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Activities Timeline -->
                <div class="timeline">
                    @forelse($todayActivities as $activity)
                        <div class="timeline-item">
                            <div class="timeline-header">
                                <div class="activity-title">
                                    {{ $activity->w_title }}
                                </div>
                                <div class="activity-meta text-right">
                                    <span class="badge 
                                        @if($activity->status) 
                                            badge-success 
                                        @else 
                                            badge-danger 
                                        @endif">
                                        @if($activity->status)
                                            <i class="fas fa-check-circle"></i> Active
                                        @else
                                            <i class="fas fa-times-circle"></i> Inactive
                                        @endif
                                    </span>
                                </div>
                            </div>
                            
                            <div class="activity-content">
                                {!! $activity->w_d !!}
                            </div>

                            <!-- Activity Links (if any URLs in description) -->
                            @php
                                // Extract URLs from the description for link display
                                preg_match_all('/https?:\/\/[^\s<>"]+|www\.[^\s<>"]+/', $activity->w_d, $matches);
                                $urls = $matches[0] ?? [];
                            @endphp
                            
                            @if(count($urls) > 0)
                                <div class="activity-links">
                                    @foreach($urls as $index => $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="activity-link">
                                            Link {{ $index + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="activity-meta">
                                <i class="fas fa-user"></i> 
                                <strong>{{ $activity->user->au_name ?? 'Unknown User' }}</strong> | 
                                <i class="fas fa-building"></i> 
                                {{ $activity->branch->branch_name ?? 'N/A' }} | 
                                <i class="fas fa-clock"></i> 
                                {{ $activity->created_at->format('h:i A') }}
                            </div>

                            <div class="activity-footer">
                                Activity ID: {{ $activity->id }} | 
                                Created: {{ $activity->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="timeline-item text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Activities Found</h5>
                            <p class="text-muted">There are no activities for today.</p>
                            <a href="{{ route('company.hrm.branches-activities') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-building"></i> View Branches
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- Auto-refresh Notice -->
                <div class="text-center mt-4">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Page auto-refreshes every 5 minutes | 
                                Last updated: {{ now()->format('h:i A') }}
                            </small>
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

    // Make activity links open in new tab
    $('.activity-content a').attr('target', '_blank');

    // Format activity content
    $('.activity-content').each(function() {
        let content = $(this).html();
        // Convert line breaks to proper HTML
        content = content.replace(/\n/g, '<br>');
        $(this).html(content);
    });
});
</script>
@endsection