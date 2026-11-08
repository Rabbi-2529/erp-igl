@extends('layouts.master')

@section('title', 'Fines List')



@section('content')


<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Fines List</h4>
                </div>
            </div>

            <div class="panel-body">
                @if(session()->has('del'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('del') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session()->has('update'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('update') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                
                <a href="{{ route('inventory.accounts.fines.create') }}" class="btn btn-primary">Create Fine</a><br><br>

                <table class="table table-bordered table-striped table-hover datatable">
                
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Title</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fines as $fine)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $fine->title }}</td>
                            <td>
                                <a href="#" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#editFineModal{{ $fine->id }}">Edit</a>
                                <button class="btn btn-danger btn-xs" onclick="deleteFine({{ $fine->id }})">Delete</button>
                            </td>
                        </tr>

                        <!-- Edit Fine Modal -->
                        <div class="modal fade" id="editFineModal{{ $fine->id }}" tabindex="-1" role="dialog" aria-labelledby="editFineModalLabel{{ $fine->id }}">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="editFineModalLabel{{ $fine->id }}">Edit Fine: {{ $fine->title }}</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Form for editing fine details -->
                                        <form action="{{ route('fines.update', ['fine' => $fine->id]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label for="title">Title</label>
                                                <input type="text" class="form-control" id="title" name="title" value="{{ $fine->title }}" required>
                                            </div>
                                            <!-- Add more fields as needed -->

                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Edit Fine Modal -->

                        @endforeach
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
    function deleteFine(id) {
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
                const url = `{{ url("/fines") }}/${id}`;

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
