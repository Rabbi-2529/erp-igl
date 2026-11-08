@extends('layouts.master')
@section('title','Expense Category')
@section('inventory_class','active')
@section('accounts_class','active')
@section('expense_class','active')
@section('expense_category','active')
@section('content')
<section class="content">


<div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                        <h3 class="box-title">Expense Category</h3>
                        </div>
                    </div>
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
          @if(Session::has('exp_up'))
          <div class="alert alert-success alert-dismissible" role="alert">
          {{ session('exp_up') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          </button>
          </div>
          @endif
</div>


              

                    <div class="col-sm-4" style="border: 1px solid grey;padding:15px; margin-right: 7px;">
                      <div class="card card-primary">
                        <div class="card-header">
                          <h3 class="card-title" style="font-size:24px; margin-top:-5px;">Add Expense Category</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form role="form" action="{{route('accounts.expense-categories')}}" method="post">
                          <input type="hidden" name="_token" value="{{csrf_token()}}" id="_token">
                          <div class="card-body">
                            <div class="form-group">
                              <label for="exampleInputEmail1">Category Name</label>
                              <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Write Category Here" required name="exp_category">
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
                      <h3 class="box-title">Expense Category List</h3>
                    </div>
                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                            <th style="text-align: center;">SL</th>
                            <th style="text-align: center;">Category</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Amount</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                        </thead>

                        <tbody>

                        <!-- employee lists -->
                        @php($total_expenses=0)
                        @if(!empty($categories))
                            @foreach($categories as $row)

                              @php($total_expenses= $total_expenses + App\Models\Inv_acc_bank_statement::getTotalExpensesByCategory($row->inv_acc_exp_cat_category_id))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->inv_acc_exp_cat_category_name }}</td>
                                    <td>{{ ($row->inv_acc_exp_cat_status==1)? 'Active':'In-Active' }}</td>
                                    <td class="text-right">
                                      {{number_format(App\Models\Inv_acc_bank_statement::getTotalExpensesByCategory($row->inv_acc_exp_cat_category_id),2)}}
                                    </td>
                                <td style="text-align: center;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-xs">Action</button>
                                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                          <span class="caret"></span>
                                          <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" style="margin-left: -40px;">
                                          <li>
                                              <a href="#" onclick="showCategoryExpensesDetails('{{ $row->inv_acc_exp_cat_category_id }}')" data-toggle="modal" data-target="#details">
                                              Show
                                            </a>
                                          </li>
                                          <li class="divider"></li>
                                          <li>
                                              <a href="{{ route('accounts.expense-category-edit',['cat_id' => $row->inv_acc_exp_cat_category_id]) }}">
                                              Update
                                            </a>
                                          </li>
                                        </ul>
                                  </div>
                                </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot>
                        <tr>
                            <th style="text-align: center;">#</th>
                            <th colspan="2" class="text-right">Total :</th>
                            <th class="text-right">{{ number_format($total_expenses, 2) }}</th>
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
        <h4 class="modal-title">Expense Categories Details</h4>
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


 function showCategoryExpensesDetails(cat_id) {

    var requestUrl="{{route('accounts.expense-categories-load-ajax')}}";
    var _token=$("#_token").val();
    $.ajax({
      type: "GET",
      url: requestUrl,
      data: { cat_id:cat_id, _token:_token},
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




