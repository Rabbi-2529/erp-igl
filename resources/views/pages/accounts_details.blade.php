@extends('layouts.master')

@section('title', 'OMS Accounts')

@section('oms_class', 'active')
@section('oms_accounts_class', 'active')

@section('content')
<section class="content">
    <div class="content-header">
        <h1>Accounts</h1>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h4>Accounts Details</h4>
                    </div>
                </div>

                <div class="panel-body">
                    <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Date</th>
                                <th>Description</th>
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
                            @php($sl = 0)
                            @foreach($details as $detail)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>
                                    @if($detail->oms_credit == 0)
                                        {{ $detail->oms_bill_date }}
                                    @elseif($detail->oms_credit != 0)
                                        {{ $detail->oms_transaction_date }}
                                    @endif
                                </td>
                                <td>
                                    @if($detail->oms_credit == 0)
                                        <a href="{{ url('/client_feedback_oms',['id' => $detail->oms_what_for]) }}" title="{{ @$detail->what_info_oms['qb_company_name'] }}" target="_blank">
                                            {{ @$detail->what_info_oms['qb_mobile'] }}
                                        </a>
                                    @elseif($detail->oms_credit != 0)
                                        Credited For {{ $detail->oms_reason }}
                                    @endif
                                </td>
                                <td>{{ $detail->oms_dfrom }}</td>
                             <td>
    @if($detail->oms_credit == 0)
        {{ $detail->mode ? $detail->mode->name : '' }} <!-- Display the mode name if it exists -->
    @else
        <b style="color: green;">{{ $detail->oms_payment }}</b>
    @endif
</td>
                                <td>{{ $detail->oms_dto }}</td>
                                <td align="right">{{ number_format($detail->oms_debit, 2) ?? 0 }}</td>
                                <td align="right">{{ number_format($detail->oms_credit, 2) ?? 0 }}</td>
                                @php($balance = $balance - $detail->oms_debit + $detail->oms_credit)
                                <td align="right">{{ number_format($balance, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" align="right"><b>Total</b></td>
                                <td align="right"><b>{{ number_format($balance, 2) }}</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->
@endsection
