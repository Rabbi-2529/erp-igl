@extends('layouts.master')

@section('title', 'Assign User to Branch')
@section('company_class','active')
@section('branch_class','active')
@section('assign_branch_class','active')
@section('content')
<style>
.centered-form {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
}
</style>
<div class="row justify-content-center" style="margin-top: 30px;">
    <div class="col-sm-6">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Assign Branch to User</h4>
                </div>
            </div>
            <div class="panel-body">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('branch.assign') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="user_id">Select User <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="" disabled selected>-- Select User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->au_id }}" {{ old('user_id') == $user->au_id ? 'selected' : '' }}>
                                    {{ $user->au_name }} ({{ $user->au_email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="branch_id">Select Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" id="branch_id" class="form-control" required>
                            <option value="" disabled selected>-- Select Branch --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->branch_id }}" {{ old('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Assign Branch</button>
                    <a href="{{ route('branch.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
