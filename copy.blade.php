@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('followup_class','menu-open')
@section('follow_cus_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Follow Up
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"> Customers List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="example2" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th style="width: 5px !important;">SL</th>
                        <th>Cus Mobile</th>
                        <th>Cus Name</th>
                        <th>Company Name</th>
                        {{--                  <th>Mobile</th>--}}
                        <th>Location</th>
                        <th>Reason</th>
                        <th>Result</th>
                        <th>Feedback</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($i)
                    @php($sl=0)
                    @foreach( $followed_cus as $follow )
                        <tr>
                            <td>{{ ++$sl }}</td>
                            @if( ($follow->qb_mobile && $follow->qb_mobile1 && $follow->qb_mobile2) == true  )
                                <td>
                                    <a target="_blank" href="{{ url('/client_feedback',['id' => $follow->qb_id]) }}" > {{ $follow->qb_mobile }}, {{ $follow->qb_mobile1 }}, {{ $follow->qb_mobile2 }} </a>
                                </td>
                            @elseif( ($follow->qb_mobile && $follow->qb_mobile1) == true )
                                <td>
                                    <a target="_blank" href="{{ url('/client_feedback',['id' => $follow->qb_id]) }}" > {{ $follow->qb_mobile }}, {{ $follow->qb_mobile1 }} </a>

                                </td>
                            @else
                                <td>
                                    <a target="_blank" href="{{ url('/client_feedback',['id' => $follow->qb_id]) }}" > {{ $follow->qb_mobile }} </a>

                                </td>
                            @endif
                            {{--                  <td>--}}
                            {{--                      <a target="_blank" href="{{ url('/client_feedback',['id' => $follow->qb_id]) }}" class="btn  btn-sm">{{ $follow->qb_mobile }}</a>--}}
                            {{--                  </td>--}}
                            <td>{{ $follow->qb_name }}</td>
                            <td>{{ $follow->qb_company_name }}</td>

                            <td  style="width: 1rem !important;">{{ $follow->qb_address }}</td>
                            <td>{{ $follow->reas_info['sr_reason'] }}</td>
                            <td>
                                @for( $i=0; $i<$follow->qb_result; $i++ )
                                    <b style="color: green; font-size: 20px;">*</b>
                                @endfor
                            </td>
                            <td align="center">{{ isset($follow->followinfo['fm_msg']) ? $follow->followinfo['fm_msg'] : '' }}</td>

                            {{--                  <td align="center"><a href="{{ url('/client_feedback',['id' => $follow->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a></td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection

@section('custom_script')

    <script>
        $('#example2').dataTable( {
            responsive: true,
        } );

    </script>
@endsection
