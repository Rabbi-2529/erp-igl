@extends('layouts.master')

@section('title', 'Branches List')
@section('company_class','active')
@section('branch_class','active')
@section('manage_branch_class','active')
@section('content')
<style>
.centered-form {

  margin: 40px auto;
  padding: 20px;
}
</style>
<div class="row justify-content-center">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag centered-form">
            <div class="panel-heading d-flex justify-content-between align-items-center">
                <h4>Branches List</h4>
                <a href="{{ route('branch.create') }}" class="btn btn-primary">Add New Branch</a>
            </div>
            <div class="panel-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>Branch ID</th>
                            <th>Branch Name</th>
                            <th>Branch Hotline</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td>{{ $branch->branch_id }}</td>
                                <td>{{ $branch->branch_name }}</td>
                                <td>{{ $branch->branch_hotline }}</td>
                                <td>{{ $branch->branch_city }}</td>
                               <td>
                                    @if($branch->status == 1)
                                        Active
                                    @elseif($branch->status == 0)
                                        Inactive
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('branch.edit', $branch->branch_id) }}" class="btn btn-sm btn-warning">Edit</a>

                                    <form action="{{ route('branch.destroy', $branch->branch_id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No branches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
