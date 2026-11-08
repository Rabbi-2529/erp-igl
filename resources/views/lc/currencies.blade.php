@extends('layouts.master')
@section('title','LC Currencies')
@section('lc_class','active')
@section('lc_settings_class','active')
@section('content')
<section class="content">
    <section class="content-header">
        @include('lc._alerts')
        <h1>LC Currency Settings</h1>
    </section>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title"><h3 class="box-title">Currencies</h3></div>
                </div>
                <div class="panel-body">
                    <p class="text-muted">
                        Each import file picks one of these currencies. The rate (to BDT) is filled in on the file and can be changed there.
                    </p>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th width="160">Rate to BDT</th>
                                <th width="120">Status</th>
                                <th width="90"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currencies as $currency)
                            @php($formId = 'currency_form_' . $currency->lcc_id)
                            <tr>
                                <td><b>{{ $currency->lcc_code }}</b></td>
                                <td><input type="text" name="name" form="{{ $formId }}" class="form-control input-sm" value="{{ $currency->lcc_name }}" maxlength="50"></td>
                                <td>
                                    <input type="number" step="0.0001" min="0.0001" name="rate" form="{{ $formId }}" class="form-control input-sm text-right"
                                           value="{{ $currency->lcc_rate + 0 }}" {{ $currency->lcc_code === 'BDT' ? 'readonly' : '' }} required>
                                </td>
                                <td>
                                    @if($currency->lcc_code === 'BDT')
                                        <span class="label label-success">Always active</span>
                                        <input type="hidden" name="status" form="{{ $formId }}" value="1">
                                    @else
                                        <select name="status" form="{{ $formId }}" class="form-control input-sm dont-select-me">
                                            <option value="1" {{ $currency->lcc_status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $currency->lcc_status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    {{-- Inputs in this row join the form through their form="" attribute --}}
                                    <form id="{{ $formId }}" action="{{ route('lc.currencies.update', $currency->lcc_id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-save"></i> Save</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-bd lobidrag">
                <div class="panel-heading">
                    <div class="panel-title"><h3 class="box-title">Add Currency</h3></div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('lc.currencies.store') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="code">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control" maxlength="3" placeholder="e.g. EUR"
                                   value="{{ old('code') }}" style="text-transform: uppercase;" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" maxlength="50" placeholder="e.g. Euro" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="rate">Rate to BDT <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0.0001" name="rate" id="rate" class="form-control" placeholder="e.g. 130" value="{{ old('rate') }}" required>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add Currency</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
