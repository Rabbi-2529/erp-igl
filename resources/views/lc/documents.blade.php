@extends('layouts.master')
@section('title','LC Documents')
@section('lc_class','active')
@section('lcm_class','active')
@section('lc_documents_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>LC Documents</h1>
    </section>

    <form method="get" action="{{ route('lc.documents') }}" class="form-inline" style="margin-bottom: 15px;">
        <label for="lc_pick">LC&nbsp;</label>
        <select name="lc" id="lc_pick" class="form-control" style="min-width: 380px;" onchange="this.form.submit()">
            <option value="">Select LC</option>
            @foreach($lcs as $item)
            <option value="{{ $item->lc_id }}" {{ $lc && $lc->lc_id == $item->lc_id ? 'selected' : '' }}>{{ $item->label }}</option>
            @endforeach
        </select>
    </form>

    @if($lc)
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3 class="box-title">
                            Documents of LC {{ $lc->lc_no }}
                            <a href="{{ route('lc.statement', ['lc' => $lc->lc_id]) }}" class="btn btn-default btn-sm pull-right"><i class="fa fa-list"></i> LC Statement</a>
                        </h3>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>Type</th><th>Title</th><th>File</th><th>Uploaded</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $document)
                            <tr>
                                <td><span class="label label-info">{{ $document->lcd_type }}</span></td>
                                <td>{{ $document->lcd_title }}</td>
                                <td>{{ $document->lcd_original_name }}</td>
                                <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($document->lcd_submit_at)->format('d M Y, h:i A') }}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{ route('lc.documents.download', $document->lcd_id) }}" class="btn btn-info btn-xs"><i class="fa fa-download"></i> Download</a>
                                    <form action="{{ route('lc.documents.delete', $document->lcd_id) }}" method="post" style="display:inline-block;" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title"><h3 class="box-title">Upload Document</h3></div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('lc.documents.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="lc_id" value="{{ $lc->lc_id }}">
                        <div class="form-group">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control dont-select-me" required>
                                @foreach($types as $type)
                                <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" maxlength="150" value="{{ old('title') }}" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label for="file">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                            <small class="text-muted">PDF, image, Word or Excel, up to 10 MB.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-upload"></i> Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection
