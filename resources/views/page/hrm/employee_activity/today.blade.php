@extends('layouts.master')

@section('title', 'Today\'s Employee Activities')

@section('human_resource_class', 'active')
@section('hrm_class', 'active')
@section('employee_activity', 'active')
@section('employee_activity_today', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <h1>Today's Employee Activity Management</h1>
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Today's Employee Activity List</h4>
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
                                <td>{{ $activity->user->au_user_id ?? 'N/A' }}</td>
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

                            <td>
                              @if(Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id || (Auth::user()->au_user_type == 4 ))
                                <a href="{{ route('employee-activity.show', $activity->id) }}" class="btn btn-info btn-sm">Show</a>
                            
                                @if($activity->status == 0)
                                    <a href="{{ route('employee-activity.accept', $activity->id) }}" class="btn btn-success btn-sm">Accept</a>
                                @endif
                            
                                @if($activity->status == 0)
                                    <a href="{{ route('employee-activity.reject', $activity->id) }}" class="btn btn-danger btn-sm">Reject</a>
                                @endif
                            @endif

                                <a href="{{ route('employee-activity.edit', $activity->id) }}" class="btn btn-primary btn-sm">Edit</a>

                                @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id)
                                    <form id="delete-form-{{ $activity->id }}" action="{{ route('employee-activity.destroy', $activity->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $activity->id }})">Delete</button>
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

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#employee-activity-table').DataTable();

        // Date filter functionality
        $('#activity-date-filter').on('change', function() {
            var filterDate = $(this).val();
            table.column(6).search(filterDate).draw();
        });

        // Initialize datepicker for the date filter input
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',  // Set date format to match the backend format
            autoclose: true        // Automatically close the calendar after selecting a date
        });
    });

    // SweetAlert confirmation for delete
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
                // If confirmed, submit the form to delete the activity
                document.getElementById('delete-form-' + activityId).submit();
            }
        });
    }
</script>
@endsection
