@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_pending_bill','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Meal
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Pending Meal Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table id="datatable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>SL</th>
                        @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            <th>Entry Date</th>
                        @endif

                        <th>Bill Date</th>
                        <th>For whom</th>
                        <th>Reason</th>
                        <th>Employee</th>
                        <th>Person</th>
                        <th>Debit/খরচ</th>
                        <th>Balance</th>
                        @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                            <th class="text-center">Action</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @php($sl=0)
                    @php($balance = 0)
                    @php($hasPermission = explode('-', Auth::user()->au_permission_status))
                    @foreach($lunch_bills as $lunch_bill)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                                <td>{{ $lunch_bill->oms_insert_at }}</td>
                            @endif
                            <td>{{ $lunch_bill->oms_bill_date }}</td>
                            {{--  solve it  --}}
                            <td>
                                <a style=" color:#337ab7!important;"
                                   href="{{ url('/client_feedback_oms',['id' => $lunch_bill->oms_what_for]) }}" title=""
                                   class="opener" target="_blank">
                                    {{ isset($lunch_bill->what_info['qb_mobile'] )?$lunch_bill->what_info['qb_mobile'] :'' }}
                                </a>

                            </td>
                            <td>
                                @if($lunch_bill->oms_reason == "1")
                                    Visit
                                @elseif($lunch_bill->oms_reason == "2")
                                    Setup
                                @elseif($lunch_bill->oms_reason == "3")
                                    Product Delivery
                                @elseif($lunch_bill->oms_reason == "4")
                                    Support
                                @elseif($lunch_bill->oms_reason == "5")
                                    Other
                                @elseif($lunch_bill->oms_reason == "6")
                                    Collection
                                @endif

                                @if($lunch_bill->menu == 1 )
                                    (Breakfast)
                                @elseif($lunch_bill->menu == 2 )
                                    (Lunch)
                                @elseif($lunch_bill->menu == 3 )
                                    (Dinner)
                                @endif
                            </td>
                            <td>{{ $lunch_bill->employee_info['au_name'] }}</td>
                            <td>{{ $lunch_bill->oms_staffs_user_id}} {{$lunch_bill->oms_person?'('.$lunch_bill->oms_person.')':'' }}</td>
                            <td align="right">{{ number_format($lunch_bill->oms_debit,2) }}</td>
                            @php($balance = $balance + $lunch_bill->oms_debit)
                            <td align="right">{{ number_format($balance,2) }}</td>
                            @if(Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5 && in_array(92, $hasPermission))
                                <td align="center">
                                    <a href="{{ route('pages.lunch_pending_bill_confirm',['id' => $lunch_bill->sl_id]) }}"
                                       class="btn btn-success btn-xs" style="color: green;">
                                        <span style="color: white" class="glyphicon glyphicon-ok"></span>
                                    </a>
                                    &nbsp;&nbsp;
                                    <a href="{{ route('pages.lunch_pending_bill_reject',['id' => $lunch_bill->sl_id]) }}"
                                       class="btn btn-danger btn-xs" style="color: red;">
                                        <span style="color: white" class="glyphicon glyphicon-remove"></span>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    </tbody>
                    <tfoot>
                    @if( Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 5)
                        <td colspan="7" align="right">Total:</td>
                        <td></td>
                        <td align="right">{{ number_format($balance,2) }}</td>
                        <td></td>
                    @else
                        <td colspan="3">Total:</td>
                        <td></td>
                        <td></td>
                        <td>Total:</td>

                        <td align="right">{{ number_format($balance,2) }}</td>
                        <td></td>
                    @endif
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@section('custom_script')
    <script type="text/javascript">
        window.onload = function () {
            var a = document.querySelectorAll('.opener'), w = [], url, random, i;
            for (i = 0; i < a.length; i++) {
                (function (i) {
                    a[i].onclick = function (e) {
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
        $(document).ready(function() {
            $('#datatable').dataTable({
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                    };

                    // Total over all pages
                    total = api
                        .column(7, {filter: 'applied'})
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Total over this page
                    pageTotal = api
                        .column(7, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    // $(api.column(4).footer()).html('$' + pageTotal + ' ( $' + total + ' total)');
                    $(api.column(7).footer()).html( total);
                },
            })

        })
    </script>
@endsection
