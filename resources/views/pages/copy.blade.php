@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('customer_class','menu-open')
@section('my_customer_list_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Customer List
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">The User's Customer</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>CusID</th>
                        <th>Mobile</th>
                        <th>Name</th>
                        <th>Com. Name</th>
                        <th>Location</th>
                        <th>Reason</th>
                        <th>Result</th>
                        {{--                        <th class="text-center">Action</th>--}}
                    </tr>
                    </thead>
                    <tbody>
                    @php($i)
                    @php($sl=0)
                    @foreach( $my_customers as $my_customer )
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $my_customer->qb_serial }}</td>
                            <td><a target="_blank" href="{{ url('/client_feedback',['id' => $my_customer->qb_id]) }}"> {{ $my_customer->qb_mobile }}</a></td>
                            <td>{{ $my_customer->qb_name }}</td>
                            <td>{{ $my_customer->qb_company_name }}</td>
                            <td>{{ $my_customer->qb_address }}</td>
                            <td>{{ $my_customer->reas_info['sr_reason'] ?? 'N/A' }}</td>
                            <td>
                                @for( $i = 1; $i <= $my_customer->qb_result; ++$i)
                                    <b style="color: green; font-size: 20px;">*</b>
                                @endfor
                            </td>
                            {{--                            <td align="center">--}}
                            {{--                                <a href="{{ url('/client_feedback',['id' => $my_customer->qb_id]) }}" class="btn btn-info btn-sm opener" target="_blank">--}}
                            {{--                                    Follow Up--}}
                            {{--                                </a>--}}
                            {{--                            </td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{--                {{ $my_customers->links('pagination.default') }}--}}


            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@section('custom_script')
    <script type="text/javascript">
        window.onload = function(){
            let a = document.querySelectorAll('.opener'), w = [], url, random, i;
            for(i = 0; i < a.length; i++){
                (function(i){
                    a[i].onclick = function(e) {
                        if (!w[i] || w[i].closed) {
                            url = this.href;
                            random = Math.floor((Math.random() * 100) + 1);
                            w[i] = window.open(url);
                        } else {
                            console.log('window ' + url + ' is already opened');
                        }
                        e.preventDefault();
                        w[i].focus();
                    };
                })(i);
            }
        };
    </script>
@endsection
