@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_users','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Lunch
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User Bill Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Date</th>
                  <th>For whom</th>
                  <th>Reason </th>
                  <th>Person</th>
                  <th>Debit</th>
                  <th>Credit</th>
                  <th class="text-center">Balance</th>
                </tr>
                </thead>
                <tbody>
                @php($balance = 0)
                @php($sl=0)
                @foreach($lunch_details as $detail)
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if($detail->oms_credit == 0)
                  <td>{{ $detail->oms_bill_date }}</td>
                  <td>
                    <a href="{{ url('/client_feedback_oms',['id' => isset($detail->oms_what_for) ? $detail->oms_what_for : 0]) }}"  target="_blank">
                      {{ isset($detail->what_info_oms['qb_mobile']) ? $detail->what_info_oms['qb_mobile'] : '' }}
                    </a>
                  </td>
                  <td>{{ $detail->oms_reason }}</td>
                  @elseif($detail->oms_credit != 0)
                  <td>{{ $detail->oms_transaction_date }}</td>
                  <td> Credited For {{ $detail->oms_reason }}</td>
                  @endif
                  @if( $detail->oms_credit == 0 )
                  <td>{{ $detail->oms_staffs_user_id}}{{ $detail->oms_person?'('.$detail->oms_person.')':'' }}</td>

                  @else
                  <td><b style="color:green;">{{ $detail->oms_payment }}</b></td>
                  @endif
                  <td align="right">{{ number_format($detail->oms_debit,2) ?? 0 }}</td>
                  <td align="right">{{ number_format($detail->oms_credit,2) ?? 0 }}</td>
                  @php($balance = $balance - $detail->oms_debit + $detail->oms_credit)
                  <td align="right">{{ number_format($balance,2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                  <td colspan="7" align="right">Total</td>
                  <td align="right">{{ number_format($balance,2) }}</td>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
