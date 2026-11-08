@extends('layouts.master')
@section('title', 'Rejected Bill')

@section('oms_class','active')
@section('conveyance_class','active')
@section('conveyance_reject_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Rejected Bill</h4>
                </div>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      @if(Auth::user()->au_user_type == 4)
                      <th>Entry Date</th>
                      @endif
                      <th>Bill Date</th>
                      {{-- <th>Reason</th> --}}
                      <th>Employee</th>
                      <th>What For</th>
                      <th>From</th>
                      <th>Mode</th>
                      <th>To</th>
                      <th>Debit/খরচ</th>
                      <th>Balance</th>
                      <th>Status</th>
                      @if( Auth::user()->au_user_type != 4 )
                      <th class="text-center">Action</th>
                      @endif
                    </tr>
                    </thead>
                    <tbody>
                    @php($balance=0)
                    @php($sl=0)
                    @foreach($rejects as $reject)
                    <tr>
                      <td>{{ ++$sl }}</td>
                      @if(Auth::user()->au_user_type == 4)
                      <td>{{ $reject->oms_insert_at }}</td>
                      @endif
                      <td>{{ $reject->oms_bill_date }}</td>
                      {{-- <td>{{ isset($reject->reason->name ) ? $reject->reason->name : '' }}</td> --}}
                      <td>{{ $reject->employee_info['au_name'] }} {{$reject->oms_staffs_user_id ? $reject->oms_staffs_user_id : '' }}</td>
                      <td>
                        <a href="{{ url('/client_feedback',['id' => $reject->oms_what_for]) }}" title="{{ $reject->what_info != '' ? $reject->what_info['qb_company_name'] : '' }}" class="opener" target="_blank">
                          {{ $reject->what_info != "" ? $reject->what_info['qb_mobile'] : ""}}
                        </a>
                          <span>{{isset($reject->projects['project_name'] )? $reject->projects['project_name']:'' }}</span>
                      </td>
    
                      <td>{{ $reject->oms_dfrom }}</td>
                      <td>{{ isset($reject->mode->name ) ? $reject->mode->name : '' }}</td>
                      <td>{{ $reject->oms_dto }}</td>
                      <td align="right">{{ number_format($reject->oms_debit,2) }}</td>
                      @php($balance = $balance - $reject->oms_debit)
                      <td align="right">{{ number_format($balance,2) }}</td>
                      <td>
                        <span class="status-cell" data-status="{{ $reject->oms_status }}" data-reason="{{ $reject->oms_rejection_reason }}">
                            @if($reject->oms_status == 3)
                                <span class="badge badge-danger">Rejected</span>
                            @elseif($reject->oms_status == 10)
                                <span class="badge badge-warning">Not Verified</span>
                            @else
                                <span class="badge badge-secondary">Pending</span>
                            @endif
                        </span>
                      </td>
                      @if( Auth::user()->au_user_type != 4 )
                      <td align="center"><a href="{{ url('/oms_update_bill',['id' => $reject->sl_id]) }}" class="btn btn-info btn-sm">Edit</a></td>
                      @endif
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                      @if( Auth::user()->au_user_type == 4 )
                      <td colspan="8" align="right">Total: </td>
                      <td></td>
                      <td align="right" >{{ number_format($balance,2) }}</td>
                      @elseif(  Auth::user()->au_user_type != 4  )
                      <td colspan="7" align="right">Total</td>
                      <td align="right" >{{ number_format($balance,2) }}</td>
                      <td></td>
                      @endif
                    </tfoot>
                  </table>
            </div>
        </div>
    </div>
</div>
<!-- Status Reason Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="statusModalLabel">Status Reason</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><strong>Status:</strong> <span id="statusText"></span></p>
                <p><strong>Reason:</strong> <span id="statusReasonText"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.status-cell', function() {
            var status = $(this).data('status');
            var reason = $(this).data('reason');

            var statusText;
            if (status == 3) {
                statusText = 'Rejected';
            } else if (status == 10) {
                statusText = 'Not Verified';
            } else {
                statusText = 'Pending';
            }

            $('#statusText').text(statusText); // Set status text
            $('#statusReasonText').text(reason); // Set reason text
            $('#statusModal').modal('show'); // Show the modal
        });
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


@endsection