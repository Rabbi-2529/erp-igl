@extends('layout.master')
@section('oms_class','menu-open')
@section('oms_convence_class','menu-open')
@section('convence_users','active')
@section('content')
<section class="content">
<section class="content-header">
      <h1>
        Conveyance
      </h1>
      @if(Auth::user()->au_user_type == 4)
        <a href="" class="btn btn-info" style="margin-top: 10px;" data-toggle="modal" data-target="#oms_convence_pay">PAID</a>
        <div class="modal fade" id="oms_convence_pay">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Pay Conveyance Bill</h4>
              </div>
              <div class="modal-body">
                {{Form::open(['action' => 'OmsController@oms_pay_convence' , 'method' => 'post' , 'class' => 'form-horizontal'])}}
                  <div class="box-body">
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Pay To</label>
                      <div class="col-sm-9">
                        <select class="form-control" name="oms_staff_id" onchange="showUser(this.value)">
                          <option value="">Select</option>
                          @foreach($users as $user)
                          <option value="{{ $user->au_id }}">{{ $user->au_name }}</option>
                          @endforeach
                        </select>
                        <b><p id="balance" class="text-success"></p></b>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Payment System</label>
                      <div class="col-sm-9">
                        <select class="form-control" name="oms_payment">
                          <option value="">Select</option>
                          <option value="Cash">Cash</option>
                          <option value="Check">Check</option>
                          <option value="Mobile Banking">Mobile Banking</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Purpose</label>
                      <div class="col-sm-9">
                        <input type="text" name="oms_reason" value="" class="form-control" id="inputEmail3" placeholder="Enter Purpose...">
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">Amount</label>
                      <div class="col-sm-9">
                        <input type="text" name="oms_credit" value="" class="form-control" id="inputEmail3" placeholder="Enter Amount...">
                      </div>
                    </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-warning pull-left" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">PAID</button>
              </div>
            </div>
            {{Form::close()}}
            <!-- /.modal-content -->
          </div>
        </div>
      @endif
</section>
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">User Bill Details</h3>
        </div>
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
            @foreach($confirms as $confirm)
            <tr>
              <td>{{ ++$sl }}</td>
              <td>{{ $confirm->first()->employee_info['au_name'] }}</td>
              <td>{{ $confirm->first()->employee_info['au_company_name'] }}</td>
              <td>{{ $confirm->first()->employee_info['au_mobile'] }}</td>
              <td align="right">{{ number_format($confirm->sum('oms_debit'),2) }}</td>
              <td align="right">{{ number_format($confirm->sum('oms_credit'),2) }}</td>

              @php($balance = $balance - $confirm->sum('oms_debit') + $confirm->sum('oms_credit'))

              <td align="right">{{ number_format($confirm->sum('oms_credit') - $confirm->sum('oms_debit'),2) }}</td>
              <td align="center"><a href="{{ url('/oms_convence_details',['id' => $confirm->first()->oms_staff_id]) }}" class="btn btn-info btn-sm">Details</a></td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
              <tr>
                  <td colspan="4" align="right"> </td>
                  <td></td>
                  <td>Total: </td>
                <td align="right">{{ number_format($balance,2) }}</td>
                  <td></td>

              </tr>
            </tfoot>
          </table>
        </div>
      </div>
  </section>
@endsection
@section('custom_script')
<script type="text/javascript">

    $(document).ready(function() {
        $('#datatable').dataTable({
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                var intVal = function (i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                };

                total = api
                    .column(4, {filter: 'applied'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                pageTotal = api
                    .column(4, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                $(api.column(4).footer()).html( total);
            },
        })

    })
    
    function showUser(id){
      var link = "{{ route('users_amount') }}";
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
</script>
@endsection
