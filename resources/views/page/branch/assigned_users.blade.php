@extends('layouts.master')
@section('company_class','active')
@section('branch_class','active')
@section('assigned_users_class','active')
@section('content')
<div class="row justify-content-center">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag centered-form">
            <div class="panel-heading d-flex justify-content-between align-items-center">
                <h4>Assigned Branches List</h4>
                <a href="{{ route('branch.create') }}" class="btn btn-primary">Add New Branch</a>
            </div>
            <div class="panel-body">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Branch Name</th>
                            <th>Branch Status (Click to toggle)</th>
                            <th>User Status (Click to toggle)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignedUsers as $user)
                            <tr>
                                <td>{{ $user->au_user_id }}</td>
                                <td>{{ $user->au_name }}</td>
                                <td>{{ $user->au_email }}</td>
                                <td>{{ $user->branch ? $user->branch->branch_name : 'N/A' }}</td>
                                <td>
                                    @if($user->branch)
                                        <a href="{{ route('branch.toggleBranchStatus', $user->branch->branch_id) }}"
                                           class="badge {{ $user->branch->status == 1 ? 'bg-success' : 'bg-danger' }} text-white"
                                           onclick="return confirm('Are you sure you want to toggle this branch status? This will affect all assigned users.')">
                                            {{ $user->branch->status == 1 ? 'Active' : 'Inactive' }}
                                        </a>
                                    @else
                                        <span class="text-muted">No Branch</span>
                                    @endif
                                </td>
                              <td>
                                    @php
                                        // User status toggle only if branch active
                                        $canToggleUser = $user->branch && $user->branch->status == 1;
                                    @endphp

                                    @if($user->au_status == 1)
                                        @if($canToggleUser)
                                            <a href="{{ route('branch.toggleUserStatus', $user->au_id) }}" 
                                               class="badge bg-success text-white"
                                               onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                Active
                                            </a>
                                        @else
                                            <span class="badge bg-success text-white" title="Cannot toggle user: branch inactive">
                                                Active
                                            </span>
                                        @endif
                                    @else
                                        @if($canToggleUser)
                                            <a href="{{ route('branch.toggleUserStatus', $user->au_id) }}" 
                                               class="badge bg-danger text-white"
                                               onclick="return confirm('Are you sure you want to activate this user?')">
                                                Inactive
                                            </a>
                                        @else
                                            <span class="badge bg-danger text-white" title="Cannot activate user: branch inactive">
                                                Inactive
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- panel-body -->
        </div> <!-- panel -->
    </div> <!-- col -->
</div> <!-- row -->
@endsection
