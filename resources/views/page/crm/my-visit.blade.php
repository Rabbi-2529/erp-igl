@extends('layouts.master')
@section('content')
@section('title', 'My Visit')

@section('customer_relation_class','active')
@section('visit_class','active')
@section('my_visit_class','active')

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4> My Sales Visit</h4>
                </div>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Follow-up By</th>
                            <th>Visit Date</th>
                            <th>Reason</th>
                            <th>Client Phone</th>
                            <th>Address</th>
                            <th style="width: 50px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sl = 0;
                        @endphp
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection
