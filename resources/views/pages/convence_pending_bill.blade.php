@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_pending_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
          Conveyance
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Pending Conveyance Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="datatable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
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
                  @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                  <th class="text-center">Action</th>
                    @else
                        <th>Action</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @php($balance=0)
                @php($sl=0)
                @foreach($pendings as $pending)
                @php($visit = App\Models\Oms_inventory::notifycus(!empty($pending->visitInfo['cf_id']) ? $pending->visitInfo['cf_id'] : '',$pending->oms_bill_date))
                @php($hasPermission = explode('-', Auth::user()->au_permission_status))
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                  <td>{{ $pending->oms_insert_at }}</td>
                    @else
                        <td style="display:none;"></td>
                    @endif
                  <td>{{ $pending->oms_bill_date }}</td>
                  <td>

                    <a href="{{ url('/client_feedback_oms',['id' => $pending->oms_what_for]) }}" title="{{ $pending->what_info != '' ? $pending->what_info['qb_company_name'] : '' }}" class="opener" target="_blank" style="{{ ($visit == true)? 'color: green;' : 'color: red;' }}">
                      {{ $pending->what_info != "" ? $pending->what_info['qb_mobile'] : "" }}
                    </a>
                      <span>{{isset($pending->projects['project_name'] )? $pending->projects['project_name']:'' }}</span>
                  </td>
                 <td>{{ isset($pending->reason->name ) ? $pending->reason->name : '' }}</td>
                  <td>{{ $pending->employee_info['au_name'] }} {{$pending->oms_staffs_user_id ? '(' . $pending->oms_staffs_user_id .')': '' }}</td>

                  <td>{{ $pending->oms_dfrom }}</td>
                  <td>{{ isset($pending->mode->name ) ? $pending->mode->name : '' }}</td>
                  <td>{{ $pending->oms_dto }}</td>
                  <td align="right">{{ number_format($pending->oms_debit,2) }}</td>
                  @php($balance = $balance + $pending->oms_debit)
                  <td align="right">{{ number_format($balance,2) }}</td>
                  @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && in_array(92, $hasPermission))
                  <td align="center">
                    <a href="{{ route('pages.convence_pending_bill_confirm',['id' => $pending->sl_id]) }}" class="btn btn-success btn-xs" style="color: green;">
                      <span style="color: #fff" class="glyphicon glyphicon-ok"></span>
                    </a>

                    &nbsp;&nbsp;

                    <a href="{{ route('pages.convence_pending_bill_reject',['id' => $pending->sl_id]) }}" class="btn btn-danger btn-xs" style="color: red;">
                      <span style="color: #fff" class="glyphicon glyphicon-remove"></span>
                    </a>
                  </td>
                    @else
                    <td></td>
                    @endif
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                @if( Auth::user()->au_user_type == 6 )

                  <td colspan="9" align="right">Total:</td>
                  <td align="right"></td>
                  <td align="right">{{ number_format($balance, 2) }}</td>
                  <td></td>
                @endif
                @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                <td colspan="9" align="right">Total:</td>
                <td align="right"></td>
                <td align="right">{{ number_format($balance, 2) }}</td>
                <td></td>
                @endif
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
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
        $('#datatable').dataTable({
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                };

                // Total over all pages
                total = api
                    .column(9, {filter: 'applied'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b) ;
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(9, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                // $(api.column(8).footer()).html(pageTotal + ' ( $' + total + ' total)');
                $(api.column(9).footer()).html( total);
            },
        })

    })
    // $(document).ready( function () {
    //     $('#myTable2').DataTable({
    //         "responsive": true,
    //     });
    // } );
</script>
@endsection
