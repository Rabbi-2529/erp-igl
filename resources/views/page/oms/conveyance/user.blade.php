@extends('layouts.master')
@section('title', 'Conveyance User')

@section('oms_class','active')
@section('conveyance_class','active')
@section('conveyance_user_class','active')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title row">
                    <div class="col-md-6">
                        <h4>Conveyance User</h4>
                    </div>
                    @if(Auth::user()->au_user_type == 4)
                    <div class="col-md-6 text-right">
                        <a href="" class="btn btn-info" style="margin-top: 10px;" data-toggle="modal" data-target="#oms_convence_pay">PAID</a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="panel-body">
                
                {{-- Modal Content START--}}
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
                              @php
                                  // Sort the users collection by au_user_id in ascending order
                                  $sorted_users = $users->sortBy('au_user_id');
                              @endphp

                              <select class="form-control" name="oms_staff_id" onchange="showUser(this.value)">
                                  <option value="">Select</option>
                                  @foreach($sorted_users as $user)
                                      <option value="{{ $user->au_id }}">{{ $user->au_user_id }} ({{ $user->au_name }})</option>
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
                  </div>
                </div>
                {{-- Modal Content END--}}
              

              <div class="box">
                <div class="box-body">
                  <table class="table table-bordered table-striped table-hover datatable">
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
                    @php($debitTotal = 0)
                    @php($creditTotal = 0)
                    @php($sl=0)
                    @foreach($confirms as $confirm)
                        @if($confirm->first()->employee_info)
                            <tr>
                                <td>{{ ++$sl }}</td>
                                <td>{{ $confirm->first()->employee_info['au_name'] }}</td>
                                <td>{{ $confirm->first()->employee_info['au_company_name'] }}</td>
                                <td>{{ $confirm->first()->employee_info['au_mobile'] }}</td>
                                <td align="right">{{ number_format($confirm->sum('oms_debit'),2) }}</td>
                                <td align="right">{{ number_format($confirm->sum('oms_credit'),2) }}</td>
                    
                                @php($balance = $balance - $confirm->sum('oms_debit') + $confirm->sum('oms_credit'))
                                @php($debitTotal += $confirm->sum('oms_debit'))
                                @php($creditTotal += $confirm->sum('oms_credit'))
                    
                                <td align="right">{{ number_format($confirm->sum('oms_credit') - $confirm->sum('oms_debit'),2) }}</td>
                                <td align="center"><a href="{{ url('/oms_convence_details',['id' => $confirm->first()->oms_staff_id]) }}" class="btn btn-info btn-sm">Details</a></td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                          <td colspan="4" align="right"><strong>Total</strong></td>
                          <td align="right"><strong>{{ number_format($debitTotal, 2) }}</strong></td>
                          <td align="right"><strong>{{ number_format($creditTotal, 2) }}</strong></td>
                          <td align="right"><strong>{{ number_format($balance, 2) }}</strong></td>
                          <td></td>
                      </tr>
                  </tfoot>
                  
                  </table>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    
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