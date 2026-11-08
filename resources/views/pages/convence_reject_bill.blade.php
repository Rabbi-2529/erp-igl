@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_reject_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Conveyance
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Rejected Conveyance Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="myTable2" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  @if(Auth::user()->au_user_type == 4)
                  <th>Entry Date</th>
                  @endif
                  <th>Bill Date</th>
                  <th>Reason</th>
                  <th>Employee</th>
                  <th>What For</th>
                  <th>From</th>
                  <th>Mode</th>
                  <th>To</th>
                  <th>Debit/খরচ</th>
                  <th>Balance</th>
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
                  <td>{{ isset($reject->reason->name ) ? $reject->reason->name : '' }}</td>
                  <td>{{ $reject->employee_info['au_name'] }} {{$reject->oms_staffs_user_id ? $reject->oms_staffs_user_id : '' }}</td>
                  <td>

                    <a href="{{ url('/client_feedback_oms',['id' => $reject->oms_what_for]) }}" title="{{ $reject->what_info != '' ? $reject->what_info['qb_company_name'] : '' }}" class="opener" target="_blank">
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
                  @if( Auth::user()->au_user_type != 4 )
                  <td align="center"><a href="{{ url('/oms_update_bill',['id' => $reject->sl_id]) }}" class="btn btn-info btn-sm">Edit</a></td>
                  @endif
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                  @if( Auth::user()->au_user_type == 4 )
                  <td colspan="9" align="right">Total: </td>
                  <td></td>
                  <td align="right" >{{ number_format($balance,2) }}</td>
                  @elseif(  Auth::user()->au_user_type != 4  )
                  <td colspan="8" align="right">Total</td>
                  <td align="right" >{{ number_format($balance,2) }}</td>
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
    // $(document).ready( function () {
    //     $('#myTable2').DataTable({
    //         "responsive": true,
    //     });
    // } );
    $(document).ready(function() {
        $('#myTable2').dataTable({
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
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(9, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                // $(api.column(4).footer()).html('$' + pageTotal + ' ( $' + total + ' total)');
                $(api.column(9).footer()).html( total);
            },
        })

    })
</script>
@endsection
