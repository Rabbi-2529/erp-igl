@extends('layouts.master')
@section('title','LC Debitors Balance')
@section('lc_class','active')
@section('lc_debitors_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>Debitors Balance <small>LC payable per supplier</small></h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title"><h3 class="box-title">Suppliers</h3></div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Supplier / LC</th>
                        <th class="text-right">LC Value (BDT)</th>
                        <th class="text-right">Paid for Goods</th>
                        <th class="text-right">Payable</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr style="background: #f4f6f8;">
                        <td><b>{{ $supplier->company }}</b> <small class="text-muted">{{ $supplier->name }} &middot; {{ $supplier->lcs->count() }} LC(s)</small></td>
                        <td class="text-right"><b>{{ number_format($supplier->value, 2) }}</b></td>
                        <td class="text-right"><b>{{ number_format($supplier->paid, 2) }}</b></td>
                        <td class="text-right"><b class="{{ $supplier->payable > 0 ? 'text-danger' : '' }}">{{ number_format($supplier->payable, 2) }}</b></td>
                        <td></td>
                    </tr>
                    @foreach($supplier->lcs as $lc)
                    <tr>
                        <td style="padding-left: 30px;">LC {{ $lc->lc_no }} <small class="text-muted">({{ \Carbon\Carbon::parse($lc->lc_date)->format('d M Y') }}, file {{ $lc->file->lcf_file_no }})</small></td>
                        <td class="text-right">{{ number_format($lc->lc_value_bdt, 2) }}</td>
                        <td class="text-right">{{ number_format($lc->goodsPaid(), 2) }}</td>
                        <td class="text-right">{{ number_format($lc->payable(), 2) }}</td>
                        <td><a href="{{ route('lc.statement', ['lc' => $lc->lc_id]) }}" class="btn btn-info btn-xs"><i class="fa fa-list"></i> Statement</a></td>
                    </tr>
                    @endforeach
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">No LCs yet.</td></tr>
                    @endforelse
                </tbody>
                @if($suppliers->isNotEmpty())
                <tfoot>
                    <tr>
                        <th class="text-right">Total</th>
                        <th class="text-right">{{ number_format($suppliers->sum('value'), 2) }}</th>
                        <th class="text-right">{{ number_format($suppliers->sum('paid'), 2) }}</th>
                        <th class="text-right">{{ number_format($suppliers->sum('payable'), 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
            <small class="text-muted">Payable = LC value in BDT minus the margin and retirement payments recorded on the LC Statement.</small>
        </div>
    </div>
</section>
@endsection
