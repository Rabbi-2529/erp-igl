@extends('layouts.master')
@section('title', 'Non Follow-Up')


@section('customer_relation_class','active')
@section('followup_class','active')

@section('nonfollow_cus_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Non Follow-Up Customers List</h4>
                </div>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      <th>Cus Mobile</th>
                      <th>Cus Name</th>
                      <th>Company Name</th>
                      <th>Location</th>
                      <th>Reason</th>
                      <th>Result</th>
                    </tr>
                    </thead>
                    <tbody>
                      @php($i)
                      @php($sl=0)
                      @foreach( $non_followed_cus as $follow )
                      <tr>
                        <td>{{ ++$sl }}</td>
                      @if($follow->qb_mobile && $follow->qb_mobile1 && $follow->qb_mobile2)
                            <td>
                                <a target="_blank" href="{{ url('/client_feedback', ['id' => $follow->qb_id]) }}">
                                    {{ maskNumber($follow->qb_mobile) }}, {{ maskNumber($follow->qb_mobile1) }}, {{ maskNumber($follow->qb_mobile2) }}
                                </a>
                            </td>
                        @elseif($follow->qb_mobile && $follow->qb_mobile1)
                            <td>
                                <a target="_blank" href="{{ url('/client_feedback', ['id' => $follow->qb_id]) }}">
                                    {{ maskNumber($follow->qb_mobile) }}, {{ maskNumber($follow->qb_mobile1) }}
                                </a>
                            </td>
                        @else
                            <td>
                                <a target="_blank" href="{{ url('/client_feedback', ['id' => $follow->qb_id]) }}">
                                    {{ maskNumber($follow->qb_mobile) }}
                                </a>
                            </td>
                        @endif
                        <td>{{ $follow->qb_name }}</td>
                        <td>{{ $follow->qb_company_name }}</td>
                        <td>{{ $follow->qb_address }}</td>
                        <td>{{ $follow->reas_info['sr_reason'] ?? 'N/A' }}</td>
                        <td>
                          @for( $i=0; $i<$follow->qb_result; $i++ )
                          <b style="color: green; font-size: 20px;">*</b>
                          @endfor
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>


@endsection