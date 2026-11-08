@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_reject_bill','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Meal
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Reject Meal Bill</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="datatable" class="table table-bordered table-striped">
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
                  <td colspan="6" align="right">Total:</td>
                  <td></td>
                  <td align="right">{{ number_format($balance,2) }}</td>
                  @elseif( Auth::user()->au_user_type != 4 )
                  <td colspan="7" align="right">Total</td>
                  <td></td>
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
    <script>
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
                    $(api.column(6).footer()).html( total);
                },
            })

        })
    </script>

@endsection
