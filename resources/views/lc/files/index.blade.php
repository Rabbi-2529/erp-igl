@extends('layouts.master')
@section('title','LC Files')
@section('lc_class','active')
@section('lc_file_class','active')
@section('lc_file_list_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>File Opening Details</h1>
    </section>

    <div class="panel panel-bd lobidrag">
        <div class="panel-heading">
            <div class="panel-title">
                <h3 class="box-title">
                    Import Files
                    <a href="{{ route('lc.files.create') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> Add File</a>
                </h3>
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>File No</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>PI No</th>
                        <th class="text-right">PI Value</th>
                        <th class="text-right">BDT</th>
                        <th>LC No</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                    @php
                        $ordered = $file->items->sum('lcfi_qty');
                        $received = $file->items->sum('lcfi_received_qty');
                    @endphp
                    <tr>
                        <td><b>{{ $file->lcf_file_no }}</b></td>
                        <td data-order="{{ $file->lcf_file_date }}">{{ \Carbon\Carbon::parse($file->lcf_file_date)->format('d M Y') }}</td>
                        <td>{{ $file->supplier->inv_sup_com_name ?? 'N/A' }}</td>
                        <td>{{ $file->lcf_pi_no }}</td>
                        <td class="text-right">{{ $file->lcf_currency }} {{ number_format($file->lcf_total_fc, 2) }}</td>
                        <td class="text-right">{{ number_format($file->lcf_total_bdt, 2) }}</td>
                        <td>{{ $file->lc->lc_no ?? '' }}</td>
                        <td>{{ $received + 0 }} / {{ $ordered + 0 }}</td>
                        <td>
                            <span class="label {{ $file->lcf_status == 2 ? 'label-success' : ($file->lcf_status == 1 ? 'label-info' : 'label-default') }}">{{ $file->status_label }}</span>
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
                            <a href="{{ route('lc.files.show', $file->lcf_id) }}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a>
                            @if($file->lcf_status == 1)
                            <a href="{{ route('lc.opening', ['file' => $file->lcf_id]) }}" class="btn btn-success btn-xs"><i class="fa fa-university"></i> Open LC</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
