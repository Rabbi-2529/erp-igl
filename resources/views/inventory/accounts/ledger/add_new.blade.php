@extends('layouts.master')
@section('title','Ledger')
@section('inventory_class','active')
@section('accounts_class','active')
@section('ledger_class','active')
@section('ledger_add','active')
@section('content')
<section class="content">
        <section class="content-header">
            @if(session()->has('suc'))
            <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('suc') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif
            @if(session()->has('sucup'))
            <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('sucup') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif
            @if(session()->has('err'))
            <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('err') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif 
            @if(session()->has('errup'))
            <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('errup') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            @endif        
            </section>
            <div class="box">
                <div class="box-header">
                     
              <form action="" method="get">
                <input type="hidden" name="_token" value="" id="_token">
               <div class="col-xs-3">
                <input type="text" name="dateinput" data-date-format="yyyy-mm-dd" autocomplete="off" value="{{ request()->dateinput }}" class="form-control" id="date" placeholder="Enter Date" >
               </div>
                <div class="col-xs-3">
                <button type="submit" class="btn btn-info" name="date">CLICK</button>
                </div>
              </form>
                      </div>
                      <!-- /.box-header -->
                      <div class="box-body">
                   </div>
               </div>
               @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
{{ Form::open(['action' => 'Inventory\InvLedgerController@insert_ledger_data' , 'method' => 'post']) }}
               @if (request()->has('dateinput'))
               <input type="text" name="issue_date" hidden value="{{ request()->dateinput }}">
                   <div class="box" id="ledger" v-cloak>
                    <div class="box-header with-border">
                      <h3 class="box-title">Cash Add</h3>
                    </div>  
                    <div class="col-md-6" style="margin: 0; padding: 0;">
                   <div class="box-body" style="padding: 0px;">
                <table class="table table-bordered table-striped table-hover datatable" id="" style="margin-top: 20px;">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Income</th>
                            <th>Customer</th>
                            <th>Description</th>
                            <th>Cash In</th>
                            <th>Add</th>
                        </tr>
                        </thead>
                        <tbody>
                       <tr>
                           <td colspan="4" class="text-right">Balance BD</td>
                           <td class="text-right"><input type="text" disabled v-model="bal_bd" value="{{ number_format($total_bal_bd,2) }}" class="form-control text-right"></td>
                           <td></td>
                       </tr>
                       
                       {{-- @dd($old); --}}
                       <tr v-for="(income_val, k) in old_income_value" :key="k">
                            <td>@{{ k+1 }}</td>
                            <td>
                                 <input type="text" value="" class="form-control" disabled>
                            </td>
                            <td>
                                <input type="text" value="" class="form-control" disabled>
                            </td>
                            <td><input type="text" name="first_narration[]" required value="" :value="income_val.inv_pro_inv_tran_desc" class="form-control" disabled></td>
                            <td>
                                <input type="text" name="cash_in[]" autocomplete="off" required :value="income_val.inv_pro_inv_credit"  class="form-control text-right" disabled>
                            </td>
                            <td style="width: 64px;" class="text-center">
                                <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#customer_modal" @click="cash_in_edit(income_val.inv_pro_inv_id)">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-for="(ledger_cash_in, k) in ledger_cash_ins" :key="k">
                            <td>@{{ k+1 }}</td>
                            <td>
                                <select name="income[]" id="" v-model="ledger_cash_in.income" class="js-example-basic-single" v-select2 required>
                                    <option value="">...</option>
                                    @foreach ($ledger_category as $cat)
                                    <optgroup label="{{ $cat->inv_ledg_cat_category_name }}">
                                        @foreach ($cat->getLedgers as $ledg)
                                            <option value="{{ $ledg->inv_ledg_id }}">{{ $ledg->inv_ledg_ledger_name }}</option>
                                        @endforeach
                                    </optgroup> 
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="customer[]" id="" v-model="ledger_cash_in.customer" class="js-example-basic-single" v-select2>
                                    <option value="">...</option>
                                @foreach ($customers as $cus)
                                    <option value="{{ $cus->inv_cus_id }}">{{ $cus->inv_cus_name }}</option>
                                @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="first_narration[]" required value="" v-model="ledger_cash_in.narration" class="form-control" required></td>
                            <td>
                                <input type="text" name="cash_in[]" autocomplete="off" required value="" v-model="ledger_cash_in.cash_in" required class="form-control text-right">
                            </td>
                            <td style="width: 64px;">
                                    <button type="button" class="btn btn-success btn-xs" @click="addNewRow()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs" @click="deleteRow(k,ledger_cash_in)">
                                        <i class="fa fa-minus"></i>
                                    </button>
                            </td>
                        </tr>
                        
                        </tbody>
                        {{-- <tfoot>
                            <tr>
                                <td colspan="4" class="text-right">Total:</td>
                                <td><input type="text" :value="total_cash_in" disabled class="text-right form-control"></td>
                                <td></td>
                            </tr>
                        </tfoot> --}}
                        </table>
                   </div>
                   </div>
                   <div class="col-md-6" style="margin: 0; padding: 0;">
                        <div class="box-body text-center" style="padding: 0px;">
                     <table class="table table-bordered table-striped table-hover datatable" id="expense_table" style="margin-top: 20px;">
                             <thead>
                             <tr>
                                 <th>SL</th>
                                 <th>Expense</th>
                                 <th>Supplier</th>
                                 <th>Description</th>
                                 <th style="width: 80px;">Cash Out</th>
                                 <th>Add</th>
                             </tr>
                             </thead>
                             <tbody>
                            <tr v-for="(expense_val, k) in old_expense_value" :key="k">
                                    <td>@{{ k+1 }}</td>
                                    <td>
                                        <select name="expense[]" id="income" v-model="expense_val.expense" class="js-example-basic-single" required v-select2 disabled>
                                            <option value="">...</option>
                                            @foreach ($ledger_category as $cat)
                                            <optgroup label="{{ $cat->inv_ledg_cat_category_name }}">
                                                @foreach ($cat->getLedgers as $ledg)
                                                    <option value="{{ $ledg->inv_ledg_id }}">
                                                        {{ $ledg->inv_ledg_ledger_name }}
                                                    </option>
                                                @endforeach
                                            </optgroup> 
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="supplier[]" id="" v-model="expense_val.supplier" class="js-example-basic-single" v-select2 disabled>
                                            <option value="">...</option>
                                        @foreach ($suppliers as $sup)
                                            <option value="{{ $sup->inv_sup_id }}">{{ $sup->inv_sup_person }}</option>
                                        @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" required name="second_narration[]" :value="expense_val.inv_pro_inv_tran_desc" class="form-control" disabled></td>
                                    <td>
                                        <input type="text" autocomplete="off" required id="cash_out" :value="expense_val.inv_pro_inv_credit" name="cash_out[]"  class="form-control text-right" disabled>
                                        </td>
                                    <td style="width: 64px;">
                                        <button type="button" class="btn btn-success btn-xs" @click="cash_out_edit(expense_val.inv_pro_inv_id)" data-toggle="modal" data-target="#supplier_modal">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                             <tr v-for="(ledger_cash_out, k) in ledger_cash_outs" :key="k">
                                 <td>@{{ k+1 }}</td>
                                 <td>
                                    <select name="expense[]" id="income" v-model="ledger_cash_out.expense" class="js-example-basic-single" required v-select2>
                                        <option value="">...</option>
                                        @foreach ($ledger_category as $cat)
                                        <optgroup label="{{ $cat->inv_ledg_cat_category_name }}">
                                            @foreach ($cat->getLedgers as $ledg)
                                                <option value="{{ $ledg->inv_ledg_id }}">
                                                    {{ $ledg->inv_ledg_ledger_name }}
                                                </option>
                                            @endforeach
                                        </optgroup> 
                                        @endforeach
                                    </select>
                                 </td>
                                 <td>
                                    <select name="supplier[]" id="" v-model="ledger_cash_out.supplier" class="js-example-basic-single" v-select2>
                                        <option value="">...</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->inv_sup_id }}">{{ $sup->inv_sup_person }}</option>
                                    @endforeach
                                    </select>
                                 </td>
                                 <td>
                                     <input type="text" required name="second_narration[]" v-model="ledger_cash_out.narration" class="form-control" style="width:105px;">
                                </td>
                                 <td>
                                     <input type="text" autocomplete="off" required id="cash_out" name="cash_out[]" v-model="ledger_cash_out.cash_out" style="width:100px;" class="form-control text-right">
                                </td>
                                 <td style="width: 64px;">
                                    <button type="button" class="btn btn-success btn-xs" @click="addNewRowCashOut()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs" @click="deleteRowCashOut(k,ledger_cash_out)">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </td>
                             </tr>
                             </tbody>
                                {{-- <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right">Total:</td>
                                        <td><input type="text" :value="total_cash_out" disabled class="form-control text-right"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-right">Balance CD: </td>
                                        <td><input type="text" disabled :value="balance_cd" class="form-control text-right"></td>
                                        <td></td>
                                    </tr>
                                </tfoot> --}}
                             </table>
                        </div>
                        </div>
                        <div class="col-md-12" style="margin: 0; padding: 0;">
                            <div class="col-md-6" style="margin: 0; padding: 0;">
                                <table class="table table-bordered table-striped table-hover datatable">
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-right" >Total:</td>
                                            <td class="text-right" style="width:122px;">@{{ total_cash_in }}</td>
                                            <td style="width:64px;"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6" style="margin: 0; padding: 0;">
                                <table class="table table-bordered table-striped table-hover datatable">
                                    <tbody>
                                            <tr>
                                                <td colspan="4" class="text-right" style="width:330px;">Total:</td>
                                                <td class="text-right" style="width:80px;">@{{ total_cash_out }}</td>
                                                <td style="width:64px;"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-right" style="width:330px;">Balance CD: </td>
                                                <td class="text-right" style="width:80px;">@{{ balance_cd }}</td>
                                                <td style="width:64px;"></td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div> 
                  </div>
                  <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-success" style="">SUBMIT</button>
                 </div>
                  @endif               
                 </section>
                 {{ Form::close() }}
