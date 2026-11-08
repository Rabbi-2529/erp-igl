@extends('layouts.master')

@section('title', 'Manage Holidays')

@section('human_resource_class','active')
@section('attendence_class','active')
@section('holiday_class','active')

@section('content')

<style>
    /* Your CSS styles here */
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Office Holidays</h4>
                </div>
            </div>
            <div class="panel-body">
                <form action="" method="get">
                    <div class="row print-hidden">
                        <div class="col-xs-3">
                            <!-- Year dropdown -->
                            <select class="form-control" name="year" id="year">
                                @for($this_year = date('Y'); $this_year >= 2007; $this_year--)
                                    <option value="{{ $this_year }}" {{ $request_year == $this_year ? 'selected' : '' }}>
                                        {{ $this_year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <!-- Month dropdown -->
                            <select class="form-control" name="month" id="month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                            {{ $request_month == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </div>
                </form>
                <br>
                <!-- Calendar view goes here -->
                <div class="table-responsive">
                    <form id="holidayForm">
                        @csrf
                        <input type="hidden" name="year" value="{{ $request_year ?? date('Y') }}">
                        <input type="hidden" name="month" value="{{ $request_month ?? date('m') }}">
                        <table class="table table-bordered calendar-table">
                            <thead>
                                <tr>
                                    <th class="cal-header">Sun</th>
                                    <th class="cal-header">Mon</th>
                                    <th class="cal-header">Tue</th>
                                    <th class="cal-header">Wed</th>
                                    <th class="cal-header">Thu</th>
                                    <th class="cal-header">Fri</th>
                                    <th class="cal-header">Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Table Data show here --}}
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Function to update the calendar view
    function updateCalendar() {
        var year = $("#year").val();
        var month = $("#month").val();
        var daysInMonth = new Date(year, month, 0).getDate();
        var firstDayOfMonth = new Date(year, month - 1, 1).getDay();
        var calendarBody = $('.calendar-table tbody');

        // Clear existing calendar content
        calendarBody.empty();

        var dayNumber = 1;

        // Fetch existing holidays for the selected month and year
        $.ajax({
            type: 'GET',
            url: '{{ route("payroll.getExistingHolidays") }}',
            data: {
                year: year,
                month: month
            },
            success: function(response) {
                var existHoliday = response.map(holiday => holiday.date);
                existHoliday = existHoliday.map(function(dateString) {
                    return parseInt(dateString, 10); // Parse string to integer with base 10 (decimal)
                });

                for (var i = 0; i < 6; i++) {
                    var row = $('<tr></tr>');

                    for (var j = 0; j < 7; j++) {
                        var cell = $('<td class="cal-cell"></td>');

                        if ((i > 0 || j >= firstDayOfMonth) && dayNumber <= daysInMonth) {
                            cell.append('<span class="date-bold">' + dayNumber + '</span><br>');

                            // Create the date in 'YYYY-MM-DD' format
                            var dateValue = year + '-' + month.padStart(2, '0') + '-' + dayNumber.toString().padStart(2, '0');

                            // Log current date to console

                            // Create a checkbox element
                            var checkbox = $('<input>').attr({
                                type: 'checkbox',
                                name: 'selected_dates[]',
                                value: dateValue
                            });
                            
                            // Check if the date is in the existing holidays
                            if (existHoliday.includes(parseInt(dayNumber))) {
                                checkbox.prop('checked', true);
                            }

                            // Append the checkbox to the cell
                            cell.append(checkbox);

                            dayNumber++;
                        } else {
                            cell.append('<span>&nbsp;</span>'); // Add empty cell for days outside the current month
                        }

                        row.append(cell);
                    }

                    calendarBody.append(row);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.status + ': ' + xhr.responseText);
            }
        });
    }

    // Call the function when the year or month dropdown changes
    $('#year, #month').change(function() {
        updateCalendar();
    });

    // Initial update when the page loads
    updateCalendar();
});

$('#holidayForm').submit(function(e) {
    e.preventDefault();

    // Serialize the form data
    var formData = $('#holidayForm').serializeArray();

    // Retrieve all date values from the checkboxes
    var selectedDates = $('input[name="selected_dates[]"]:checked').map(function() {
        return this.value;
    }).get();

    // Retrieve all date values for the current month
    var allDates = $('input[name="selected_dates[]"]').map(function() {
        return this.value;
    }).get();

    // Remove allDates that are selected
    var uncheckedDates = allDates.filter(date => !selectedDates.includes(date));

    // Add unchecked dates to formData
    uncheckedDates.forEach(function(date) {
        formData.push({ name: 'unchecked_dates[]', value: date });
    });

    // Log unchecked dates to console
    console.log('Unchecked Dates:', uncheckedDates);

    // AJAX request
    $.ajax({
        type: 'POST',
        url: '{{ route("payroll.holidays") }}',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function(response) {
            alert('Holidays saved successfully!');
        },
        error: function(xhr, status, error) {
            console.error(xhr.status + ': ' + xhr.responseText);
        }
    });
});
</script>
@endsection
