@extends('layout.master')
@section('content')
<section class="content">
          <section class="content-header">
		      <h1>
		        Follow Up
		      </h1>
		  </section>
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Todays Created Customers List ({{ date('d-m-Y') }})</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>Customer ID</th>
                  <th>Mobile</th>
                  <th>Company Name</th>
                  <th>Reason</th>
                  <th>Result</th>
                  <th>Type</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @foreach( $tc_list as $list )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $list->qb_serial }}</td>
                  <td>{{ $list->qb_mobile }}</td>
                  <td>{{ $list->qb_company_name }}</td>
                  <td>{{ $list->reas_info['sr_reason'] ?? 'N/A' }}</td>
                  <td>
                    @for( $i=1; $i<=$list->qb_result; ++$i )
                      <b style="color: green; font-size: 20px;">*</b>
                    @endfor
                  </td>
                  <td>
                      @if ($list->qb_cus_type == 1 || $list->qb_cus_type == 0)
                        <span class="text-success">Potential</span>
                     @elseif($list->qb_cus_type == 2)
                        <span class="text-warning">Pipeline</span>
                     @elseif($list->qb_cus_type == 3)
                        <span class="text-primary">Regular</span>
                     @endif
                  </td>
                  <td>
                    <a href="{{ url('/client_feedback',['id' => $list->qb_id]) }}" class="btn btn-info btn-sm">Follow Up
                    </a>
                  </td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
