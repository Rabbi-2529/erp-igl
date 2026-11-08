@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_paid_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Lunch
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Paid/Final Lunch Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Date</th>
                  <th>For whom</th>
                  <th>Reason</th>
                  <th>Person</th>
                  <th>Credit</th>
                  <th>Debit/খরচ</th>
                  <th>Balance</th>
                </tr>
                </thead>
                <tbody>
                @php( $balance =0 )
                @php($sl=0)
                @foreach( $lunch_pays as $paid )
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if( $paid->oms_status == 8 )
                  <td>{{ $paid->oms_transaction_date }}</td>
                  @elseif( $paid->oms_status == 2 )
                  <td>{{ $paid->oms_bill_date }}</td>
                  @endif
                  <td>
                    <a href="{{ url('/client_feedback_oms',['id' => $paid->oms_what_for]) }}" title="{{ $paid->oms_what_for == '' ? '' : $paid->what_info_oms['qb_company_name'] }}">
                      {{ $paid->oms_what_for == '' ? '' : $paid->what_info_oms['qb_mobile'] }}
                    </a>
                  </td>
                  @if( !empty( $paid->oms_credit ) )
                  <td>Credited By <b style="color:green;">{{ $paid->oms_payment }}</b></td>
                  @else
                  <td>{{ $paid->oms_reason }}</td>
                  @endif
                    <td>
                        @if($paid->oms_reason == "1")
                            Visit
                        @elseif($paid->oms_reason == "2")
                            Setup
                        @elseif($paid->oms_reason == "3")
                            Product Delivery
                        @elseif($paid->oms_reason == "4")
                            Support
                        @elseif($paid->oms_reason == "5")
                            Other
                        @elseif($paid->oms_reason == "6")
                            Collection
                        @endif

                        @if($paid->menu === 1 )
                            (Breakfast)
                        @elseif($paid->menu === 2 )
                            (Lunch)
                        @elseif($paid->menu === 3 )
                            (Dinner)
                        @endif
                    </td>
                  <td>{{ $paid->oms_staffs_user_id}} {{$paid->oms_person?'('.$paid->oms_person.')':'' }}</td>
                  <td align="right">{{ number_format($paid->oms_credit,2) ?? 0 }}</td>
                  <td align="right">{{ number_format($paid->oms_debit,2) ?? 0 }}</td>
                  @php( $balance =  $balance - $paid->oms_credit + $paid->oms_debit)
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
