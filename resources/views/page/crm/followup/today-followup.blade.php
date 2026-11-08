@extends('layouts.master')
@section('content')
@section('title', 'Today Followup')

@section('customer_relation_class','active')
@section('followup_class','active')
@section('today_followup_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Today Followup</h4>
                </div>
            </div>

            <div class="panel-body">
              <table class="datatable table table-striped table-bordered table-hover">
                <thead>
                     <tr>
                        <th>SL</th>
                        <th>Follow Up</th>
                        <th>Cus ID</th>
                        <th>Mobile</th>
                        <th>Name</th>
                        <th>Company Name</th>
                        <th>Reason</th>
                        <th>Type</th>
                        <th>Result</th>
                        <th>Feedback</th>
                     </tr>
                </thead>
                <tbody>
                    @php($sl=0)
                    @foreach( $todays as $today )
                     <tr>
                       <td>{{ ++$sl }}</td>
                       
                       {{-- Check if cus_mobile relationship exists and has data --}}
                       @if(isset($today->cus_mobile) && $today->cus_mobile)
                           {{-- Check for qb_staff_id --}}
                           @if( empty($today->cus_mobile->qb_staff_id) || $today->cus_mobile->qb_staff_id == 0)
                               {{-- Check if entry_info relationship exists --}}
                               <td>{{ $today->cus_mobile->entry_info['au_name'] ?? 'N/A' }}</td>
                           @else
                               {{-- Check if entry_staff relationship exists --}}
                               <td>{{ $today->cus_mobile->entry_staff['au_name'] ?? 'N/A' }}<br>( Admin Reference )</td>
                           @endif
                           
                           <td>{{ $today->cus_mobile->qb_serial ?? 'N/A' }}</td>
                         <td>
                            @if(isset($today->cus_mobile->qb_id) && isset($today->cus_mobile->qb_mobile))
                                <a href="{{ url('/client_feedback',['id' => $today->cus_mobile->qb_id]) }}"
                                   title="{{ $today->cus_mobile->qb_company_name ?? '' }}" 
                                   class="opener" target="_blank">
                                    {{ maskNumber($today->cus_mobile->qb_mobile) }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                           <td>{{ $today->cus_mobile->qb_name ?? 'N/A' }}</td>
                           <td>{{ $today->cus_mobile->qb_company_name ?? 'N/A' }}</td>
                           
                           {{-- Check if reas_info relationship exists --}}
                           <td>{{ isset($today->cus_mobile->reas_info) ? $today->cus_mobile->reas_info['sr_reason'] ?? 'N/A' : 'N/A' }}</td>
                           
                           <td>
                               @if(isset($today->cus_mobile->qb_cus_type))
                                   @if ($today->cus_mobile->qb_cus_type == 1 || $today->cus_mobile->qb_cus_type == 0)
                                       <span class="text-success">Potential</span>
                                   @elseif($today->cus_mobile->qb_cus_type == 2)
                                       <span class="text-warning">Pipeline</span>
                                   @elseif($today->cus_mobile->qb_cus_type == 3)
                                       <span class="text-primary">Regular</span>
                                   @else
                                       <span>N/A</span>
                                   @endif
                               @else
                                   N/A
                               @endif
                           </td>
                           
                           <td>
                               @if(isset($today->cf_result))
                                   @for( $i=1; $i<=$today->cf_result; ++$i )
                                       <b style="color: green; font-size: 20px;">*</b>
                                   @endfor
                               @endif
                           </td>
                       @else
                           <td colspan="10" class="text-center">No customer data available</td>
                       @endif
                       
                       {{-- Feedback column --}}
                       @if(isset($today->cus_mobile) && $today->cus_mobile)
                           <td>{{ $today->feedbackinfo['fm_msg'] ?? 'N/A' }}</td>
                       @endif
                       
                     </tr>
                    @endforeach
                </tbody>
              </table>
            </div>
        </div>
    </div>
</div>

@endsection