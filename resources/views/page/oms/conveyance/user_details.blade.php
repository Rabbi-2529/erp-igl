@extends('layouts.master')
@section('title', 'Conveyance Bill Details')

@section('oms_class','active')
@section('conveyance_class','active')
@section('conveyance_user_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Conveyance Bill Details</h4>
                </div>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
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
                    @php($debitTotal = 0)
                    @php($creditTotal = 0)
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
                        <a href="{{ url('/client_feedback',['id' => $detail->oms_what_for]) }}" title="{{ $detail->what_info_oms['qb_company_name'] }}" target="_blank">
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
                      @php($debitTotal += $detail->oms_debit)
                      @php($creditTotal += $detail->oms_credit)
                      @php($balance = $balance - $detail->oms_debit + $detail->oms_credit)
                      <td align="right">{{ number_format($balance,2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="7" align="right"><b>Total</b></td>
                        <td align="right"><b>{{ number_format($debitTotal,2) }}</b></td>
                        <td align="right"><b>{{ number_format($creditTotal,2) }}</b></td>
                        <td align="right"><b>{{ number_format($balance,2) }}</b></td>
                      </tr>
                    </tfoot>
                  </table>
            </div>
        </div>
    </div>
</div>

@endsection
