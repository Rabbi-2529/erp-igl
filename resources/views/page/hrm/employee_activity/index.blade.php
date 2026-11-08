@extends('layouts.master')

@section('title', 'Employee Activities')

@section('human_resource_class', 'active')
@section('employee_activity', 'active')
@section('employee_activity_my', 'active')

@section('content')
<style>
    .row.g-3 {
        margin-bottom: 20px;
    }

    .row.g-3 .col-md-3 {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-control {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 14px;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .form-control:focus {
        border-color: #5c6ac4;
        box-shadow: 0 0 5px rgba(92, 106, 196, 0.5);
        outline: none;
    }

    .datepicker {
        background: url('https://cdn-icons-png.flaticon.com/512/747/747310.png') no-repeat right 10px center;
        background-size: 20px 20px;
        background-color: #fff;
    }

    @media (max-width: 767px) {
        .row.g-3 .col-md-3 {
            margin-bottom: 10px;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Employee Activity</h1>
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Employee Activity List</h4>
                </div>
            </div>

            <div class="panel-body">
                @php
                    $user = Auth::user();
                    $existingActivity = App\Models\EmployeeActivity::where('user_id', $user->au_id)
                                            ->whereDate('activity_date', today())
                                            ->exists();
                @endphp



                <!-- Filters -->

                <div class="row g-3 mb-3">
                    <div class="col-md-3 col-sm-6 col-12">
                        <input type="text" id="activity-date-filter" class="form-control datepicker" placeholder="Filter by Date" />
                    </div>
 
                    <div class="col-md-3 col-sm-6 col-12">
                        <input type="text" id="employee-id-filter" class="form-control" placeholder="Filter by Employee ID" />
                    </div>
                           
                 @if(!$existingActivity)

            <a href="{{ route('employee-activity.create') }}" class="btn btn-info">Add New Activity</a>

       @endif
                </div>
                

       
                <!-- Responsive Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="employee-activity-table">
                        <thead>
                            <tr>
                                <th>#</th>
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
                                <td>{{ \Illuminate\Support\Str::limit($activity->w_title, 30) }}</td>
                                <td><span>{!! Str::limit(strip_tags($activity->w_d), 30) !!}</span></td>
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
                                    <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Actions">
                                        @if(Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id || (Auth::user()->au_user_type == 4))
                                            <a href="{{ route('employee-activity.show', $activity->id) }}" class="btn btn-info mb-1">Show</a>

                                            @if($activity->status == 0)
                                                <a href="{{ route('employee-activity.accept', $activity->id) }}" class="btn btn-success mb-1">Accept</a>
                                                <a href="{{ route('employee-activity.reject', $activity->id) }}" class="btn btn-danger mb-1">Reject</a>
                                            @endif
                                        @endif

                                        @if($activity->status != 2)
                                            <a href="{{ route('employee-activity.edit', $activity->id) }}" class="btn btn-primary mb-1">Edit</a>
                                        @endif

                                        @if(Auth::user()->au_user_type == 4 || (Auth::user()->au_user_type == 5 && Auth::user()->au_id != $activity->user_id))
                                            <form id="delete-form-{{ $activity->id }}" action="{{ route('employee-activity.destroy', $activity->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger mb-1" onclick="confirmDelete({{ $activity->id }})">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- /.table-responsive -->
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        var table = $('#employee-activity-table').DataTable();

        // Filter by Date
        $('#activity-date-filter').on('change', function() {
            var filterDate = $(this).val();
            table.column(4).search(filterDate).draw(); // Column 4 is Activity Date
        });

        // Filter by Employee ID (by title in this case, due to missing actual ID column)
        $('#employee-id-filter').on('keyup change', function() {
            var empId = $(this).val();
            table.column(1).search(empId).draw(); // Column 1 might represent employee
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
