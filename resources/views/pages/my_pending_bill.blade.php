@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_my_pending_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Convence
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Pending Convence Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  @if( Auth::user()->au_user_type == 4 )
                  <th>Entry Date</th>
                  @endif
                  <th>Bill Date</th>
                  <th>What For</th>
                  <th>Employee</th>
                  <th>From</th>
                  <th>Mode</th>
                  <th>To</th>
                  <th>Debit/খরচ</th>
                  <th>Balance</th>
                  @if( Auth::user()->au_user_type == 4 )
                  <th class="text-center">Action</th>
                  @endif
                </tr>
                </thead>
                <tbody>
                @php($balance=0)
                @php($sl=0)
                @foreach($my_pendings as $pending)
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if( Auth::user()->au_user_type == 4 )
                  <td>{{ $pending->oms_insert_at }}</td>
                  @endif
                  <td>{{ $pending->oms_bill_date }}</td>
                  <td>
                    <a href="{{ url('/client_feedback_oms',['id' => $pending->oms_what_for]) }}" title="{{ $pending->what_info_oms['qb_company_name'] }}" class="opener" target="_blank">
                      {{ $pending->what_info_oms['qb_mobile'] }}
                    </a>
                  </td>
                  <td>{{ $pending->employee_info['au_name'] }}</td>
                  <td>{{ $pending->oms_dfrom }}</td>
                  <td>{{  isset($pending->mode->name ) ? $pending->mode->name : '' }}</td>
                  <td>{{ $pending->oms_dto }}</td>
                  <td align="right">{{ number_format($pending->oms_debit,2) }}</td>
                  @php($balance = $balance + $pending->oms_debit)
                  <td align="right">{{ number_format($balance,2) }}</td>
                  @if( Auth::user()->au_user_type == 4 )
                  <td align="center">
                    <a href="{{ route('pages.convence_pending_bill_confirm',['id' => $pending->sl_id]) }}" class="btn btn-success btn-xs" style="color: green;">
                      <span class="glyphicon glyphicon-ok"></span>
                    </a>

                    &nbsp;&nbsp;

                    <a href="{{ route('pages.convence_pending_bill_reject',['id' => $pending->sl_id]) }}" class="btn btn-danger btn-xs" style="color: red;">
                      <span class="glyphicon glyphicon-remove"></span>
                    </a>
                  </td>
                  @endif
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                @if( Auth::user()->au_user_type != 4 )
                  <td colspan="8" align="right">Total</td>
                  <td align="right">{{ number_format($balance, 2) }}</td>
                @endif
                @if( Auth::user()->au_user_type == 4)
                <td colspan="9" align="right">Total</td>
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
@endsection
