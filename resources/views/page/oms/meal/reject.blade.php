@extends('layouts.master')
@section('title', 'Rejected Bill')

@section('oms_class','active')
@section('meal_class','active')
@section('meal_reject_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Rejected Bill (Meal)</h4>
                </div>
            </div>

            <div class="panel-body">
                <table id="datatable" class="table table-bordered table-striped table-hover datatable">
                    <thead>
                    <tr>
                      <th>SL</th>
                      @if( Auth::user()->au_user_type == 4 )
                      <th>Entry Date</th>
                        @else
                          <th></th>
                      @endif
                      <th>Bill Date</th>
                      <th>For whom</th>
                      <th>Employee</th>
                      <th>Reason</th>
                      <th>Person</th>
                      <th>Debit/খরচ</th>
                      @if( Auth::user()->au_user_type != 4 )
                      <th class="text-center">Action</th>
                      @endif
                    </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @php($balance=0)
                    @foreach($lunch_rejects as $lunch_reject)
                    <tr>
                      <td>{{ ++$sl }}</td>
                      @if( Auth::user()->au_user_type == 4 )
                      <td>{{ $lunch_reject->oms_insert_at }}</td>
                        @else
                          <td></td>
                        @endif
                      <td>{{ $lunch_reject->oms_bill_date }}</td>
                       {{--  solve it  --}}
                      <td>{{   $lunch_reject->oms_what_for }}</td>
                      <td>{{ $lunch_reject->employee_info['au_name'] }}</td>
                      <td>
                          @if($lunch_reject->oms_reason === "1")
                              CCTV Setup
                          @elseif($lunch_reject->oms_reason === "2")
                              CCTV Service
                          @elseif($lunch_reject->oms_reason === "3")
                              IT Support
                          @elseif($lunch_reject->oms_reason === "4")
                              Visit
                          @elseif($lunch_reject->oms_reason === "5")
                              Other
                          @endif
    
                          @if($lunch_reject->menu === 1 )
                              (Breakfast)
                          @elseif($lunch_reject->menu === 2 )
                              (Lunch)
                          @elseif($lunch_reject->menu === 3 )
                              (Dinner)
                          @endif
                      </td>
                      <td>{{ $lunch_reject->oms_staffs_user_id}} {{$lunch_reject->oms_person?'('.$lunch_reject->oms_person.')':'' }}</td>
                      <td align="right">{{ number_format($lunch_reject->oms_debit,2) }}</td>
                      @if( Auth::user()->au_user_type != 4 )
                      <td align="center">
                          <a href="{{ url('/oms_lunch_update_bill',['id' => $lunch_reject->sl_id]) }}" class="btn btn-info btn-sm">Edit</a>
                      </td>
                      @endif
                    </tr>
                    @php($balance = $balance + $lunch_reject->oms_debit)
                    @endforeach
                    </tbody>
                    <tfoot>
                      @if( Auth::user()->au_user_type == 4 )
                      <td colspan="7" align="right"><strong>Total:</strong></td>
                      <td align="right"><strong>{{ number_format($balance,2) }}</strong></td>
                      @elseif( Auth::user()->au_user_type != 4 )
                      <td colspan="8" align="right"><strong>Total:</strong></td>
                      <td align="right"><strong>{{ number_format($balance,2) }}</strong></td>
                      <td></td>
                      @endif
    
    
                    </tfoot>
                  </table>
            </div>
        </div>
    </div>
</div>


@endsection