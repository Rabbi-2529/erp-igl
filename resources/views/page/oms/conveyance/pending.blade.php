@extends('layouts.master')
@section('title', 'Pending Bill')

@section('oms_class', 'active')
@section('conveyance_class', 'active')
@section('conveyance_pending_class', 'active')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
.swal2-popup {
    display: none;
    position: relative;
    box-sizing: border-box;
    width: 39em;
    height: 295px;
    max-width: 100%;
    padding: 0 0 1.25em;
    border: none;
    border-radius: 5px;
    background: #fff;
    font-family: inherit;
    font-size: 1rem;
}
/* Small "confirmed" toast must not use the big popup size above */
.swal2-popup.swal2-toast {
    width: auto;
    height: auto;
    padding: .75em 1em;
}
.copyButton {
   background-color: #4CAF50;
    color: white;
    border: none;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    padding: 2px 3px;
}

.copyButton:hover {
    background-color: #45a049;
}

.copyButton i {
    margin-right: 5px; /* Space between icon and text */
}

/* Fade out a row after it is confirmed */
tr.row-confirmed td {
    background: #dff0d8 !important;
    transition: opacity .4s ease;
    opacity: .3;
}
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Pending Bill</h4>
                </div>
            </div>

            <div class="panel-body">
                <table id="datatable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>SL</th>
                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            <th>Entry Date</th>
                            @else
                            <th style="display:none;"></th>
                            @endif
                            <th>Bill Date</th>
                            <th>For whom</th>
                            <th>Reason</th>
                            <th>Employee</th>
                            <th>From</th>
                            <th>Mode</th>
                            <th>To</th>
                            <th>Debit/খরচ</th>
                            <th>Balance</th>
                            <th>Verification</th>
                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            <th class="text-center">Action</th>
                            @else
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php($balance = 0)
                        @php($sl = 0)
                        @php($hasPermission = explode('-', Auth::user()->au_permission_status))
                        @foreach($pendings as $pending)
                        @php($visit = App\Models\Oms_inventory::notifycus(!empty($pending->visitInfo['cf_id']) ? $pending->visitInfo['cf_id'] : '',$pending->oms_bill_date))
                        <tr>
                            <td>{{ ++$sl }}</td>
                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            <td>{{ $pending->oms_insert_at }}</td>
                            @else
                            <td style="display:none;"></td>
                            @endif
                            <td>{{ $pending->oms_bill_date }}</td>
                            <td>
                                <a href="{{ url('/client_feedback',['id' => $pending->oms_what_for]) }}"
                                   title="{{ $pending->what_info != '' ? $pending->what_info['qb_company_name'] : '' }}"
                                   class="opener" target="_blank"
                                   style="{{ ($visit == true)? 'color: green;' : 'color: red;' }}">
                                   {{ $pending->what_info != "" ? $pending->what_info['qb_mobile'] : "" }}
                                </a>
                                <span>{{ isset($pending->projects['project_name']) ? $pending->projects['project_name'] : '' }}</span>
                            </td>
                            <td>
                                <button class="copyButton">
                                    {{ isset($pending->reason->name) ? $pending->reason->name : 'No Reason' }}
                                </button>
                            </td>

                            <td>{{ $pending->employee_info['au_name'] }}
                                {{ $pending->oms_staffs_user_id ? '(' . $pending->oms_staffs_user_id . ')' : '' }}</td>

                            <td>{{ $pending->oms_dfrom }}</td>
                            <td>{{ isset($pending->mode->name) ? $pending->mode->name : '' }}</td>
                            <td>{{ $pending->oms_dto }}</td>
                            <td align="right">{{ number_format($pending->oms_debit, 2) }}</td>

                            @php($balance += $pending->oms_debit)

                            <td align="right">{{ number_format($balance, 2) }}</td>

                            <!-- Verification Buttons -->
                            <td align="center">
                                @if($pending->oms_status == 9)
                                    <span class="badge badge-success">Verified</span>
                                @elseif(Auth::user()->au_user_type == 4 || in_array(99, $hasPermission))
                                    <button class="btn btn-success btn-xs verify-btn" data-id="{{ $pending->sl_id }}" data-action="accept">
                                        <span class="glyphicon glyphicon-ok"></span>
                                    </button>

                                    <button class="btn btn-danger btn-xs verify-btn" data-id="{{ $pending->sl_id }}" data-action="reject">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </button>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            @if((Auth::user()->au_user_type == 4 || (Auth::user()->au_user_type == 5 || in_array(92, $hasPermission))) && $pending->oms_status == 9)
                                <td align="center">
                                    <a href="{{ route('pages.convence_pending_bill_confirm', ['id' => $pending->sl_id, 'search' => request('search')]) }}"
                                        class="btn btn-success btn-xs confirm-btn" style="color: green;" title="Confirm bill">
                                        <span style="color: #fff" class="glyphicon glyphicon-ok"></span>
                                    </a>

                                    &nbsp;&nbsp;

                                    <button type="button" class="btn btn-danger btn-xs reject-btn" data-id="{{ $pending->sl_id }}" style="color: red;">
                                        <span style="color: #fff" class="glyphicon glyphicon-remove"></span>
                                    </button>
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if(Auth::user()->au_user_type == 6)
                        <tr>
                            <td colspan="9" align="right"><strong>Total</strong></td>
                            <td align="right"></td>
                            <td align="right"><strong id="total-balance">{{ number_format($balance, 2) }}</strong></td>
                            <td></td>
                        </tr>
                        @endif
                        @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                        <tr>
                            <td colspan="9" align="right"><strong>Total</strong></td>
                            <td align="right"><strong id="total-debit">0.00</strong></td>
                            <td align="right"><strong id="total-balance">{{ number_format($balance, 2) }}</strong></td>
                            <td></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal (one modal for all rows) -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="rejectModalLabel">Reject Bill</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="rejectId">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason</label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#datatable').DataTable({
            "stateSave": true,
            "processing": true,
            "serverSide": false,
        });

        var DEBIT_COL = 9;
        var BALANCE_COL = 10;

        function money(value) {
            return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Total debit of the rows matching the current search
        function updateTotals() {
            var totalDebit = 0;

            table.rows({ search: 'applied' }).every(function() {
                var debit = parseFloat($(this.node()).find('td:eq(' + DEBIT_COL + ')').text().replace(/,/g, ''));
                if (!isNaN(debit)) {
                    totalDebit += debit;
                }
            });

            $('#total-debit').text(money(totalDebit));
        }

        // Rebuild the running "Balance" column and the SL numbers after a row is removed
        function recalcBalance() {
            var running = 0;
            var sl = 0;

            table.rows({ order: 'applied' }).every(function() {
                var node = this.node();
                var debit = parseFloat($(node).find('td:eq(' + DEBIT_COL + ')').text().replace(/,/g, ''));
                if (!isNaN(debit)) {
                    running += debit;
                }
                table.cell(node, 0).data(++sl);
                table.cell(node, BALANCE_COL).data(money(running));
            });

            $('#total-balance').text(money(running));
            table.draw(false);
            updateTotals();
        }

        // Trigger the updateTotals function whenever a search is performed
        table.on('search.dt', function() {
            updateTotals();
        });

        $(document).on('click', '.copyButton', function() {
            var mobileNumber = $(this).closest('tr').find('a').text().trim();
            table.search(mobileNumber).draw();
        });

        // Confirm (Action ✔) without reloading the page
        $(document).on('click', '.confirm-btn', function(e) {
            e.preventDefault();

            var button = $(this);
            if (button.hasClass('disabled')) {
                return;
            }

            var row = button.closest('tr');
            var original = button.html();
            button.addClass('disabled').html('<i class="fas fa-spinner fa-spin" style="color:#fff"></i>');

            $.ajax({
                url: button.attr('href'),
                type: 'GET',
                dataType: 'json'
            }).done(function(response) {
                if (response.success) {
                    row.addClass('row-confirmed');
                    setTimeout(function() {
                        table.row(row).remove();
                        recalcBalance();
                    }, 400);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    button.removeClass('disabled').html(original);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            }).fail(function(xhr) {
                button.removeClass('disabled').html(original);
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not confirm the bill. Please try again.';
                Swal.fire({ icon: 'error', title: 'Error', text: message });
            });
        });

        // Trigger the updateTotals function initially to set the totals when the page loads
        updateTotals();

        // Restore DataTable state on page load
        if (table) {
            table.state.load();
        }
    });
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.verify-btn', function(e) {
            e.preventDefault();

            var button = $(this);
            var id = button.data('id');
            var action = button.data('action');
            var url = action === 'accept'
                ? `{{ route('pages.convence_verification_accept', ':id') }}`.replace(':id', id)
                : `{{ route('pages.convence_verification_reject', ':id') }}`.replace(':id', id);

            // If action is reject, prompt for rejection reason
            if (action === 'reject') {
                Swal.fire({
                    title: 'Rejection Reason',
                    input: 'textarea',
                    inputLabel: 'Reason for rejection',
                    inputPlaceholder: 'Enter the reason for rejection...',
                    inputAttributes: {
                        'aria-label': 'Enter the reason for rejection'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('Please enter a rejection reason.');
                        }
                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ rejection_reason: reason })
                        }).then(response => response.json())
                        .catch(error => {
                            Swal.showValidationMessage('Request failed: ' + error);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload(); // Reload the page when rejection reason is given
                    }
                });
            } else {
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                updateRow(button, action, id);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred: ' + xhr.statusText
                        });
                    }
                });
            }
        });

        function updateRow(button, action, id) {
            var row = button.closest('tr');

            // Update the status cell with a badge
            var statusCell = row.find('td.status-cell');
            if (statusCell.length) {
                var badgeClass = action === 'accept' ? 'badge-success' : 'badge-danger';
                var statusText = action === 'accept' ? 'Verified' : 'Rejected';
                statusCell.html(`<span class="badge ${badgeClass}">${statusText}</span>`);
            }
            row.find('td:eq(11)').html('<span class="badge badge-success">Verified</span>');

            // Remove the verify buttons
            var actionButtons = row.find('.verify-btn');
            if (actionButtons.length) {
                actionButtons.remove();
            }

            // Show Confirm / Reject buttons (Confirm works without reload via .confirm-btn)
            var searchParam = '{{ request('search') }}';
            row.find('td:last').html(`
                <a href="{{ route('pages.convence_pending_bill_confirm', ['id' => ':id', 'search' => ':search']) }}"
                   class="btn btn-success btn-xs confirm-btn" style="color: green;" title="Confirm bill">
                   <span style="color: #fff" class="glyphicon glyphicon-ok"></span>
                </a>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-danger btn-xs reject-btn" data-id=":id" style="color: red;">
                    <span style="color: #fff" class="glyphicon glyphicon-remove"></span>
                </button>
            `.replace(/:id/g, id).replace(/:search/g, searchParam));
        }
    });
</script>
<script type="text/javascript">
    window.onload = function(){
    var a = document.querySelectorAll('.opener'), w = [], url, random, i;
    for(i = 0; i < a.length; i++){
      (function(i){
        a[i].onclick = function(e) {
          if (!w[i] || w[i].closed) {
            url = this.href;
            random = Math.floor((Math.random() * 100) + 1);
            w[i] = window.open(url);
          } else {
            console.log('window ' + url + ' is already opened');
          }
          e.preventDefault();
          w[i].focus();
        };
      })(i);
    }
    };
  </script>
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.reject-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var formAction = "{{ route('pages.convence_pending_bill_reject', ':id') }}".replace(':id', id);
        $('#rejectForm').attr('action', formAction);
        $('#rejectId').val(id);
        $('#rejectModal').modal('show');
    });
});
</script>

@endsection