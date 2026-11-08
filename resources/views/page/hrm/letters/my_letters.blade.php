@extends('layouts.master')
@section('title', 'My Letters')
@section('human_resource_class', 'active')
@section('hrm_letters_class', 'active')
@section('my_letters_class', 'active')

@section('content')
@include('page.hrm.letters._style')
@include('lc._alerts')

<div class="lt-head">
    <div>
        <h3>My Letters</h3>
        <p class="lt-sub">Letters the company has issued to you: appointment, salary and experience certificates, promotions and more.</p>
    </div>
</div>

<div class="panel panel-bd">
    <div class="panel-body">
        @if($list->isEmpty())
            <p class="text-center text-muted" style="padding: 24px 0;">No letters for you yet. When HR issues a letter to you, it appears here.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped lt-table">
                    <thead><tr><th>Reference</th><th>Date</th><th>Letter</th><th>Subject</th><th></th></tr></thead>
                    <tbody>
                        @foreach($list as $r)
                            <tr>
                                <td style="white-space: nowrap;"><span class="lt-prefix">{{ $r->hl_reference }}</span></td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($r->hl_letter_date)->format('d M Y') }}</td>
                                <td>{{ $r->hlt_name }}</td>
                                <td>{{ $r->hl_subject }}</td>
                                <td style="white-space: nowrap;"><a href="{{ route('hrm.letters.show', $r->hl_id) }}" class="btn btn-default btn-xs"><i class="fa fa-eye"></i> Open</a>
                                    <a href="{{ route('hrm.letters.print', $r->hl_id) }}" class="btn btn-default btn-xs" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
                                    <form method="POST" action="{{ route('hrm.letters.pdf', $r->hl_id) }}" style="display: inline; margin: 0;">@csrf<button type="submit" class="btn btn-default btn-xs"><i class="fa fa-file-pdf-o"></i> PDF</button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
