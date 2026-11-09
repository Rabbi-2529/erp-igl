@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('customer_class','menu-open')
@section('total_customer_list_class','active')
@section('content')
    <section class="content">
        <section class="content-header">
            <h1>
                Customer List
            </h1>
        </section>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Total Customers List</h3>



            </div>
            <!-- /.box-header -->



            <div class="mt-5">
                <a href="{{ route('total_customer_list.export') }}" class="btn btn-primary">
                    Export Data
                </a>

            </div>
            <div class="box-body">
                <table id="myTable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Cus Name</th>
                        <th>Com. Name</th>

                        {{--  error  --}}
                        <th>Mobile</th>
                        <th>Location</th>
                        <th>Reason</th>
                        <th>Result</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php($i)
                    @php($sl=0)

                    {{--  @foreach($customers as $customer)


                          <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $customer->qb_serial }}</td>
                            <td>{{ $customer->qb_name }}</td>
                            <td>{{ $customer->qb_company_name }}</td>
                            <td>
                                  @if ($customer->qb_mobile != NULL && $customer->qb_mobile1 != NULL && $customer->qb_mobile2 != NULL)
                                      {{ $customer->qb_mobile }},{{ $customer->qb_mobile1 }},{{ $customer->qb_mobile2 }}
                                  @elseif($customer->qb_mobile != NULL && $customer->qb_mobile1 != NULL)
                                      {{ $customer->qb_mobile }},{{ $customer->qb_mobile1 }}
                                  @else
                                      {{ $customer->qb_mobile }}
                                  @endif
                            </td>
                            <td>{{ $customer->qb_address }}</td>
                            <td>{{ $customer->reas_info['sr_reason'] ?? 'N/A' }}</td>
                            <td>
                              @for( $i=1; $i<=$customer->qb_result; ++$i )
                                <b style="color: green; font-size: 20px;">*</b>
                              @endfor
                            </td>
                            <td align="center">
                                <a href="{{ url('/client_feedback',['id' => $customer->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a>
                            </td>
                          </tr>
                          @endforeach  --}}


                    </tbody>
                </table>


            </div>
            <!-- /.box-body -->
        </div>
    </section>
@endsection
@section('custom_style')




    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
@endsection
@section('custom_script')









    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>

    <script>


        $(document).ready( function () {
            $('#myTable').DataTable({


                processing: true,
                serverSide: true,
                searching: true,

                searchDelay: 200,
                "filter": true,
                "orderMulti": false,

                ajax: '{!! url('/total_customer_list_L') !!}',



                columns: [



                    {
                        data: 'qb_serial',
                        name: 'qb_serial',orderable:false,
                    },


                    {
                        data: 'qb_name',
                        name: 'qb_name', orderable:false,
                    },

                    {
                        data: 'qb_company_name',
                        name: 'qb_company_name', orderable:false,
                    },


                    {
                        data: 'qb_mobile',
                        name: 'qb_mobile', orderable:false,
                    },

                    {
                        data: 'qb_address',
                        name: 'qb_address', orderable:false,
                    },
                    {
                        data: 'qb_reason',
                        name: 'qb_reason', orderable:false,
                    },



                    {
                        data: 'qb_result',
                        name: 'qb_result', orderable:false,
                    },

                    //    {data: 'Action', name: 'action'},


                    {
                        data: "button",orderable:false, searchable: false,
                        render: function(data, type, $row) {
                            @foreach ($customers as $customer)


                                return '<a href="{{ url('/client_feedback', ['id' => $customer->qb_id]) }}" class="btn btn-info btn-sm">Follow Up</a>';

                            @endforeach

                        },

                    },


                ]




            });
        } );


    </script>


@endsection






{{--  previous code  --}}




{{--



   $(document).ready(function() {
        $('#example').DataTable( {
            dom: 'Bfrtip',
            buttons: [
              {
                  extend: 'excelHtml5',
                  exportOptions: {
                    columns: [4]
                  }
              },
              {
                  extend: 'pdfHtml5',
                  exportOptions: {
                      columns: [4]
                  }
              },

            ]
        } );
    } );

  --}}
