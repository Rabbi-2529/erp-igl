@extends('layouts.master')

@section('title', 'Add New Fine')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Add New Fine</h3>
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

                <form method="POST" action="{{ route('fines.store') }}" class="form-horizontal">
                    @csrf
                    <div class="form-group">
                        <label for="title" class="col-sm-2 control-label">Title <span class="text-danger">*</span></label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" required>
                            @error('title')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="{{ route('fines.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
