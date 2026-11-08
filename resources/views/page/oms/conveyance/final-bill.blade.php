@extends('layouts.master')
@section('title', 'Final Bill')

@section('oms_class','active')
@section('conveyance_class','active')
@section('convence_paid_bill','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Paid/Final Bill</h4>
                </div>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Date</th>
                      <th>Description</th>
                      <th>From</th>
                      <th>Mode</th>
                      <th>To</th>
                      <th>Debit/খরচ</th>
                      <th>Credit/জমা</th>
                      <th>Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @php( $balance = 0 )
                    @foreach ($paid as $bill)
                    <tr>
                      <td>{{ ++$sl }}</td>
                      @if( $bill->oms_status == 7 )
                      <td>{{ $bill->oms_transaction_date }}</td>
                      <td>Credited For {{ isset($bill->reason->name ) ? $bill->reason->name : '' }}</b></td>
                      @elseif( $bill->oms_status == 1 )
                      <td>{{ $bill->oms_insert_at }}</td>
                      <td>{{ $bill->what_info_oms['qb_company_name'] }} ({{ $bill->what_info_oms['qb_mobile'] }})
                      @endif
                      </td>
                      <td>{{ $bill->oms_dfrom }}</td>
                      @if( $bill->oms_credit == 0 )
                        <td>{{ isset($bill->mode->name ) ? $bill->mode->name : '' }}</td>
                      @else
                      <td><b style="color:green;">{{ $bill->oms_payment }}</b></td>
                      @endif
                      <td>{{ $bill->oms_dto }}</td>
                      <td align="right">{{ number_format($bill->oms_debit,2) ?? 0 }}</td>
                      <td align="right">{{ number_format($bill->oms_credit,2) ?? 0 }}</td>
                      @php( $balance = $balance - $bill->oms_debit +  $bill->oms_credit )
                      <td align="right">{{ number_format($balance,2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>


@endsection