@extends('layouts.master')
@section('title','Expenses')
@section('inventory_class','active')
@section('accounts_class','active')
@section('expense_class','active')
@section('expenses','active')
@section('content')
<section class="content">
        @if(Session::has('errmsg'))
              <div class="alert alert-danger alert-dismissible" role="alert">
              {{ session('errmsg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif
              @if(Session::has('msg'))
              <div class="alert alert-success alert-dismissible" role="alert">
              {{ session('msg') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
              </div>
              @endif

                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Expenses</h3>
                    </div>

                    <!-- /.box-header -->
                    <!-- form start -->
                    <div class="box-body">
                    <div class="col-sm-4" style="border: 1px solid grey;padding:15px; margin-right: 7px;">
                      <div class="card card-primary">
                        <div class="card-header">
                          <h3 class="card-title" style="font-size:24px; margin-top:-5px;">Add Expense</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form role="form" action="{{route('accounts.expenses')}}" method="post">
                          @csrf
                          <div class="card-body">
                            <div class="form-group">
                              <label for="exampleInputEmail1">Category Name</label>

                              <select required name="exp_category_id" class="form-control select2" id="exampleInputEmail1">
                                <option value="">-- Select A Category --</option>
                                @foreach($categories as $category)
                                <option value="{{$category->inv_acc_exp_cat_category_id}}">{{ $category->inv_acc_exp_cat_category_name }}</option>
                                @endforeach
                              </select>
                            </div>
                             <div class="form-group">
                              <label for="exampleInputEmail1">Expense Name</label>
                              <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Write Expense Name Here" required name="exp_name" ">
                            </div>
                          </div>
                          <!-- /.card-body -->

                          <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="reset" class="btn btn-danger">Reset</button>
                          </div>
                        </form>
                      </div>
                    </div>
                    <div class="col-sm-1" style="margin-right: -10px; margin-left: -10px;"></div>
                    <div class="col-sm-7" style="border: 1px solid grey;padding:15px;margin-left:5px; ">
                      <div class="box-header with-border">
                      <h3 class="box-title">Expenses List</h3>
                    </div>
                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                            <th style="text-align: center;">SL</th>
                            <th style="text-align: center;">Name</th>
                            <th style="text-align: center;">Category</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Amount</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                        </thead>

                        <tbody>


                        @php($total_expenses=0)
                        @if(!empty($expenses))
                            @foreach($expenses as $row)
                              @php($total_expenses+=App\Models\Inv_acc_bank_statement::getTotalExpensesByExpenses($row->inv_acc_exp_id))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->inv_acc_exp_expense_name }}</td>
                                    <td>
                                      {{ $row->getCategoryByCatID['inv_acc_exp_cat_category_name'] }}
                                    </td>
                                    <td>{{ ($row->inv_acc_exp_cat_status==1)? 'Active':'In-Active' }}</td>
                                    <td class="text-right">
                                      {{ number_format(App\Models\Inv_acc_bank_statement::getTotalExpensesByExpenses($row->inv_acc_exp_id),2)}}
                                    </td>
                                    <td style="text-align: center;">
                                  <a href="#" onclick="showExpensesDetails('{{ $row->inv_acc_exp_id }}')" data-toggle="modal" data-target="#details">

                                  <i class="fa fa-eye"></i>
                                </a>
                                </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot>
                        <tr>
                            <th style="text-align: center;">#</th>
                            <th colspan="3" class="text-right">Total:</th>
                            <th class="text-right" >{{ number_format($total_expenses, 2) }}</th>
                            <th style="text-align: center;">---</th>
                        </tr>
                        </tfoot>
                    </table>
                    </div>
                  </div>
                  </div>

      <!-- Modal -->
<div class="modal fade" id="details" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Expense Details</h4>
      </div>
      <div class="modal-body">
        <div class="load-details"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>


 </section>
@endsection
@section('custom_script')
<script type="text/javascript">

$(document).ready(function(){

$( "#opendate" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});



 function showExpensesDetails(exp_id) {

    var requestUrl="{{route('accounts.expense-load-ajax')}}";
    var _token=$("#_token").val();
    $.ajax({
      type: "GET",
      url: requestUrl,
      data: { exp_id:exp_id, _token:_token},
      success: function (result) {
       $(".load-details").html(result);
       $("#showDetailsModal").modal("show");
      }
    });
  }

</script>
@endsection


@section('custom_style')
<style type="text/css">
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
</style>
@endsection





