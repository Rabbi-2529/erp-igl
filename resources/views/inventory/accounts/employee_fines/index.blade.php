@extends('layouts.master')

@section('title', 'Employee Fines List')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Employee Fines List</h4>
                </div>
            </div>

            <div class="panel-body">
                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <a href="{{ route('inventory.accounts.employee_fines.create') }}" class="btn btn-primary">Create Employee Fine</a><br><br>

                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeFines as $employeeFine)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $fineHolders[$employeeFine->id]->au_name ?? 'Unknown' }}
                                ({{ $employeeFine->au_user_id }})
                            </td>

                            <td>{{ $fines->firstWhere('id', $employeeFine->title)->title ?? 'Unknown' }}</td>
                            <td>{{ $employeeFine->amount }}</td>
                            <td>{{ $employeeFine->date }}</td>
                            <td>
                                <!-- Edit Button -->
                                <a href="#" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#editFineModal{{ $employeeFine->id }}">Edit</a>

                                <!-- Delete Button -->
                                <button class="btn btn-danger btn-xs" onclick="deleteEmployeeFine({{ $employeeFine->id }})">
                                    Delete
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Fine Modal -->
                        <div class="modal fade" id="editFineModal{{ $employeeFine->id }}" tabindex="-1" role="dialog" aria-labelledby="editFineModalLabel{{ $employeeFine->id }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="editFineModalLabel{{ $employeeFine->id }}">Edit Fine: {{ $employeeFine->au_user_id }}</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Form for editing fine details -->
                                        <form action="{{ route('employee_fines.update', ['id' => $employeeFine->id]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label for="au_user_id">User</label>
                                                <select name="au_user_id" id="au_user_id" class="form-control">
                                                    @php
                                                        // Sort the $users collection by au_user_id in ascending order
                                                        $sorted_users = $users->sortBy('au_user_id');
                                                    @endphp
                                                    
                                                    @foreach($sorted_users as $user)
                                                    <option value="{{ $user->au_user_id }}" {{ $user->au_user_id == $employeeFine->au_user_id && (empty($fineHolders[$employeeFine->id]) || $fineHolders[$employeeFine->id]->au_id == $user->au_id) ? 'selected' : '' }}>
                                                        {{ $user->au_user_id }} ({{ $user->au_name }}){{ $leftIds->has($user->au_id) ? ' (left)' : '' }}
                                                    </option>

                                                    @endforeach
                                                </select>

                                            </div>
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <select name="title" id="title" class="form-control">
                                                    @foreach($fines as $fine)
                                                        <option value="{{ $fine->id }}" {{ $fine->id == $employeeFine->title ? 'selected' : '' }}>{{ $fine->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="amount">Amount</label>
                                                <input type="text" class="form-control" id="amount" name="amount" value="{{ $employeeFine->amount }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="date">Date</label>
                                                <input type="date" class="form-control" id="" name="date" value="{{ $employeeFine->date }}" required>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6">No employee fines found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom class for larger Swal.fire popup */
.swal-wide {
    width: 600px !important;
    font-size: 18px !important;
}
</style>

<script>
// Retrieve CSRF token from meta tag
const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

// JavaScript function to handle deletion using Swal.fire and fetch API
function deleteEmployeeFine(id) {
    // Confirm deletion with Swal.fire
    Swal.fire({
        title: 'Are you sure?',
        text: 'Once deleted, you will not be able to recover this fine!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Delete',
        customClass: {
            popup: 'swal-wide'  // Custom class for larger size
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Prepare the request URL
            const url = `{{ url("/employee_fines") }}/${id}`;

            // Construct the fetch request
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // Assuming response is JSON; adjust as needed
            })
            .then(data => {
                // Handle successful deletion
                console.log('Delete success:', data); // Log response data for debugging
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The fine has been deleted.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal-wide'  // Custom class for larger size
                    }
                }).then(() => {
                    location.reload(); // Reload page or update as needed
                });
            })
            .catch(error => {
                console.error('Error deleting fine:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the fine.',
                    icon: 'error',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal-wide'  // Custom class for larger size
                    }
                });
            });
        }
    });
}
</script>

@endsection

@section('custom_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script>
            $("#date").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            format: 'dd-MM-yyyy'
        });


</script>
@endsection
