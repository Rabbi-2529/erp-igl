@extends('layouts.master')

@section('title', 'Activities by Date')
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
.filter-card {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}
</style>

<div class="row justify-content-center" style="margin-top: 30px;">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4><i class="fas fa-calendar-alt"></i> Activities by Date Range</h4>
                </div>
            </div>
            <div class="panel-body">
                
                <!-- Filter Form -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card filter-card">
                            <div class="card-body">
                                <form action="{{ route('company.hrm.activities-by-date') }}" method="GET" class="form-inline">
                                    <div class="form-group mr-3 mb-2">
                                        <label for="start_date" class="mr-2"><strong>From Date:</strong></label>
                                        <input type="date" name="start_date" class="form-control datepicker" value="{{ $startDate }}" required>
                                    </div>
                                    <div class="form-group mr-3 mb-2">
                                        <label for="end_date" class="mr-2"><strong>To Date:</strong></label>
                                        <input type="date" name="end_date" class="form-control datepicker" value="{{ $endDate }}" required>
                                    </div>
                                    <div class="form-group mr-3 mb-2">
                                        
                                        <select name="branch_id" class="form-control">
                                            <option value="">All Branches</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->branch_id }}" 
                                                    {{ $selectedBranch == $branch->branch_id ? 'selected' : '' }}>
                                                    {{ $branch->branch_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-2">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('company.hrm.activities-by-date') }}" class="btn btn-secondary mb-2 ml-2">
                                        <i class="fas fa-sync"></i> Reset
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activities Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="employee-activity-table">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>User</th>
                                                <th>Branch</th>
                                                <th>Work Title</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activities as $index => $activity)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $activity->created_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <strong>{{ $activity->user->au_name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">{{ $activity->user->au_user_id ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>{{ $activity->branch->branch_name ?? 'N/A' }}</td>
                                                    <td><strong>{{ $activity->w_title }}</strong></td>
                                                    <td>{{ Str::limit(strip_tags($activity->w_d), 80) }}</td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($activity->status) 
                                                                badge-success 
                                                            @else 
                                                                badge-danger 
                                                            @endif">
                                                            @if($activity->status)
                                                                <i class="fas fa-check"></i> Active
                                                            @else
                                                                <i class="fas fa-times"></i> Inactive
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>{{ $activity->created_at->format('h:i A') }}</td>
                                                    <td>
                                                        <button type="button" 
                                                            class="btn btn-sm btn-info" 
                                                            data-toggle="modal" 
                                                            data-target="#viewDescriptionModal" 
                                                            data-title="{{ $activity->w_title }}" 
                                                            data-user="{{ $activity->user->au_name ?? 'N/A' }}" 
                                                            data-branch="{{ $activity->branch->branch_name ?? 'N/A' }}" 
                                                            data-date="{{ $activity->created_at->format('Y-m-d') }}" 
                                                            data-description="{{ strip_tags($activity->w_d) }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                                        <h5 class="text-muted">No Activities Found</h5>
                                                        <p class="text-muted">Try adjusting your search criteria</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Description Modal -->
                <div class="modal fade" id="viewDescriptionModal" tabindex="-1" role="dialog" aria-labelledby="viewDescriptionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="viewDescriptionModalLabel">Activity Description</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <h5 id="activityTitle"></h5>
                                <p><strong>User:</strong> <span id="activityUser"></span></p>
                                <p><strong>Branch:</strong> <span id="activityBranch"></span></p>
                                <p><strong>Date:</strong> <span id="activityDate"></span></p>
                                <hr>
                                <p id="activityDescription" style="white-space: pre-line;"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

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
