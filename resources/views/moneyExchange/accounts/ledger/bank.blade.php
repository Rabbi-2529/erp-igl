@extends('layout.master')
@section('inventory_class','menu-open')
@section('accounts_class','menu-open')
@section('ledger_class','menu-open')
@section('ledger_bank','active')
@section('content')
<section class="content-header">
    <div class="box">
        <div class="box-header">
             
      <form action="" method="get">
        <input type="hidden" name="_token" value="" id="_token">
       <div class="col-xs-3">
        <input type="text" name="start_date" data-date-format="yyyy-mm-dd" autocomplete="off" value="" class="form-control" id="start_date" placeholder="Enter Start Date" >
       </div>
          <div class="col-xs-3">
            <button type="submit" class="btn btn-info" name="searchbtn">CLICK</button>
          </div>
          </form>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
           </div>
       </div>
        <h1>
         Bank Income & Expense  
        </h1>
  
      </section>
      <div class="box" id="bank">
              <!-- /.box-header -->
              <div class="box-body" >
                <div class="col-md-6">
                  <div class="row">
                    <div class="col-md-8">
                    <select name="" id="" class="form-control select2">
                        <option value="">Select Bank</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <input type="text" class="form-control" value="bank value" disabled>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 text-right">
                  <button type="button" class="btn btn-success btn-sm" @click="addNewRow()">
                    <i class="fa fa-plus"></i>
                  </button>
                </div>
                <div class="col-md-12">
                <table id="example6" class="table table-bordered table-striped" style="margin-top: 20px;">
                  <thead>
                  <tr>
                    <th style="width: 55px;">A/C Name</th>
                    <th style="width: 225px;">Payment By</th>
                    <th>Particulars</th>
                    <th style="width: 116px;">Debit</th>
                    <th style="width: 116px;">Credit</th>
                    <th style="width: 127px;">Balance Amount</th>
                    <th>Add</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                  <tr>
                      <td></td>
                      <td>
                        <select name="" id="" class="form-control select2">
                          <option value="">Select One</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control" style="padding:1px;">
                      </td>
                      <td>
                          <input type="text" class="form-control" style="width: 100px; padding:1px;">
                      </td>
                      <td>
                          <input type="text" class="form-control" style="width: 100px; padding:1px;">
                      </td>
                      <td class="text-right">
                          <input type="text" class="form-control" style="width: 100px;">
                      </td>
                      <td style="text-align: center;">
                        <button class="btn btn-success btn-xs">
                          <i class="fa fa-plus"></i>
                        </button> 
                        <button class="btn btn-danger btn-xs">
                            <i class="fa fa-minus"></i>
                        </button> 
                      </td>
                  </tr>
                  
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="5" style="text-align:right; font-weight: bolder;">Total:</td>
                      <td style="font-weight: bolder; text-align: right;"></td>
                      <td style="text-align: center;font-weight: bolder;">---</td>
                    </tr>
                  </tfoot>
                </table>
                </div>
              </div>
              <!-- /.box-body -->
            </div>
           </section>
@endsection
@section('custom_script')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js"></script>
<script>
  var app = new Vue({
    el: '#bank',
    data: {
      bank_summery: [],
    },
    methods:{
      addNewRow() {
      this.bank_summery.push({
          income: '',
          customer: '',
          narration: '',
          cash_in: 0
          });
      },
    },
  });
</script>    
@endsection

