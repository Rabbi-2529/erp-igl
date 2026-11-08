@extends('layouts.master')
@section('money_class','menu-open')
@section('accounts_class','menu-open')
@section('bank_class','menu-open')
@section('bank_list','active')
@section('content')
<section class="content">
        <section class="content-header">
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
              
              <h1>
                Bank
              </h1>
        
            </section>
            <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">Bank List</h3>
                    </div>
                   
                    <!-- /.box-header -->
                    <div class="box-body">


                      <table id="example1" class="table table-bordered table-striped table-hover datatable">
                        <thead>
                        <tr>
                          <th>SL</th>
                          <th>Bank Name</th>
                          <th>Branch Name</th>
                         
                          <th>Account No</th>
                           <th>Debit</th>
                          <th>Credit</th>
                         
                          <th>Balance</th>
                          <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($sl=0)
                        @php($debit = 0)
                        @php($totalDebit = 0)
                        @php($credit = 0)  
                        @php($totalCredit = 0) 
                        @php($balance = 0)
                        @php($total = 0)

                        @foreach ($bank_infos as $bank)
                        @php($debit = App\Models\MexAccBankStatement::getTotalDebitByBankId($bank->mex_abi_id))
                        @php($totalDebit += $debit)
                        @php($credit = App\Models\MexAccBankStatement::getTotalCreditByBankId($bank->mex_abi_id))
                        @php($totalCredit += $credit)
                        @php($balance = App\Models\MexAccBankStatement::getAvailableBalanceByBankId($bank->mex_abi_id))
                        @php($total = $total + $balance)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $bank->bank_info->bank_name }}</td>
                            <td>{{ $bank->mex_abi_branch_name }}</td>
                            
                            <td>{{ $bank->mex_abi_account_no }}</td>
                           
                            <td style="text-align: right;">
                              {{number_format($debit,2)}}
                            </td>
                            <td style="text-align: right;">
                             {{number_format($credit,2)}}
                           </td>
                            <td style=" text-align: right;">{{number_format($balance,2)}}</td>
                            <td align="center">
                                <a href="{{ route('money-exchange.accounts.bank-statement-detail', $bank->mex_abi_id) }}" class="action_btn btn-success"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('money-exchange.accounts.update-bank', $bank->mex_abi_id) }}" class="action_btn btn_edit"><i class="fa fa-edit"></i></a>
                                {{-- <a href="{{ route('accounts.delete-bank', $bank->inv_abi_id) }}" onclick="return confirm('Are you sure?')" class="action_btn btn_delete"><i class="fa fa-trash"></i></a> --}}
                            </td>
                          </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                          <tr>
                            <td colspan="4" class="text-right"><b>Total:</b></td>
                            <td class="text-right">{{ number_format($totalDebit,2) }}</td>
                            <td class="text-right">{{ number_format($totalCredit,2) }}</td>
                            <td class="text-right">{{ number_format($total,2) }}</td>
                            <td></td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    <!-- /.box-body -->
                  </div>
                 </section>
@endsection
@section('custom_style')
    <style>
        .action_btn{
            border: 1px solid;
            padding: 5px;
        }
        .btn_show{
            background-color: green;
            color: white;
        }
        .btn_delete{
            background-color: red;
            color: white;
        }
        .btn_edit{
            background-color: cornflowerblue;
            color: white;
        }
    </style>
    @endsection