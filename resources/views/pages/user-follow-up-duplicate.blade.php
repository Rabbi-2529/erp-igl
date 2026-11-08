@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('user_follow_up','active')
@section('content')
<section class="content">
          <section class="content-header">
		      <h1>
		        Duplicate Follow Up Given Today By {{ $user->au_name }}
		      </h1>
		  </section>
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Today ({{ date('d-m-Y') }})</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Mobile</th>
                  <th>Result</th>
                  <th>Type</th>
                  <th>Feedback</th>
                  <th>Next Date</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach( $given_fu as $given )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>
                    <a href="{{ url('/client_feedback',['id' => $given->cf_qb_id]) }}" target="_blank" style="text-decoration: none;">
                      {{ $given->cus_mobile['qb_mobile'] }}
                    </a>
                  </td>
                  <td>
                    @for( $i = 1; $i<=$given->cf_result; ++$i )
                    <b style="color: green; font-size: 20px;">*</b>
                    @endfor
                  </td>
                  <td>
                      @if ($given->cus_mobile['qb_cus_type'] == 1 || $given->cus_mobile['qb_cus_type'] == 0)
                        <span class="text-success">Potential</span>
                     @elseif($given->cus_mobile['qb_cus_type'] == 2)
                        <span class="text-warning">Pipeline</span>
                     @elseif($given->cus_mobile['qb_cus_type'] == 3)
                        <span class="text-primary">Regular</span>
                     @endif
                  </td>
                  <td>{{ $given->feedbackinfo['fm_msg'] }}</td>
                  <td>{{ $given->cf_next_date }}</td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
