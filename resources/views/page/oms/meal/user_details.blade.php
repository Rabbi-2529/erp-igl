@extends('layouts.master')
@section('title', 'Conveyance User')

@section('oms_class','active')
@section('meal_class','active')
@section('meal_user_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Conveyance User (Meal)</h4>
                    @if(count($lunch_details) > 0)
    @php
        $firstEntry = $lunch_details[0];
        $staffName = optional($firstEntry->employee_info)->au_name ?? '';
        $staffUserId = optional($firstEntry->employee_info)->au_user_id ?? '';
    @endphp
    <div class="alert alert-info">
        <strong>Staff Name:</strong> {{ $staffName }} <br>
        <strong>Staff ID:</strong> {{ $staffUserId }}
    </div>
@endif
                </div>
            </div>

            <div class="panel-body">
                <table id="datatable" class="table table-bordered table-striped table-hover datatable">
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
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $balance = 0;
                        $sl=0;
                    @endphp
                    @foreach($lunch_details as $detail)
                    <tr>
                      <td>{{ ++$sl }}</td>
                      @if($detail->oms_credit == 0)
                      <td>{{ $detail->oms_bill_date }}</td>
                      <td>
                        <a href="{{ url('/client_feedback',['id' => isset($detail->oms_what_for) ? $detail->oms_what_for : 0]) }}"  target="_blank">
                          {{ isset($detail->what_info_oms['qb_mobile']) ? $detail->what_info_oms['qb_mobile'] : '' }}
                        </a>
                      </td>
                      <td>{{ $detail->oms_reason }}</td>
                      @elseif($detail->oms_credit != 0)
                      <td>{{ $detail->oms_transaction_date }}</td>
                      <td>=</td>
                      <td> Credited For {{ $detail->oms_reason }}</td>
                      
                      @endif
                      @if( $detail->oms_credit == 0 )
                        <td>{{ $detail->oms_staffs_user_id}}{{ $detail->oms_person?'('.$detail->oms_person.')':'' }}</td>
                      @else
                        <td><b style="color:green;">{{ $detail->oms_payment }}</b></td>
                      @endif
                      <td align="right">{{ number_format($detail->oms_debit,2) ?? 0 }}</td>
                      <td align="right">{{ number_format($detail->oms_credit,2) ?? 0 }}</td>
                      @php
                        $totalDebit += $detail->oms_debit;
                        $totalCredit += $detail->oms_credit;
                        $balance = $totalCredit - $totalDebit;
                      @endphp
                      <td align="right">{{ number_format($balance,2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                      <td colspan="5" align="right"><strong>Total</strong></td>
                      <td align="right"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                      <td align="right"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                      <td align="right"><strong>{{ number_format($balance, 2) }}</strong></td>
                    </tfoot>
                  </table>
            </div>
        </div>
    </div>
</div>

@endsection