@isset($old_date_value_income)
{{ Form::open(['action' => ['Inventory\InvLedgerController@update_ledger_data'] , 'method' => 'post']) }}
    <div class="modal fade" id="customer_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Income</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cash_in_edit">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
            </div>
        </div>
        </div>
{{ Form::close() }}
@endisset

@isset($old_date_value_expense)
{{ Form::open(['action' => ['Inventory\InvLedgerController@update_ledger_cash_out_data'] , 'method' => 'post']) }}
        <div class="modal fade" id="supplier_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Expense</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="cash_out_edit">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                    </div>
                </div>
                </div>
{{ Form::close() }}
@endisset
@endsection
@section('custom_script')
<script type="text/javascript">
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});

$(document).ready(function() {
    var table = $('#example6').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    } );
} );
$(document).ready(function(){

$( "#date" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
        autoclose: true,
     });
$( "#to" ).datepicker({
       daysOfWeekHighlighted: "7",
        todayHighlight: true,
     });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js"></script>
<script>
    let old_expense_val = [];
    let old_income_val = [];
    let old_expense = [];
    let old_income = [];
    let old_supplier = [];
    let old_customer = [];
    let old_narration = [];
    let old_first_narration = [];
    let old_cash_out = [];
    let old_cash_in = [];
    @if (old('expense') != '') 
        @php($old_expense = old('expense'))
        old_expense = <?php echo json_encode($old_expense) ?>;
        @php($old_expense = old('supplier'))
        old_supplier = <?php echo json_encode($old_expense) ?>;
        @php($old_expense = old('second_narration'))
        old_narration = <?php echo json_encode($old_expense) ?>;
        @php($old_expense = old('cash_out'))
        old_cash_out = <?php echo json_encode($old_expense) ?>;
    @endif
    @if (old('income') != '') 
        @php($old_income = old('income'))
        old_income = <?php echo json_encode($old_income) ?>;
        @php($old_expense = old('customer'))
        old_customer = <?php echo json_encode($old_income) ?>;
        @php($old_income = old('first_narration'))
        old_first_narration = <?php echo json_encode($old_income) ?>;
        @php($old_income = old('cash_in'))
        old_cash_in = <?php echo json_encode($old_income) ?>;
    @endif
    Vue.directive('select2', {
    inserted(el) {
        $(el).on('select2:select', () => {
            const event = new Event('change', { bubbles: true, cancelable: true });
            el.dispatchEvent(event);
        });

        $(el).on('select2:unselect', () => {
            const event = new Event('change', {bubbles: true, cancelable: true})
            el.dispatchEvent(event)
        })
    },
    });

    var app = new Vue({
        el: "#ledger",
        data: {
            serial: 1,
            ledger_cash_ins: [],
            ledger_cash_outs: [],
            olDexpense: [],
            olDsupplier:[],
            olDnarration: [],
            olDcash_out: [],
            olDincome: [],
            olDcustomer: [],
            olD_first_narration: [],
            olD_cash_in: [],
            cash_in: [],
            cash_out: [],
            bal_bd: {{ @$total_bal_bd }},
            old_income_value: {!! json_encode(@$old_date_value_income) !!},
            old_expense_value: {!! json_encode(@$old_date_value_expense) !!},
            customers: {!! json_encode(@$customers_n) !!},
            ledger_cat: {!! json_encode(@$ledger_category_n) !!}
        },
        methods:{
            addNewRow() {
                this.ledger_cash_ins.push({
                    income: '',
                    customer: '',
                    narration: '',
                    cash_in: 0
                    });
                },
            addNewRowCashOut() {
            this.ledger_cash_outs.push({
                expense: '',
                supplier: '',
                narration: '',
                cash_out: 0
                });
            },
            setOldData(){
                this.olDexpense = old_expense;
                this.olDsupplier = old_supplier;
                this.olDnarration = old_narration;
                this.olDcash_out = old_cash_out;
                this.olDincome = old_income;
                this.olDcustomer = old_customer;
                this.olD_first_narration = old_first_narration;
                this.olD_cash_in = old_cash_in;
                for (let i = 0; i < this.olDexpense.length; i++) {
                    this.ledger_cash_outs.push({
                        expense: this.olDexpense[i],
                        supplier: this.olDsupplier[i],
                        narration: this.olDnarration[i],
                        cash_out: this.olDcash_out[i],
                    });
                }
                for (let i = 0; i < this.olDincome.length; i++) {
                    this.ledger_cash_ins.push({
                        income: this.olDincome[i],
                        customer: this.olDcustomer[i],
                        narration: this.olD_first_narration[i],
                        cash_in: this.olD_cash_in[i]
                    });
                }
            },
            deleteRow(index, ledger_cash_in) {
                var idx = this.ledger_cash_ins.indexOf(ledger_cash_in);
                console.log(idx, index);
                if (idx > -1) {
                    this.ledger_cash_ins.splice(idx, 1);
                }
                // this.calculateTotal();
            },
            deleteRowCashOut(index, ledger_cash_out) {
                var idx = this.ledger_cash_outs.indexOf(ledger_cash_out);
                console.log(idx, index);
                if (idx > -1) {
                    this.ledger_cash_outs.splice(idx, 1);
                }
                // this.calculateTotal();
            },
            cash_in_edit(cash_id) {
                let url = "{{ route('accounts.ledger-cash-in-edit') }}";
                var _token=$("#_token").val();
                $.ajax({  
                type: "GET",
                url: url,
                data: { cash_id: cash_id,_token:_token},
                success: function (result) {
                $("#cash_in_edit").html(result);
                }
                });
            },
            cash_out_edit(cash_out_id) {
                let url = "{{ route('accounts.ledger-cash-out-edit') }}";
                var _token=$("#_token").val();
                $.ajax({  
                type: "GET",
                url: url,
                data: { cash_out_id: cash_out_id,_token:_token},
                success: function (result) {
                $("#cash_out_edit").html(result);
                }
                });
            },
            set_table_serial() {
            let cs = 1;
            $('#expense_table td:first-child').each(function () {
                $(this).html(cs++);
            });
            let as = 1;
            $('#income_table td:first-child').each(function () {
                $(this).html(as++);
            });
        }
        },
        computed:{
            total_cash_in: function(){
                let amount = 0;
                for (let i = 0; i < this.ledger_cash_ins.length; i++) {
                        amount += parseFloat(this.ledger_cash_ins[i].cash_in);
                }
                for (let j = 0; j < this.old_income_value.length; j++) {
                    amount += parseFloat(this.old_income_value[j].inv_pro_inv_credit);
                    
                }
                return amount.toFixed(2);
            },
            total_cash_out: function(){
                let amount = 0;
                for (let i = 0; i < this.ledger_cash_outs.length; i++) {
                        amount += parseFloat(this.ledger_cash_outs[i].cash_out);
                }
                for (let j = 0; j < this.old_expense_value.length; j++) {
                        amount += parseFloat(this.old_expense_value[j].inv_pro_inv_credit);
                }
                return amount.toFixed(2);
            },
            balance_cd: function(){
                let total_balance_cd = 0;
                total_balance_cd = parseFloat(this.bal_bd) + parseFloat(this.total_cash_in) - parseFloat(this.total_cash_out);
                return total_balance_cd.toFixed(2);
            }
        },
        beforeMount(){
            this.setOldData();
            this.addNewRow();   
            this.addNewRowCashOut();
        },
    }); 
</script>
<script type="text/javascript">
  function loadExpense() {  
    let cat_id = $("#category").val();
    var requestUrl="{{route('accounts.ajax-load_expense')}}";
    var _token = $("#_token").val();
    $.ajax({  
      type: "GET",
      url: requestUrl,
      data: { cat_id: cat_id,_token:_token},
      success: function (result) {
       $(".expense_name_div").html(result);
      }
    });
  }

  function loadAvailableBalanceOfBank() {
    let bank_id = $("#bank_id").val();
    var requestUrl="{{route('accounts.ajax-load-bank-balance')}}";
    var _token = $("#_token").val();
    //$("#_token").val();
    $.ajax({  
      type: "GET",
      url: requestUrl,
      data: { bank_id: bank_id,_token:_token},
      success: function (result) {
       $(".bank_balance_div").html(result);
      }
    });
  }

  $("#form-id").submit(function (event) {
let btn = $("#submit-button-id");
btn.prop('disabled', true);
setTimeout(function(){
btn.prop('disabled', false);
}, 5000);
return true;
});
  
</script>
@endsection

@section('custom_style')
<style type="text/css">
  .form-control::-webkit-inner-spin-button,
  .form-control::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}
.form-control{
    height: 25px;
}
.js-example-basic-single{
    width: 84px;
}
.select2-container--open .select2-dropdown--below{
    width: 185px !important;
}
[v-cloak] 
{ 
    display: none; 
    }
.select2-results__option{
    padding: 0px;
}

</style>
@endsection