@extends('layouts.master')

@section('title', 'Employee Activities')

@section('human_resource_class', 'active')
@section('hrm_class', 'active')
@section('employee_activity', 'active')
@section('employee_activity_total', 'active')

@section('content')

<style>
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

.modal-header.bg-primary {
    background-color: #3c8dbc;
}

.text-white {
    color: white;
}

</style>
<div class="row">
    <div class="col-sm-12">
        <h1> Employee Activity Management</h1>
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Employee Activity List</h4>
                </div>
            </div>
     @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
            <div class="panel-body">
                @php
                    $user = Auth::user();  // Get the current logged-in user
                    $existingActivity = App\Models\EmployeeActivity::where('user_id', $user->au_id)
                            ->whereDate('activity_date', today())  // Check if activity already exists for today
                            ->exists();  // Returns true if activity exists, false otherwise
                @endphp

                @if(!$existingActivity)
                    <a href="{{ route('employee-activity.create') }}" class="btn btn-info pull-right">Add New Activity</a>
                @endif

                <!-- Filter by Date (optional) -->
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" id="activity-date-filter" class="form-control datepicker" placeholder="Filter by Date" />
                    </div>
                     <div class="col-md-3">
                        <input type="text" id="employee-id-filter" class="form-control" placeholder="Filter by Employee ID" />
                    </div>
                </div>
                <br />

                <!-- DataTable -->
                <table class="table table-bordered table-striped table-hover" id="employee-activity-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                <th>Employee Name</th>
                                <th>Employee ID</th>
                            @endif
                            <th>Activity Title</th>
                            <th>Working Description</th>
                            <th>Status</th>
                            <th>Activity Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                <td>{{ $activity->user->au_name ?? 'N/A' }}</td>
                                <td>
    <a href="#" class="employee-details" 
       data-id="{{ $activity->user->au_user_id ?? 'N/A' }}"
       data-name="{{ $activity->user->au_name ?? 'N/A' }}"
       data-dept="{{ $activity->sell_amount ?? 'N/A' }}"
    
       data-status="{{ $activity->status ?? 'N/A' }}"
       data-status-text="{{ $activity->status == 0 ? 'Pending' : ($activity->status == 1 ? 'Accepted' : ($activity->status == 2 ? 'Rejected' : 'Unknown')) }}"

       data-date="{{ $activity->created_at ? $activity->created_at->format('d M Y') : 'N/A' }}"
       data-desc="{{ $activity->w_d ?? 'No description' }}"
       data-title="{{ $activity->w_title ?? 'N/A' }}">
        {{ $activity->user->au_user_id ?? 'N/A' }}
    </a>
</td>

                      @endif

                            <td>{{ \Illuminate\Support\Str::limit($activity->w_title, 30) }}</td>
                    <td>
    <span>{!! Str::limit(strip_tags($activity->w_d), 30) !!}</span>
  
</td>

                            <td>
                                @if($activity->status == 0)
                                    <span class="label label-warning">Pending</span>
                                @elseif($activity->status == 1)
                                    <span class="label label-success">Accepted</span>
                                @elseif($activity->status == 2)
                                    <span class="label label-danger">Rejected</span>
                                @else
                                    <span class="label label-default">Unknown</span>
                                @endif
                            </td>

                            <td>{{ $activity->activity_date }}</td>

                          <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $activity->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-gear"></i> Action
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $activity->id }}">
                                @if(Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id || (Auth::user()->au_user_type == 4))
                                 <li>
    <a href="{{ route('employee-activity.show', $activity->id) }}" target="_blank" data-toggle="tooltip" data-placement="left" title="Show">
        <i class="fa fa-eye" aria-hidden="true"></i> Show
    </a>
