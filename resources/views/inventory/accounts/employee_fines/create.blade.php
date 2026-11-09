@extends('layouts.master')

@section('title', 'Create Employee Fine')

@section('content')


<div class="row" style = "max-width: 65%; margin: 0 auto; padding-left: 25px; padding-right: 25px;">
    <div class="col-sm-offset-2 col-sm-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Add New Fine</h3>
            </div>
            <div class="panel-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul> 
                </div>
                @endif

                <form method="POST" action="{{ route('employee_fines.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="au_user_id">User:</label>
                        @php
                            // Sort the $users collection by au_user_id in ascending order
                            $sorted_users = $users->sortBy('au_user_id');
                        @endphp

                        <select class="form-control" id="au_user_id" name="au_user_id">
                            <option value="">Select Option</option>
                            <!-- Iterate over the sorted users and populate the select options -->
                            @foreach ($sorted_users as $user)
                                <option value="{{ $user->au_user_id }}">{{ $user->au_user_id }} ({{ $user->au_name }}){{ isset($leftIds) && $leftIds->has($user->au_id) ? ' (left)' : '' }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="title">Reason:</label>
                        <select class="form-control" id="title" name="title">
                            <option value="">Select Option</option>
                            @foreach ($fines as $fine)
                            <option value="{{ $fine->id }}" {{ old('title') == $fine->id ? 'selected' : '' }}>{{ $fine->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}">
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="text" class="form-control" id="date" placeholder="Enter a date.." name="date" value="{{ old('date', $employeeFine->date ?? '') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script>
            $("#date").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
            format: 'dd/mm/yyyy'
        });


</script>
@endsection

