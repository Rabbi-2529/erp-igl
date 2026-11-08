@extends('layout.master')
@section('sds_menu_class','menu-open')
@section('customer_class','menu-open')
@section('order_customer_class','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Customer List
      </h1>
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">Ordered Customers List</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  @if (Auth::user()->au_user_type == 4)
                    <th>User</th>
                    <th class="text-center">Credit</th>
                    <th class="text-center">Debit</th>
                  @elseif(Auth::user()->au_user_type == 5)
                    <th>User</th>
                    <th class="text-center">Credit</th>
                    <th class="text-center">Debit</th>
                    <th>Action</th>
                  @elseif(Auth::user()->au_user_type == 6)
                    <th>Company Name</th>
                    <th>Client Mobile</th>
                    <th class="text-center">Credit</th>
                    <th class="text-center">Debit</th>
                    <th class="text-center">Total</th>
                    <th>Action</th>
                  {{-- @else
                  <th>Cus Name</th>
                  <th>Company Name</th>
                  <th>Client Mobile</th>
                  <th>Client Message</th>
                  <th class="text-center">Credit</th>
                  <th class="text-center">Debit</th>
                  <th>Action</th> --}}
                  @endif
                </tr>
                </thead>
                <tbody>
                @php($sl=0)
                @php($total = 0)
                @php($total_remain = 0)
                @foreach( $orders as $order )
                @php($leader = App\Models\Admin_user::where('au_company_id', Auth::user()->au_company_id)
                                          ->where('au_id',$order->user_id)->first())
                <tr>
                  <td>{{ ++$sl }}</td>
                  @if (Auth::user()->au_user_type == 4)
                    <td>{{ App\Models\Admin_user::userDetail($order->user_id) }}</td>
                    <td class="text-right">{{ number_format($order->total_credit,2) }}</td>
                    <td class="text-right">{{ number_format($order->total_debit,2) }}</td>
                  @elseif(Auth::user()->au_user_type == 5)
                    <td>{{ App\Models\Admin_user::userDetail($order->user_id) }}</td>
                    <td class="text-right">{{ number_format($order->total_credit,2) }}</td>
                    <td class="text-right">{{ number_format($order->total_debit,2) }}</td>
                    @if ($leader->au_user_type == 5)
                    <td>
                      <button type="button" data-toggle="modal" onclick="orderDetail('{{ $order->id }}')" data-target="#exampleModal" class="btn btn-sm btn-info">Action</button>
                    </td>
                    @else
                    <td></td>
                    @endif
                  @elseif(Auth::user()->au_user_type == 6)
                    <td>{{ App\Models\Sds_query_book::clientDetail($order->order_client)->qb_company_name }}</td>
                    <td>{{ App\Models\Sds_query_book::clientDetail($order->order_client)->qb_mobile }}</td>
                    <td class="text-right">{{ number_format($order->total_credit,2) }}</td>
                    <td class="text-right">{{ number_format($order->total_debit,2) }}</td>
                    @php($total_remain = $order->total_credit - $order->total_debit)
                    <td class="text-right">{{ number_format($total_remain,2) }}</td>
                    <td>
                      <button type="button" data-toggle="modal" onclick="orderDetail('{{ $order->id }}')" data-target="#exampleModal" class="btn btn-sm btn-info">Action</button>
                    </td>
                    {{-- @else
                  <td>{{ $order->orderInfo->qb_name }}</td>
                  <td>{{ $order->orderInfo->qb_company_name }}</td>
                  <td>{{ $order->orderInfo->qb_mobile }}</td>
                  <td>{{ $order->description }}</td>
                  <td class="text-right">{{ number_format($order->order_price_credit,2) }}</td>
                  <td class="text-right">{{ number_format($order->order_price_debit,2) }}</td>
                  <td></td> --}}
                  @endif
                  {{-- @php($total = $total + $order->order_price_credit - $order->order_price_debit) --}}
                </tr>
                @endforeach
                </tbody>

              </table>
            </div>
            <!-- /.box-body -->
          </div>
          </section>

          <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Order</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" id="orderData">
                  ...
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
          @endsection
          @section('custom_script')
            <script type="text/javascript">


                function orderDetail(orderId){
                  // console.log(orderId);
                  $.ajax({
                    type: "GET",
                    url: "{{ route('order-update-form') }}",
                    data: {orderId:orderId},
                    success:function(result){
                      $('#orderData').html(result);
                      $('#exampleModal').show();
                    }
                  })
                }

        $('form').submit(function() {
            $(this).find("button[type='submit']").prop('disabled',true);
        });

            </script>
          @endsection
