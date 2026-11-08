@extends('layouts.master')

@section('content')
@section('title', 'Blood Donated Users')
@section('human_resource_class', 'active')
@section('payroll', 'active')
@section('employee_blood_donation', 'active')

<section class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Blood Donated Users</h4>
                    </div>
                </div>

                <div class="panel-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <!-- Add a 'Create Blood Donation' button here -->
                    <a href="{{ route('payroll.blood_donates.create') }}" class="btn btn-success mb-3"> Blood Donation</a>
<br>
<br>
                    <table class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Blood Group</th>
                             
                                <th>Last Donation Date</th>
                                <th>Donated By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($donations as $donation)
                            <tr>
                                <td>{{ $donation->adminUser->au_name }}</td>
                                <td>{{ $donation->adminUser->au_blood_group }}</td>
                            
                                <td>
    @if($donation->donate_date)
        {{ \Carbon\Carbon::parse($donation->donate_date)->format('Y-m-d') }}
    @else
        No Donation Yet
    @endif
</td>
                                <td>
                                    @if($donation->for_whom)
                                    {{ $donation->for_whom }}
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td>
    <!-- Edit Button with Icon -->
    <a href="{{ route('payroll.blood_donates.edit', $donation->id) }}" class="btn btn-warning btn-sm">
        <i class="fa fa-edit"></i> Edit
    </a>

    <!-- Delete Button with Icon (Only for Admins) -->
    <!-- Check if the user is an admin -->
    <form action="{{ route('payroll.blood_donates.delete', $donation->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this donation record?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fa fa-trash"></i> Delete
        </button>
    </form>
</td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('custom_script')
<!-- Add any custom JS scripts here -->
@endsection
