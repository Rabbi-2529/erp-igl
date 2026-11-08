@extends('layouts.master')

@section('title', 'PC Settings')

@section('pc_settings_class', 'active')

@section('content')
<div class="row justify-content-center">
    <div class="col-sm-8">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>PC Settings</h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- Display success or error messages -->
                @if (session('success'))
                    <div class="alert alert-success text-center">{{ session('success') }}</div>
                @elseif (session('error'))
                    <div class="alert alert-danger text-center">{{ session('error') }}</div>
                @endif

                @if (auth()->user()->au_user_type == 4)
                    <!-- Display all user PC settings for user_type 4 -->
                    <h5>All User PC Settings</h5>
                    <table id="password" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Employee Id</th>
                                <th>Password</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pcSettings as $setting)
                                <tr>
                                    <td>{{ $setting->user->au_name }}</td>
                                    <td>{{ $setting->user->au_user_id }}</td>
                                    <td>{{ $setting->password }}</td> <!-- Show password in plain text for user_type 4 -->
                                    <td>
                                    <div class="d-flex justify-content-between">                                     
<button class="btn btn-primary btn-sm d-inline" onclick="editPassword({{ $setting->id }})">
    Edit 
</button>

<!-- Delete Button -->

         </div>                           </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                @if ($pcSettings)
    <div class="mb-3">
        <h4>Your Password</h4>

        <!-- Password form always shown initially -->
        @if (!session('panelauthenticated'))
   <form action="{{ route('pc_setting.verify') }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="panelPassword">Enter Panel Password to View:</label>
        <div class="input-group">
            <input type="password" name="panelPassword" id="panelPassword" class="form-control form-control-sm w-100" required>
            <div class="input-group-append">
                <!-- Eye icon to toggle password visibility -->
            
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-info">Verify</button>
</form>



        @else
            <!-- After correct password, show the actual password -->
            <p><strong>Password:</strong> <em>{{ $pcSettings->password }}</em></p>
            
            <!-- Edit and Delete Buttons -->
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary btn-sm" onclick="editPassword({{ $pcSettings->id }})">
                    Edit Password
                </button>

                <!-- Delete form -->
                <form action="{{ route('pc_setting.delete', $pcSettings->id) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" data-id="{{ $pcSettings->id }}">
                        Delete Password
                    </button>
                </form>
            </div>
        @endif

        <!-- Show error message if password is incorrect -->
        @if (session('error'))
            <p>Your panel password was incorrect. Please try again.</p>
        @endif
    </div>
@else
  <!-- Add Password Form -->
    <form action="{{ route('pc_setting.create') }}" method="post" class="form-horizontal" id="user_registration_form" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="text" class="form-label">Set Password:</label>
        <div class="input-group">
            <!-- Input field with smaller size and full width -->
            <input type="password" name="password" id="password" class="form-control form-control-sm w-100" required>
          
        </div>
        <br>
        
    <button type="submit" class="btn btn-success w-md m-b-5">Save</button>
    </div>

</form>
@endif



                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editPasswordModal" tabindex="-1" role="dialog" aria-labelledby="editPasswordModalLabel" aria-hidden="true" data-inert>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPasswordModalLabel">Edit Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPasswordForm">
                    <div class="form-group">
                        <label for="newPassword">Enter New Password</label>
                        <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" required>
                    </div>
                    <!-- Hidden input field to hold the password ID -->
                    <input type="hidden" id="passwordId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updatePasswordBtn">Update</button>
            </div>
        </div>
    </div>
</div>




<!-- Custom CSS for Styling -->
<style>
    /* Center the content */
    .row.justify-content-center {
        display: flex;
        justify-content: center;
    }

    .col-sm-6 {
        width: 100%;
        max-width: 600px; /* Set a maximum width for the form */
    }

    .form-control-sm {
        padding: 8px 12px;
        font-size: 14px;
    }

    /* Make buttons smaller and aligned */
    .btn-sm {
    font-size: 10px; /* Smaller font */
    padding: 3px 8px; /* Smaller padding */
}

    .btn-danger, .btn-primary {
        font-size: 14px;
        padding: 6px 12px;
    }

    /* Form field styling */
    .form-control {
        font-size: 14px;
        padding: 8px 12px;
    }

    .panel-body {
        padding: 20px;
    }
    .d-inline {
    margin-right: 10px; /* Adds space between buttons */
}

    /* Adjust buttons inside modal and delete form */
    .btn-primary, .btn-success {
        font-size: 14px;
        padding: 6px 12px;
    }

    .d-flex {
    display: flex;
    align-items: center; /* Vertically align buttons */
}

.d-flex .btn {
    margin-right: 5px; /* Small space between buttons */
}

    .alert {
        margin-bottom: 20px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle password deletion with SweetAlert confirmation
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent form submission
            
            const passwordId = this.querySelector('button').getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to recover this password!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Dynamically use the ID to submit the form
                    this.action = "{{ route('pc_setting.delete', '') }}/" + passwordId;
                    this.submit();
                }
            });
        });
    });


</script>
<script>
    $(document).ready(function() {
        $('#password').DataTable({
            
            
        });
    });
</script>
<script>
    const editPasswordUrl = "{{ route('pc_setting.edit', ':id') }}";
</script>
<script>
 // Function to open the edit password modal and set the password ID
function editPassword(id) {
    // Set the hidden input field with the password id
    document.getElementById('passwordId').value = id;

    // Remove the "inert" attribute to make the modal visible and accessible
    document.getElementById('editPasswordModal').removeAttribute('aria-hidden');
    document.getElementById('editPasswordModal').removeAttribute('data-inert');

    // Show the modal
    $('#editPasswordModal').modal('show');
}

// Handle password update via AJAX (POST)
document.getElementById('updatePasswordBtn').addEventListener('click', function() {
    const newPassword = document.getElementById('newPassword').value;  // Get the new password from input
    const passwordId = document.getElementById('passwordId').value;  // Get the password ID
    const url = editPasswordUrl.replace(':id', passwordId);  // Replace the placeholder with the actual ID

    // Validate password
    if (!newPassword) {
        Swal.fire('Error', 'Password cannot be empty', 'error');
        return;
    }

    // Send the password update via fetch (POST request)
    fetch(url, {
        method: 'POST',  // Use POST to update
        headers: {
            'Content-Type': 'application/json',  // Send JSON data
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content  // CSRF token for security
        },
        body: JSON.stringify({ password: newPassword })  // Send the new password as JSON
    })
    .then(response => response.json())  // Handle the JSON response
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Password updated successfully!', 'success').then(() => {
                location.reload();  // Reload the page to reflect the updated password
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to update password', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred: ' + error.message, 'error');  // Handle errors
    });

    // Close the modal after the request
    $('#editPasswordModal').modal('hide');

    // Reapply "inert" attribute to prevent further focus when modal is closed
    document.getElementById('editPasswordModal').setAttribute('aria-hidden', 'true');
    document.getElementById('editPasswordModal').setAttribute('data-inert', 'true');
});


</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script>
    // JavaScript to toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('panelPassword');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = "password";
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
</script>

@endsection
