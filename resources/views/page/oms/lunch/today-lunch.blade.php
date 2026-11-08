@extends('layouts.master')

@section('title', 'Today Employee Lunch')

@section('oms_class', 'active')
@section('office_lunch_class', 'active')
@section('today_lunch_class', 'active')

@section('content')

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .print-only {
            display: table-cell !important;
        }

        body {
            zoom: 85%; /* Adjust as needed */
            margin: 0 !important;
            padding: 0 !important;
        }

        .panel-body {
            padding: 5px !important;
            margin: 0 !important;
        }

        table {
            width: 100% !important;
            font-size: 12px !important; /* Smaller font for print */
            border-collapse: collapse !important;
        }

        th, td {
            padding: 3px !important; /* Reduced padding */
            border: 1px solid #000 !important;
        }

        /* Prevent page breaks inside table rows */
        tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* Ensure table doesn't break across pages if possible */
        table {
            page-break-inside: auto !important;
        }

        /* Reduce margin around headings */
        h4 {
            margin: 5px 0 !important;
            padding: 0 !important;
        }

        /* Hide unnecessary elements */
        .panel-bd, .lobidrag {
            border: none !important;
            box-shadow: none !important;
        }
    }

    .print-only {
        display: none;
    }

    /* For screen view, keep original styling */
    @media screen {
        .panel-body {
            padding: 15px;
        }
        
        table {
            font-size: 14px;
        }
        
        th, td {
            padding: 8px;
        }
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title no-print">
                    <h4>Today Employee Lunch</h4>
                </div>
            </div>

            <div class="panel-body">
                <table id="lunchshowtable" class="table table-striped table-bordered table-hover">
                    <h4 class="text-center">Employee Lunch List of: <strong>{{ now()->format('d F Y') }}</strong></h4>
                    <button class="btn btn-primary no-print pull-right" onclick="window.print()">Print</button>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 30px;">SL.</th>
                            <th class="text-center" style="width: 70px;">Emp ID</th>
                            <th class="text-center" style="width: 260px;">Name</th>
                            <th class="text-center" style="width: 220px;">Request Time | Cancel Time</th>
                            <th class="text-center">Lunch Notes</th>
                            <th class="text-center print-only" style="width: 120px;">Signature</th>
                            <th class="text-center no-print" style="width: 120px;">Cancel Reason</th>
                            <th class="no-print text-center" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
    @php
        // Separate employees and guests
        $employees = $todayLunchUsers->filter(function($lunchUser) {
            return $lunchUser->status != 2;
        });

        $guests = $todayLunchUsers->filter(function($lunchUser) {
            return $lunchUser->status == 2;
        });

        // Sort employees by Emp ID in ascending order
        $sortedEmployees = $employees->sortBy(function($lunchUser) use ($userDetails) {
            $userDetail = $userDetails->where('au_id', $lunchUser->user_id)->first();
            return optional($userDetail)->au_user_id;
        });

        // Combine sorted employees and guests
        $sortedLunchUsers = $sortedEmployees->merge($guests);

        // Initialize serial number
        $serialNumber = 1;
    @endphp

    @forelse($sortedLunchUsers as $lunchUser)
        @php
            $userDetail = $userDetails->where('au_id', $lunchUser->user_id)->first();
        @endphp

        <tr>
            {{-- Serial --}}
            <td class="text-center">{{ $serialNumber++ }}</td>

            {{-- Emp Id --}}
            <td class="text-center">
                @if ($lunchUser->status == 2)
                    Guest
                @else
                    {{ optional($userDetail)->au_user_id }}
                @endif
            </td>

            {{-- Emp Name --}}
            <td>
                @if ($lunchUser->status == 2)
                    @if (!is_null($lunchUser->lunch_cancel))
                        <span style="text-decoration: line-through;">
                            Total <span class="text-danger" style="font-weight: bold;"> {{ $lunchUser->total_meal }}</span> Guest from <span class="text-danger"> {{ optional($userDetail)->au_name }}</span>
                        </span>
                    @else
                        Total <span class="text-danger" style="font-weight: bold;"> {{ $lunchUser->total_meal }}</span> Guest from <span class="text-danger"> {{ optional($userDetail)->au_name }}</span>
                    @endif
                @else
                    @if (!is_null($lunchUser->lunch_cancel))
                        <span style="text-decoration: line-through;">{{ optional($userDetail)->au_name }}</span>
                    @else
                        {{ optional($userDetail)->au_name }}
                    @endif
                @endif
            </td>

            {{-- Request Time | Cancel Time --}}
            <td class="text-center">
                {{ \Carbon\Carbon::parse($lunchUser->lunch_in)->format('H:i:s') }}
                
                @if($lunchUser->lunch_cancel)
                    |<span class="text-danger">{{ \Carbon\Carbon::parse($lunchUser->lunch_cancel)->format('H:i:s') }}</span>
                @endif
            </td>                                
            {{-- Lunch Notes --}}
            <td class="text-center">
                @if (!is_null($lunchUser->notes))
                    {{ $lunchUser->notes }}
                @else
                    N/A
                @endif
            </td>

            {{-- Signature --}}
            <td class="text-center print-only">
                {{-- Empty cell for signature --}}
            </td>

            {{-- Cancel Reason --}}
            <td class="text-center no-print">
                @if (!is_null($lunchUser->cancel_reason))
                    <span class="text-danger">{{ $lunchUser->cancel_reason }}</span>
                @else
                    N/A
                @endif
            </td>
            {{-- Action --}}
            <td class="text-center no-print">
                @if (is_null($lunchUser->lunch_cancel))

                    @if (!$cancelTimeMatch)
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancelLunchModal{{ $lunchUser->id }}">
                            Cancel Lunch
                        </button>
                    @else
                        <span class="text-danger" style="font-weight:bold;">Time Out</span>
                    @endif
                @else
                    <span class="text-danger" style="font-weight:bold;">Canceled</span>
                @endif
            </td>

            {{-- Modal For Cancel START --}}
            <div class="modal fade" id="cancelLunchModal{{ $lunchUser->id }}" tabindex="-1" role="dialog" aria-labelledby="cancelLunchModalLabel{{ $lunchUser->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <!-- Modal content -->
                    </div>
                </div>
            </div>
            {{-- Modal For Cancel END --}}
        </tr>
    @empty
        {{-- If data not available it will show no lunch data --}}
        <tr>
            <td class="text-center" colspan="7">No lunch data available today!</td>
        </tr>
    @endforelse
</tbody>
                </table>
                {{-- Total Meal Section --}}
                <p>
                    <span style="text-decoration: underline;">Total Meal:</span>
                    
                    @if($todayLunchUsers && count($todayLunchUsers) > 0)
                        <strong style="font-size:15px;">{{ $lunchCount + 1 }}</strong>
                        <span style="font-size: smaller;">(Included cook.)</span>
                    @else
                        <strong style="font-size:15px;">0</strong>
                    @endif
                </p>
                

                <div class="text-center">
                    <button class="btn btn-success no-print" data-toggle="modal" data-target="#addGuestModal">Add Guest</button>
                    <button class="btn btn-success no-print" data-toggle="modal" data-target="#addEmpModal">Add Employee</button>
                </div>
            </div>

            {{-- Modal For Gaust Add --}}
            <div class="modal fade" id="addGuestModal" tabindex="-1" role="dialog" aria-labelledby="addGuestModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="addGuestModalLabel">Add Guest</h4>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('manual-lunch-entry') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="totalMeal">Total Guest</label>
                                            <input type="number" placeholder="Guest Count" class="form-control" id="totalMeal" name="total_meal" required>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label for="note">Notes</label>
                                            <input type="text" placeholder="Write something about guest meal.(Optional)" class="form-control" id="notes" name="notes">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal For Employee Add -->
            <div class="modal fade" id="addEmpModal" tabindex="-1" role="dialog" aria-labelledby="addEmpModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="addEmpModalLabel">Add Employee</h4>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('lunch-mark-admin') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="employeeList">Select Employee(s):</label>
                                    <select class="form-control" id="employeeList" name="user_id" required>
                                        <!-- Hidden option -->
                                        <option value="" hidden>Select an employee</option>

                                        <!-- Sort $users by au_user_id before populating the dropdown -->
                                        @php
                                            $sortedUsers = $users->sortBy('au_user_id');
                                        @endphp

                                        <!-- Populate with sorted user list -->
                                        @foreach($sortedUsers as $user)
                                            <option value="{{ $user->au_id }}"> {{ $user->au_user_id }} ({{ $user->au_name }})</option>
                                        @endforeach
                                    </select>                                      
                                </div>
                                <button type="submit" class="btn btn-primary">Add Lunch Entry</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('').DataTable({
            
            
        });
    });
</script>

@endsection
