@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_lunch_class','menu-open')
@section('lunch_users','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Meal
      </h1>
      @if(Auth::user()->au_user_type == 4)
      <a href="" class="btn btn-info" style="margin-top: 10px;" data-toggle="modal" data-target="#oms_convence_pay">PAID</a>
      
      @endif
    </section>
	<div class="box">
            <div class="box-header">
              <h3 class="box-title">User Bill Details</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="datatable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SL</th>
                  <th>User Name</th>
                  <th>Company Name</th>
                  <th>Mobile</th>
                  <th>Debit</th>
                  <th>Credit</th>
                  <th>Balance</th>
                  <th class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @php($balance=0)
                @php($sl=0)
                @foreach( $lunch as $confirm )
                <tr>
                  <td>{{ ++$sl }}</td>
                  <td>{{ $confirm->first()->employee_info['au_name'] }}</td>
                  <td>{{ $confirm->first()->employee_info['au_company_name'] }}</td>
                  <td>{{ $confirm->first()->employee_info['au_mobile'] }}</td>
                  <td align="right">{{ number_format($confirm->sum('oms_debit'),2) }}</td>
                  <td align="right">{{ number_format($confirm->sum('oms_credit'),2) }}</td>
                  @php($balance = $balance - $confirm->sum('oms_debit') + $confirm->sum('oms_credit'))
                  <td align="right">{{ number_format($confirm->sum('oms_credit') - $confirm->sum('oms_debit'),2) }}</td>
                  <td align="center">
                      <a href="{{ url('/oms_lunch_details',['id' => $confirm->first()->oms_staff_id]) }}" class="btn btn-info btn-sm">Details</a>
                  </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                  <td colspan="4" align="right"></td>
                  <td></td>
                  <td>Total:</td>

                  <td align="right">{{ number_format($balance,2) }}</td>
                  <td></td>

                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
         </section>
@endsection
@section('custom_script')
<script type="text/javascript">
  function showUser(id){

    var link = "{{ route('users_lunch_amount') }}";
    console.log(id);
    $.ajax({
      url: link,
      data: {id : id},
      success: function(result){
        if(result == 0) {
          $("#balance").text('');
          $("#balance").text("No Due Balance!");
        } else {
          $("#balance").text('');
          $("#balance").text("Amount is: "+result);
        }
      }
    });
  }
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
                  .column(4, {filter: 'applied'})
                  .data()
                  .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);

              // Total over this page
              pageTotal = api
                  .column(4, { page: 'current' })
                  .data()
                  .reduce(function (a, b) {
                      return intVal(a) + intVal(b);
                  }, 0);

              // Update footer
              // $(api.column(4).footer()).html('$' + pageTotal + ' ( $' + total + ' total)');
              $(api.column(4).footer()).html( total);
          },
      })

  })
</script>
@endsection