</li>
                                    @if($activity->status == 0)
                                        <li>
                                            <a href="{{ route('employee-activity.accept', $activity->id) }}" data-toggle="tooltip" data-placement="left" title="Accept">
                                                <i class="fa fa-check" aria-hidden="true"></i> Accept
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('employee-activity.reject', $activity->id) }}" data-toggle="tooltip" data-placement="left" title="Reject">
                                                <i class="fa fa-times" aria-hidden="true"></i> Reject
                                            </a>
                                        </li>
                                    @endif
                                @endif
                    @if($activity->status != 2)
    <li>
        <a href="{{ route('employee-activity.edit', $activity->id) }}" data-toggle="tooltip" data-placement="left" title="Edit">
            <i class="fa fa-pencil" aria-hidden="true"></i> Edit
        </a>
    </li>
@endif
                                @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id)
                                    <li>
                                        <a href="#" onclick="event.preventDefault(); confirmDelete({{ $activity->id }})" data-toggle="tooltip" data-placement="left" title="Delete">
                                            <i class="fa fa-trash" aria-hidden="true"></i> Delete
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        
                        <!-- Delete form (hidden) -->
                        @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id)
                            <form id="delete-form-{{ $activity->id }}" action="{{ route('employee-activity.destroy', $activity->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" role="dialog" aria-labelledby="employeeDetailsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">
                    <i class="fa fa-user"></i> Employee Activity Details
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Employee Info -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-user"></i> Employee Info</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value" id="modal-employee-name">N/A</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Employee ID:</span>
                                    <span class="info-value" id="modal-employee-id">N/A</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Sell Amount:</span>
                                    <span class="info-value" id="modal-sell-amount">N/A</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value" id="modal-employee-email">N/A</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Info -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-info-circle"></i> Activity Info</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="info-item">
                                    <span class="info-label">Date:</span>
                                    <span class="info-value" id="modal-activity-date">N/A</span>
                                </div>
                                <div class="info-item">
    <span class="info-label">Status:</span>
    <span class="info-value">
        <span id="modal-activity-status" class="badge badge-secondary">N/A</span>
    </span>
</div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Working Description -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="info-card">
                            <div class="info-card-header">
                                <h5><i class="fa fa-align-left"></i> Working Description</h5>
                            </div>
                            <div class="info-card-body">
                                <div class="description-content" id="modal-activity-description">
                                    No description available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <i class="fa fa-times"></i> Close
    </button>

    @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
        @if($activity->status == 0)
            <a href="{{ route('employee-activity.accept', $activity->id) }}" 
               class="btn btn-success">
                <i class="fa fa-check"></i> Accept Activity
            </a>
        @else
            <a href="{{ route('employee-activity.reject', $activity->id) }}" 
               class="btn btn-warning">
                <i class="fa fa-undo"></i> Revert to Inactive
            </a>
        @endif
    @endif
</div>


        </div>
    </div>
</div>




<script>
    $(document).ready(function() {
        $('.employee-details').on('click', function(e) {
            e.preventDefault();

            // Fetch data attributes from the clicked element
            const name = $(this).data('name');
            const id = $(this).data('id');
            const dept = $(this).data('dept');
            const statusText = $(this).data('status-text');
            const date = $(this).data('date');
            const description = $(this).data('desc');
            const title = $(this).data('title');

            // Set values to modal fields
            $('#modal-employee-name').text(name);
            $('#modal-employee-id').text(id);
            $('#modal-sell-amount').text(dept);
            $('#modal-activity-title').text(title);
            $('#modal-activity-date').text(date);
            $('#modal-activity-status').text(statusText);
            $('#modal-activity-description').html(description);

            // Show the modal
            $('#employeeDetailsModal').modal('show');
        });
    });
</script>


<script>
    $(document).ready(function() {
        var table = $('#employee-activity-table').DataTable();

        // Filter by Date (column 5 = Activity Date)
        $('#activity-date-filter').on('change', function() {
            var filterDate = $(this).val();
            table.column(6).search(filterDate).draw();
        });

        // Filter by Employee ID (column 1 = Employee ID)
        $('#employee-id-filter').on('keyup change', function() {
            var empId = $(this).val();
            table.column(2).search(empId).draw();
        });

        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function confirmDelete(activityId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to recover this activity!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + activityId).submit();
            }
        });
    }
</script>
@endsection
