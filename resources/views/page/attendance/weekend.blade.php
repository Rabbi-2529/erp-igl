@extends('layouts.master')

@section('title', 'Manage Holidays')

@section('human_resource_class','active')
@section('attendence_class','active')
@section('holiday_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Weekends</h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    @if($weekend)
                        <div class="box-header with-border">
                            <blockquote>
                                <p class="offday" style="font-variant: small-caps; font-weight:bold;">Your official weekend is: <span style="color: red">{{ $weekend->weekend }}</span></p>
                            </blockquote>
                        </div>
                    @endif

                    <div class="col-sm-12">
                        <div class="box-main">
                            @if($weekend)
                            <h4 class="box-title">Update Your Current Weekend:</h4>
                            @else
                            <h4 class="box-title">Add Weekend</h4>
                            @endif

                            {{ Form::open(['route' => 'payroll.add-weekend', 'method' => 'post', 'id' => 'weekend']) }}
                            @csrf

                            <div class="form-group">
                                <label for="weekendSelect">Select Weekend:</label>
                                <select class="form-control" id="weekendSelect" name="weekend[]" multiple>
                                    <option value="saturday" {{ $weekend && str_contains($weekend->weekend, 'saturday') ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ $weekend && str_contains($weekend->weekend, 'sunday') ? 'selected' : '' }}>Sunday</option>
                                    <option value="monday" {{ $weekend && str_contains($weekend->weekend, 'monday') ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ $weekend && str_contains($weekend->weekend, 'tuesday') ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ $weekend && str_contains($weekend->weekend, 'wednesday') ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ $weekend && str_contains($weekend->weekend, 'thursday') ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ $weekend && str_contains($weekend->weekend, 'friday') ? 'selected' : '' }}>Friday</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary pull-right">Save Weekends</button>

                            {{ Form::close() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


            <div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title" style="padding-bottom: 0;">
                    <div class="row">
                        <div class="col-xs-6">
                            <h4>Holidays</h4>
                        </div>
                        <div class="col-xs-6 text-right">
                            <a href="{{ route('payroll.add-holiday') }}" class="btn btn-primary">Update Holidays</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body">
    <div class="row">
        <div class="box-header with-border">
            <blockquote>
                <p class="offday" style="font-variant: small-caps;">Your official holidays:</p>

                @php
                    $months = [];
                    foreach ($holiday as $h) {
                        $holidayMonth = \Carbon\Carbon::createFromDate($h->year, $h->month, $h->date)->format('F');
                        $holidayDate = \Carbon\Carbon::createFromDate($h->year, $h->month, $h->date)->format('d');
                        if (!isset($months[$holidayMonth])) {
                            $months[$holidayMonth] = [];
                        }
                        $months[$holidayMonth][] = $holidayDate;
                    }
                @endphp

                @foreach(range(1, 12) as $monthIndex)
                    @php
                        $currentMonth = \Carbon\Carbon::create()->month($monthIndex)->format('F');
                    @endphp
                    <li>{{ $currentMonth }} - 
                        @if(isset($months[$currentMonth]))
                            <span style="color: red">{{ implode(', ', $months[$currentMonth]) }}</span>
                        @else
                            No holidays found
                        @endif
                    </li>
                @endforeach
            </blockquote>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>




@endsection
