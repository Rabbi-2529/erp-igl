@extends('layouts.master')

@section('company_class','active')
@section('branch_class','active')
@section('add_branch_class','active')
@section('content')
<style>
.centered-form {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
}
</style>
<div class="row justify-content-center">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag centered-form">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Register New Branch</h4>
                </div>
            </div>
            <div class="panel-body">
                {{-- Show validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('branch.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="branch_name">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="branch_name" id="branch_name" class="form-control" value="{{ old('branch_name') }}" required>
                    </div>

             
                    <div class="form-group">
                        <label for="branch_address">Branch Address</label>
                        <textarea name="branch_address" id="branch_address" class="form-control">{{ old('branch_address') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="branch_city">City</label>
                        <input type="text" name="branch_city" id="branch_city" class="form-control" value="{{ old('branch_city') }}">
                    </div>

                    <div class="form-group">
                        <label for="branch_state">State</label>
                        <input type="text" name="branch_state" id="branch_state" class="form-control" value="{{ old('branch_state') }}">
                    </div>

                    <div class="form-group">
                        <label for="branch_zip">ZIP / Postal Code</label>
                        <input type="text" name="branch_zip" id="branch_zip" class="form-control" value="{{ old('branch_zip') }}">
                    </div>

                    <div class="form-group">
                        <label for="branch_hotline">Hotline</label>
                        <input type="text" name="branch_hotline" id="branch_hotline" class="form-control" value="{{ old('branch_hotline') }}">
                    </div>

                    <div class="form-group">
                        <label for="branch_email">Email</label>
                        <input type="email" name="branch_email" id="branch_email" class="form-control" value="{{ old('branch_email') }}">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Branch</button>
                    <a href="{{ route('branch.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
