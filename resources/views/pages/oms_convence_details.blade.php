@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_users','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Convence
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User Bill Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="myTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Project</th>
                  <th>From</th>
                  <th>Mode</th>
                  <th>To</th>
                  <th>Debit</th>
                  <th>Credit</th>
                  <th class="text-center">Balance</th>
                </tr>
                </thead>
                <tbody>
                @php($balance = 0)
                @php($sl=0)
                @foreach($details as $detail)
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if($detail->oms_credit == 0)
                  <td>{{ $detail->oms_bill_date }}</td>
                  @elseif($detail->oms_credit != 0)
                  <td>{{ $detail->oms_transaction_date }}</td>
                  @endif
                  @if($detail->oms_credit == 0)
                  <td>
                    <a href="{{ url('/client_feedback_oms',['id' => $detail->oms_what_for]) }}" title="{{ $detail->what_info_oms['qb_company_name'] }}" target="_blank">
                      {{ $detail->what_info_oms['qb_mobile'] }}
                    </a>
                  </td>
                  @elseif($detail->oms_credit != 0)
                  <td>Credited For {{ $detail->oms_reason }}</td>
                  @endif
                  <td>{{ isset($detail->projects->project_name)?$detail->projects->project_name:''}}</td>
                  <td>{{ $detail->oms_dfrom }}</td>
                  @if( $detail->oms_credit == 0 )
                  <td>{{ $detail->oms_mode }}</td>
                  @else
                  <td><b style="color: green;">{{ $detail->oms_payment }}</b></td>
                  @endif
                  <td>{{ $detail->oms_dto }}</td>
                  <td align="right">{{ number_format($detail->oms_debit,2) ?? 0 }}</td>
                  <td align="right">{{ number_format($detail->oms_credit,2) ?? 0 }}</td>
                  @php($balance = $balance - $detail->oms_debit + $detail->oms_credit)
                  <td align="right">{{ number_format($balance,2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="8" align="right">Total</td>
                    <td align="right">{{ number_format($balance,2) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
    <script type="text/javascript">
        $(document).ready( function () {
            $('#myTable').DataTable();
        } );
    </script>
@endsection
