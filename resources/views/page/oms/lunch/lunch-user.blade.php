@extends('layouts.master')
@section('title', 'Lunch Users')

@section('oms_class', 'active')
@section('office_lunch_class', 'active')
@section('lunch_user_class', 'active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Users with Lunch Permission</h4>
                </div>
            </div>

            <div class="panel-body">
                @if (session('msg'))
                    <div class="alert alert-{{ session('type') }}">
                        {{ session('msg') }}
                    </div>
                @endif

                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>SL</th> 
                           
                            <th>Name</th>
                            <th>ID</th> 
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comUsers as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                              
                                <td>{{ $user->au_name }}</td>
                                <td>{{ $user->au_user_id }}</td>
                                <td>{{ $user->au_email }}</td>
                                <td>{{ $user->au_mobile }}</td>
                                <td>
                                    <a href="{{ route('permission', ['id' => $user->au_id]) }}" data-toggle="tooltip" data-placement="left" title="Permission">
                                        <i class="fa fa-server" aria-hidden="true"></i> Permission
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No users with lunch permission.</td> <!-- Adjusted colspan for the new column -->
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
