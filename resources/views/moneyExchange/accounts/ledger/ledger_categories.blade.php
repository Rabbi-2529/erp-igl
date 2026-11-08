@extends('layout.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('ledger_class','menu-open')
@section('ledger_category','active')
@section('content')
<section class="content">

@if(Session::has('err'))
<div class="alert alert-danger alert-dismissible" role="alert">
{{ session('err') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
@endif
@if(Session::has('err_up'))
<div class="alert alert-danger alert-dismissible" role="alert">
{{ session('err_up') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
@endif
@if(Session::has('suc'))
<div class="alert alert-success alert-dismissible" role="alert">
{{ session('suc') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
@endif
@if(Session::has('up_suc'))
<div class="alert alert-success alert-dismissible" role="alert">
{{ session('up_suc') }}
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
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

                   <div class="box">
                    <div class="box-header with-border">
                      <h3 class="box-title">Ledger Category</h3>
                    </div>
                   
                    <!-- /.box-header -->
                    <!-- form start -->
                    <div class="box-body">
                    <div class="col-sm-4" style="border: 1px solid grey;padding:15px; margin-right: 7px;">
                      <div class="card card-primary">
                        <div class="card-header">
                          <h3 class="card-title" style="font-size:24px; margin-top:-5px;">Add Ledger Category</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form role="form" action="{{route('accounts.ledger-category-insert')}}" method="post">
                          <input type="hidden" name="_token" value="{{csrf_token()}}" id="_token">
                          <div class="card-body">
                            <div class="form-group">
                              <label for="exampleInputEmail1">Category Name</label>
                              <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Write Category Here" required name="ledg_category">
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
                      <h3 class="box-title">Ledger Category List</h3>
                    </div>
                      <table id="example1" class="table table-bordered table-striped">
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

                              @php($total_expenses= $total_expenses + App\Inv_acc_bank_statement::getTotalExpensesByCategory($row->inv_acc_exp_cat_category_id))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->inv_ledg_cat_category_name }}</td>
                                    <td>{{ ($row->inv_ledg_cat_status==1)? 'Active':'In-Active' }}</td>
                                    <td class="text-right">
                                      {{number_format(App\Inv_acc_bank_statement::getTotalExpensesByCategory($row->inv_acc_exp_cat_category_id),2)}}
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
                                              <a href="#" onclick="showCategoryLedgerDetails('{{ $row->inv_acc_exp_cat_category_id }}')" data-toggle="modal" data-target="#details">
                                              Show
                                            </a>
                                          </li>
                                          <li class="divider"></li>
                                          <li>
                                              <a href="" data-toggle="modal" data-target="#ledg_cat_edit" onclick="LedgerUpdateDetails('{{ $row->inv_ledg_cat_cat_id }}')">
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
{{ Form::open(['action' => 'Inventory\InvLedgerController@ledger_category_update' , 'method' => 'post']) }}
<div class="modal fade" id="ledg_cat_edit" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ledger Category Update</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="ledger_cat_update">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
{{ Form::close() }}

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


 function showCategoryLedgerDetails(cat_id) {

    var requestUrl="{{route('accounts.ledger-categories-load-ajax')}}";
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

  function LedgerUpdateDetails(ledg_cat_id) {

  var requestUrl="{{route('accounts.ledger-category-update')}}";
  var _token=$("#_token").val();
  $.ajax({  
    type: "GET",
    url: requestUrl,
    data: { ledg_cat_id:ledg_cat_id, _token:_token},
    success: function (result) {
    $("#ledger_cat_update").html(result);
    $("#ledg_cat_edit").modal("show");
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




