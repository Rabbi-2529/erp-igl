<?php
namespace App\Http\Controllers\Inventory;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Inv_product_categories;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_bank;
use App\Models\Inv_acc_expense_category;
use App\Models\Inv_acc_bank_statement;
use App\Models\Inv_acc_expense;
use Session;
use DB;
use App\Models\Inv_supplier;
use App\Models\Inv_product_inventory;
use PDF;

class BankAccountController extends Controller
{

	public function addBank()
	{
		$banks = Inv_bank::where('status', 1)->get();

		return view('inventory.accounts.bank.addbank', compact('banks'));
	}
	public function storeBank(Request $request)
	{

		try {
			$company_id=Auth::user()->au_company_id;
			$branch_id=Auth::user()->au_branch_id;
			$userid=Auth::user()->au_id;

			$new_bank = new Inv_acc_bank_info();
			$new_bank->inv_abi_company_id = $company_id;
			$new_bank->inv_abi_branch_id = $branch_id;
			$new_bank->inv_abi_bank_id = $request->bank_id;
			$new_bank->inv_abi_branch_name = $request->branch_name;
			$new_bank->inv_abi_account_name = ucfirst($request->acc_name);
			$new_bank->inv_abi_account_no = $request->acc_no;
			$new_bank->inv_abi_open_date = $request->opendate;
			$new_bank->inv_abi_account_type = 1; //1==bank
			$new_bank->inv_abi_submit_at = Carbon::now();
			$new_bank->inv_abi_submit_by = $userid;

			$new_bank->save();

			return redirect()->back()->with('msg','Bank Added Successfully Completed');

		} catch(\Exception $e) {
			return redirect()->back()->withInput()->with('errmsg','Something Went wrong! '.$e->getMessage());
		}
	}


	public function showBankList() {
		$com = Auth::user()->au_company_id;
$branch = Auth::user()->au_branch_id;
		$bank_infos = Inv_acc_bank_info::where('inv_abi_company_id', $com)
		->where('inv_abi_branch_id', $branch)
								->where('inv_abi_status', 1)
								->get();

		return view('inventory.accounts.bank.banklist', compact('bank_infos'));
	}

	public function deleteBank($id)
	{
		try{
			$com = Auth::user()->au_company_id;
				$branch = Auth::user()->au_branch_id;
			Inv_acc_bank_info::where('inv_abi_company_id', $com)
				            ->where('inv_abi_branch_id', $branch)
							->where('inv_abi_id', $id)
							->where('inv_abi_status', 1)
							->update(['inv_abi_status' => 0]);
			return redirect()->back()->with('msg','Bank Deleted Successfully');
		} catch(\Exception $e) {
			return redirect()->back()->with('errmsg','Something went wrong!');
		}
	}


	public function editBank($id)
	{
		$banks = Inv_bank::where('status', 1)->get();
		$com = Auth::user()->au_company_id;
		$branch = Auth::user()->au_branch_id;
		$bank_info = Inv_acc_bank_info::where('inv_abi_company_id', $com)
			                         ->where('inv_abi_branch_id', $branch)
						->where('inv_abi_id', $id)
						->where('inv_abi_status', 1)
						->first();
		if(!empty($bank_info)) {
			return view('inventory.accounts.bank.editbank', compact('banks', 'bank_info'));

		} else {
			return redirect()->back()->with('errmsg','Invalid Bank!');
		}
	}


	public function updateBank($id, Request $request)
	{
		try {
			$com = Auth::user()->au_company_id;
					$branch = Auth::user()->au_branch_id;
			$userid=Auth::user()->au_id;
			$bank_info = Inv_acc_bank_info::where('inv_abi_company_id', $com)
			                    ->where('inv_abi_branch_id', $branch)
						->where('inv_abi_id', $id)
						->where('inv_abi_status', 1)
						->first();
			if(empty($bank_info)) {
				return redirect()->back()->with('errmsg','Invalid Bank!');
			}

			$banks = Inv_bank::where('status', 1)->get();


			$bank_info->inv_abi_bank_id = $request->bank_id;
			$bank_info->inv_abi_branch_name = $request->branch_name;
			$bank_info->inv_abi_account_name = $request->acc_name;
			$bank_info->inv_abi_account_no = $request->acc_no;
			$bank_info->inv_abi_open_date = $request->opendate;
			$bank_info->inv_abi_updated_at = Carbon::now();
			$bank_info->inv_abi_updated_by = $userid;

			$bank_info->save();

			return redirect()->back()->with('msg','Bank Updated Successfully Completed');

		} catch (\Exception $e) {
			return redirect()->back()->with('errmsg','Something went wrong. '.$e->getMessage());
		}
	}


	public function bankDepositWithdrawShowForm()
	{
    	$banks = Inv_acc_bank_info::where('inv_abi_company_id', Auth::user()->au_company_id)
                ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                ->get();

		$categories=Inv_acc_expense_category::where('inv_acc_exp_cat_company_id',		Auth::user()->au_company_id)
		 ->where('inv_acc_exp_cat_branch_id',	Auth::user()->au_branch_id)
					->where('inv_acc_exp_cat_status',1)
					->orderBy('inv_acc_exp_cat_category_name')->get();

		return view('inventory.accounts.bank.dipositwithdrawbank',compact('banks','categories'));
	}

	public function bankDepositWithdrawStore(Request $request)
	{
		try{
        	if (Inv_acc_bank_info::where('inv_abi_id', $request->bank_id)
                ->where('inv_abi_company_id', Auth::user()->au_company_id)
                ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                ->first())
			{
				$request->validate([
					'paid_amount' => 'required|numeric|min:0',
					'trans_date' => 'required|date_format:d/m/Y', // Validate the date format
					'service_charge' => 'nullable|numeric|min:0',
				]);

				$inv_Acc_Statement=new Inv_acc_bank_statement();
				$inv_Acc_Statement->inv_abs_company_id=Auth::user()->au_company_id;
				$inv_Acc_Statement->inv_abs_branch_id=Auth::user()->au_branch_id;
				//$inv_Acc_Statement->inv_abs_inventory_id=
				// $inv_Acc_Statement->inv_abs_reference_id=$request->expense_id;// balance 3 for credit and 4 for debit
				$inv_Acc_Statement->inv_abs_reference_type= $request->balance_type;
				$inv_Acc_Statement->inv_abs_bank_id=$request->bank_id;

				$inv_Acc_Statement->inv_abs_transaction_date = Carbon::createFromFormat('d/m/Y', $request->trans_date);
				$inv_Acc_Statement->inv_abs_voucher_no=Carbon::now()->format('YmdHis');
				$inv_Acc_Statement->inv_abs_description=$request->reference;
				$inv_Acc_Statement->inv_abs_submit_by=Auth::user()->au_id;
				$inv_Acc_Statement->inv_abs_submit_at=Carbon::now();

				if($request->balance_type==6)
				{
					$inv_Acc_Statement->inv_abs_credit=$request->paid_amount;
					$inv_Acc_Statement->inv_abs_debit=0;
				}
				else
				{

					$inv_Acc_Statement->inv_abs_credit=0;
					$inv_Acc_Statement->inv_abs_debit=$request->paid_amount;
				}

				if($inv_Acc_Statement->save())
				{
					// Optional service charge is cut from the same bank as its own transaction
					if($request->service_charge > 0)
					{
						$this->saveServiceCharge($request->bank_id, $request->service_charge, $inv_Acc_Statement->inv_abs_transaction_date, $inv_Acc_Statement->inv_abs_voucher_no, $request->reference);
					}

					Session::flash('msg','Successfully Added.');
					return redirect()->back();
				}
				else
				{
					Session::flash('errmsg','Failed To Add.Please Try Again.');
					return redirect()->back();
				}


			}
			else
			{
				Session::flash('errmsg','The Bank You Selected is Not Belongs to Your Company.');
				return redirect()->back();
			}
		}
		catch(Exception $err)
		{
			Session::flash('errmsg','Something Goes Wrong.Please Try Again Later');
			return redirect()->back();
		}
	}
	/**
	 * Saves a bank service charge as its own transaction (money out of the given bank).
	 */
	private function saveServiceCharge($bankId, $amount, $transDate, $voucherNo, $description = null)
	{
		$charge = new Inv_acc_bank_statement();
		$charge->inv_abs_company_id = Auth::user()->au_company_id;
		$charge->inv_abs_branch_id = Auth::user()->au_branch_id;
		$charge->inv_abs_reference_type = 11; //11= Bank Service Charge
		$charge->inv_abs_bank_id = $bankId;
		$charge->inv_abs_debit = $amount;
		$charge->inv_abs_credit = 0;
		$charge->inv_abs_service_charge = $amount;
		$charge->inv_abs_transaction_date = $transDate;
		$charge->inv_abs_voucher_no = $voucherNo;
		$charge->inv_abs_description = 'Service charge' . ($description ? ': ' . $description : '');
		$charge->inv_abs_status = 1;
		$charge->inv_abs_submit_by = Auth::user()->au_id;
		$charge->inv_abs_submit_at = Carbon::now();
		$charge->save();

		return $charge;
	}

	public function showContraForm()
	{
		$banks=Inv_acc_bank_info::where('inv_abi_company_id',Auth::user()->au_company_id)
		 ->where('inv_abi_branch_id',Auth::user()->au_branch_id)
		->where('inv_abi_account_type','!=',2)->get();
		$cash=Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID();
		return view('inventory.accounts.voucher.createcontra',compact('banks','cash'));
	}

	public function createContra(Request $request)
	{
		  DB::beginTransaction();
		try {
			$request->validate([
							'amount'=>'required|numeric|min:0',
							'service_charge'=>'nullable|numeric|min:0']);

            // Optional service charge is always cut from the selected (From) bank
            $serviceCharge = (float) ($request->service_charge ?? 0);

            $cashBank = Inv_acc_bank_info::where('inv_abi_status', 1)
                ->where('inv_abi_company_id', Auth::user()->au_company_id)
                 ->where('inv_abi_branch_id',Auth::user()->au_branch_id)
                ->where('inv_abi_account_type', 2) //accouunt type 2=cash
                ->orderBy('inv_abi_id')->first();
            if ($request->pay_type == 1) {
                /*CASH TO BANK*/
                $available_cash_balance = Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID();

                if ($available_cash_balance > $request->amount) {

                    $cashDebit = new Inv_acc_bank_statement();
                    $cashDebit->inv_abs_company_id = Auth::user()->au_company_id;
                      $cashDebit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $cashDebit->inv_abs_reference_id = $request->bank_id;
                    $cashDebit->inv_abs_bank_id = $cashBank->inv_abi_id;
                    $cashDebit->inv_abs_debit = $request->amount;
                    $cashDebit->inv_abs_credit = 0;
                    $cashDebit->inv_abs_transaction_date = $request->trans_date;
                    $cashDebit->inv_abs_description = $request->description;
                    $cashDebit->inv_abs_voucher_no=Carbon::now()->format('YmdHis');
                    $cashDebit->inv_abs_status = 1;
                    $cashDebit->inv_abs_reference_type = 5; //5=contra
                    $cashDebit->inv_abs_submit_at = Carbon::now();
                    $cashDebit->inv_abs_submit_by = Auth::user()->au_id;
                    $cashDebit->save();

                    $bankCredit = new Inv_acc_bank_statement();
                    $bankCredit->inv_abs_company_id = Auth::user()->au_company_id;
                        $bankCredit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $bankCredit->inv_abs_reference_id = $cashBank->inv_abi_id;
                    $bankCredit->inv_abs_bank_id = $request->bank_id;
                    $bankCredit->inv_abs_debit = 0;
                    $bankCredit->inv_abs_credit = $request->amount;
                    $bankCredit->inv_abs_transaction_date = $request->trans_date;
                    $bankCredit->inv_abs_description = $request->description;
                    $bankCredit->inv_abs_status = 1;
                    $bankCredit->inv_abs_reference_type = 5; //3=bank
                    $bankCredit->inv_abs_submit_at = Carbon::now();
                    $bankCredit->inv_abs_submit_by = Auth::user()->au_id;
                    $bankCredit->save();

                    $cashDebit->inv_abs_contra_transaction_id = $bankCredit->inv_abs_id;
                    $cashDebit->save();


                    $bankCredit->inv_abs_contra_transaction_id = $cashDebit->inv_abs_id;
                    $bankCredit->save();

                    if ($serviceCharge > 0) {
                        $this->saveServiceCharge($request->bank_id, $serviceCharge, $request->trans_date, $cashDebit->inv_abs_voucher_no, $request->description);
                    }


                } else {

                    Session::flash('errmsg', 'Amount Can\'t be more then available cash amount');
                    return redirect()->route('accounts.create-contra')->withInput($request->all());
                }


            } elseif ($request->pay_type == 2) {
                /*BANK TO CASH*/
                $available_bank_balance = Inv_acc_bank_info::singleBankTotalBalance($request->bank_id);
                if ($available_bank_balance > $request->amount + $serviceCharge) {

                    $bankDebit = new Inv_acc_bank_statement();
                    $bankDebit->inv_abs_company_id = Auth::user()->au_company_id;
                      $bankDebit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $bankDebit->inv_abs_reference_id = $cashBank->inv_abi_id;
                    $bankDebit->inv_abs_bank_id = $request->bank_id;
                    $bankDebit->inv_abs_debit = $request->amount;
                    $bankDebit->inv_abs_credit = 0;
                    $bankDebit->inv_abs_transaction_date = $request->trans_date;
                    $bankDebit->inv_abs_description = $request->description;
                    $bankDebit->inv_abs_voucher_no = Carbon::now()->format('YmdHis');
                    $bankDebit->inv_abs_status = 1;
                    $bankDebit->inv_abs_reference_type = 5;//3=bank
                    $bankDebit->inv_abs_submit_at = Carbon::now();
                    $bankDebit->inv_abs_submit_by = Auth::user()->au_id;
                    $bankDebit->save();

                    $cashCredit = new Inv_acc_bank_statement();
                    $cashCredit->inv_abs_company_id = Auth::user()->au_company_id;
                       $cashCredit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $cashCredit->inv_abs_reference_id= $request->bank_id;
                    $cashCredit->inv_abs_bank_id = $cashBank->inv_abi_id;
                    $cashCredit->inv_abs_debit = 0;
                    $cashCredit->inv_abs_credit = $request->amount;
                    $cashCredit->inv_abs_transaction_date = $request->trans_date;
                    $cashCredit->inv_abs_description = $request->description;
                    $cashCredit->inv_abs_status = 1;
                    $cashCredit->inv_abs_reference_type = 5;//5=contra
                    $cashCredit->inv_abs_submit_at = Carbon::now();
                    $cashCredit->inv_abs_submit_by = Auth::user()->au_id;
                    $cashCredit->save();

                    $bankDebit->inv_abs_contra_transaction_id = $cashCredit->inv_abs_id;
                    $bankDebit->save();
                    $cashCredit->inv_abs_contra_transaction_id = $bankDebit->inv_abs_id;
                    $cashCredit->save();

                    if ($serviceCharge > 0) {
                        $this->saveServiceCharge($request->bank_id, $serviceCharge, $request->trans_date, $bankDebit->inv_abs_voucher_no, $request->description);
                    }


                } else {

                    Session::flash('errmsg', 'Amount Can\'t be more then available Bank amount');
                    return redirect()->route('accounts.create-contra')->withInput($request->all());
                }


            } elseif ($request->pay_type == 3) {
                /*BANK TO BANK*/
                $fromBank = Inv_acc_bank_info::where('inv_abi_id', $request->bank_id)
                    ->where('inv_abi_company_id', Auth::user()->au_company_id)
                    ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                    ->where('inv_abi_account_type', '!=', 2)
                    ->first();
                $toBank = Inv_acc_bank_info::where('inv_abi_id', $request->to_bank_id)
                    ->where('inv_abi_company_id', Auth::user()->au_company_id)
                    ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
                    ->where('inv_abi_account_type', '!=', 2)
                    ->first();

                if (empty($fromBank) || empty($toBank) || $fromBank->inv_abi_id == $toBank->inv_abi_id) {
                    DB::rollBack();
                    Session::flash('errmsg', 'Select a From Bank and a different To Bank.');
                    return redirect()->route('accounts.create-contra')->withInput($request->all());
                }

                $available_bank_balance = Inv_acc_bank_statement::getAvailableBalanceByBankId($fromBank->inv_abi_id);
                if ($available_bank_balance >= $request->amount + $serviceCharge) {
                    $voucherNo = Carbon::now()->format('YmdHis');

                    $bankDebit = new Inv_acc_bank_statement();
                    $bankDebit->inv_abs_company_id = Auth::user()->au_company_id;
                    $bankDebit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $bankDebit->inv_abs_reference_id = $toBank->inv_abi_id;
                    $bankDebit->inv_abs_bank_id = $fromBank->inv_abi_id;
                    $bankDebit->inv_abs_debit = $request->amount;
                    $bankDebit->inv_abs_credit = 0;
                    $bankDebit->inv_abs_transaction_date = $request->trans_date;
                    $bankDebit->inv_abs_description = $request->description;
                    $bankDebit->inv_abs_voucher_no = $voucherNo;
                    $bankDebit->inv_abs_status = 1;
                    $bankDebit->inv_abs_reference_type = 5; //5=contra
                    $bankDebit->inv_abs_submit_at = Carbon::now();
                    $bankDebit->inv_abs_submit_by = Auth::user()->au_id;
                    $bankDebit->save();

                    $bankCredit = new Inv_acc_bank_statement();
                    $bankCredit->inv_abs_company_id = Auth::user()->au_company_id;
                    $bankCredit->inv_abs_branch_id = Auth::user()->au_branch_id;
                    $bankCredit->inv_abs_reference_id = $fromBank->inv_abi_id;
                    $bankCredit->inv_abs_bank_id = $toBank->inv_abi_id;
                    $bankCredit->inv_abs_debit = 0;
                    $bankCredit->inv_abs_credit = $request->amount;
                    $bankCredit->inv_abs_transaction_date = $request->trans_date;
                    $bankCredit->inv_abs_description = $request->description;
                    $bankCredit->inv_abs_voucher_no = $voucherNo;
                    $bankCredit->inv_abs_contra_transaction_id = $bankDebit->inv_abs_id;
                    $bankCredit->inv_abs_status = 1;
                    $bankCredit->inv_abs_reference_type = 5; //5=contra
                    $bankCredit->inv_abs_submit_at = Carbon::now();
                    $bankCredit->inv_abs_submit_by = Auth::user()->au_id;
                    $bankCredit->save();

                    $bankDebit->inv_abs_contra_transaction_id = $bankCredit->inv_abs_id;
                    $bankDebit->save();

                    if ($serviceCharge > 0) {
                        $this->saveServiceCharge($fromBank->inv_abi_id, $serviceCharge, $request->trans_date, $voucherNo, $request->description);
                    }

                } else {

                    Session::flash('errmsg', 'Amount and service charge can\'t be more than the From Bank balance (' . number_format($available_bank_balance, 2) . ')');
                    return redirect()->route('accounts.create-contra')->withInput($request->all());
                }


            } else {

                session()->flash('errmsg', 'Invalid Payment Method');
                return redirect()->route('accounts.create-contra')->withInput($request->all());
            }
        } catch (\Exception $e) {

            DB::rollBack();
            session()->flash('errmsg', 'Something Went Wrong'.$e->getMessage());
            return redirect()->route('accounts.create-contra')->withInput($request->all());
        }

        DB::commit();


        session()->flash('msg', 'Successfully Added Contra Voucher');
        return redirect()->back();
	}
	public function showContraList()
	{
		$contras=Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)
		 ->where('inv_abs_branch_id',Auth::user()->au_branch_id)
				->where('inv_abs_reference_type',5)->get();
				return view('inventory.accounts.voucher.contralist',compact('contras'));
	}
public function editContra($id)
{
    $contra = Inv_acc_bank_statement::where('inv_abs_id', $id)
        ->where('inv_abs_company_id', Auth::user()->au_company_id)
        ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
        ->where('inv_abs_reference_type', 5)
        ->firstOrFail();

    $pairedTransaction = Inv_acc_bank_statement::where('inv_abs_id', $contra->inv_abs_contra_transaction_id)
        ->first();

    $banks = Inv_acc_bank_info::where('inv_abi_company_id', Auth::user()->au_company_id)
        ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
        ->where('inv_abi_account_type', '!=', 2)
        ->get();

    $cashBank = Inv_acc_bank_info::where('inv_abi_status', 1)
        ->where('inv_abi_company_id', Auth::user()->au_company_id)
        ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
        ->where('inv_abi_account_type', 2)
        ->orderBy('inv_abi_id')->first();

	$cash=Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID();
    // Determine pay type (1 = cash to bank, 2 = bank to cash)
    $payType = ($contra->inv_abs_debit > 0 && $contra->inv_abs_bank_id == $cashBank->inv_abi_id) ? 1 : 2;

    // 3 = bank to bank: neither side is the cash account
    if ($pairedTransaction
        && $contra->inv_abs_bank_id != $cashBank->inv_abi_id
        && $pairedTransaction->inv_abs_bank_id != $cashBank->inv_abi_id) {
        $payType = 3;
    }

    return view('inventory.accounts.voucher.editcontra', compact('contra', 'pairedTransaction', 'cash','banks', 'payType'));
}

public function updateContra(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        $originalContra = Inv_acc_bank_statement::where('inv_abs_id', $id)
            ->where('inv_abs_company_id', Auth::user()->au_company_id)
            ->where('inv_abs_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abs_reference_type', 5)
            ->firstOrFail();

        $pairedTransaction = Inv_acc_bank_statement::where('inv_abs_id', $originalContra->inv_abs_contra_transaction_id)
            ->firstOrFail();

        $cashBank = Inv_acc_bank_info::where('inv_abi_status', 1)
            ->where('inv_abi_company_id', Auth::user()->au_company_id)
            ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abi_account_type', 2)
            ->orderBy('inv_abi_id')->first();

        if ($request->pay_type == 1) {
            // Cash to Bank update
            $available_cash_balance = Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID() + $originalContra->inv_abs_debit;

            if ($available_cash_balance >= $request->amount) {
                // Update original transaction (cash debit)
                $originalContra->update([
                    'inv_abs_debit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);

                // Update paired transaction (bank credit)
                $pairedTransaction->update([
                    'inv_abs_credit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);
            } else {
                Session::flash('errmsg', 'Amount Can\'t be more than available cash amount');
                return redirect()->back()->withInput($request->all());
            }
        } elseif ($request->pay_type == 2) {
            // Bank to Cash update
            $available_bank_balance = Inv_acc_bank_info::singleBankTotalBalance($request->bank_id) + $originalContra->inv_abs_debit;

            if ($available_bank_balance >= $request->amount) {
                // Update original transaction (bank debit)
                $originalContra->update([
                    'inv_abs_debit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);

                // Update paired transaction (cash credit)
                $pairedTransaction->update([
                    'inv_abs_credit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);
            } else {
                Session::flash('errmsg', 'Amount Can\'t be more than available bank amount');
                return redirect()->back()->withInput($request->all());
            }
        } elseif ($request->pay_type == 3) {
            // Bank to Bank update: amount, date and description (the banks stay as they are)
            $debitSide  = $originalContra->inv_abs_debit > 0 ? $originalContra : $pairedTransaction;
            $creditSide = $originalContra->inv_abs_debit > 0 ? $pairedTransaction : $originalContra;

            $available_bank_balance = Inv_acc_bank_statement::getAvailableBalanceByBankId($debitSide->inv_abs_bank_id) + $debitSide->inv_abs_debit;

            if ($available_bank_balance >= $request->amount) {
                $debitSide->update([
                    'inv_abs_debit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);

                $creditSide->update([
                    'inv_abs_credit' => $request->amount,
                    'inv_abs_transaction_date' => $request->trans_date,
                    'inv_abs_description' => $request->description,
                    'inv_abs_updated_at' => Carbon::now(),
                    'inv_abs_updated_by' => Auth::user()->au_id
                ]);
            } else {
                Session::flash('errmsg', 'Amount Can\'t be more than available From Bank amount');
                return redirect()->back()->withInput($request->all());
            }
        } else {
            session()->flash('errmsg', 'Invalid Payment Method');
            return redirect()->back()->withInput($request->all());
        }

        DB::commit();
        session()->flash('msg', 'Contra Voucher Updated Successfully');
        return redirect()->route('accounts.contra-list');

    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('errmsg', 'Something Went Wrong: '.$e->getMessage());
        return redirect()->back()->withInput($request->all());
    }
}





	public function showGeneralLedgerForm()
	{
		 $inv_Suppliers=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
				->where('inv_sup_status',1)->get();

		$startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
		$endDate = Carbon::now()->format('Y-m-d');

		return view('inventory.accounts.voucher.general_ledger',compact('inv_Suppliers', 'startDate', 'endDate'));
	}
	public function showGeneralLedgerData(Request $request)
	{
		$request->validate([
			'supplier_id' => 'required|integer',
			'start_date'  => 'required|date_format:Y-m-d',
			'end_date'    => 'required|date_format:Y-m-d|after_or_equal:start_date',
		]);

		$inv_Suppliers=Inv_supplier::where('inv_sup_com_id',Auth::user()->au_company_id)
			->where('inv_sup_status',1)->get();

		$ledger = $this->buildSupplierLedger($request->supplier_id, $request->start_date, $request->end_date);
		if (!$ledger) {
			return redirect()->route('accounts.general_ledger')->withErrors(['supplier_id' => 'Select a supplier of your company.']);
		}

		$startDate = $request->start_date;
		$endDate = $request->end_date;

		return view('inventory.accounts.voucher.general_ledger',compact('ledger','inv_Suppliers', 'startDate', 'endDate'));
	}

	/**
	 * Supplier ledger for a period: opening balance, one line per invoice / voucher
	 * (an invoice's product lines are added together) and a running balance.
	 * Balance = credit - debit, as everywhere else: below zero means we owe the supplier.
	 */
	private function buildSupplierLedger($supplierId, $startDate, $endDate)
	{
		$com = Auth::user()->au_company_id;
		$branch = Auth::user()->au_branch_id;

		$supplier = Inv_supplier::where('inv_sup_id', $supplierId)->where('inv_sup_com_id', $com)->first();
		if (!$supplier) {
			return null;
		}

		// Opening balance rows are saved without a branch, so include those too (same as the purchase invoice)
		$rows = function () use ($com, $branch, $supplierId) {
			return Inv_product_inventory::where('inv_pro_inv_com_id', $com)
				->where(function ($q) use ($branch) {
					$q->where('inv_pro_inv_branch_id', $branch)->orWhereNull('inv_pro_inv_branch_id');
				})
				->where('inv_pro_inv_status', 1)
				->where('inv_pro_inv_deal_type', 1)
				->where('inv_pro_inv_party_id', $supplierId);
		};

		$openingBalance = $rows()->where('inv_pro_inv_issue_date', '<', $startDate)->sum('inv_pro_inv_credit')
			- $rows()->where('inv_pro_inv_issue_date', '<', $startDate)->sum('inv_pro_inv_debit');

		$typeLabels = [
			1 => 'Purchase', 2 => 'Purchase Return', 3 => 'Opening / Adjustment', 4 => 'Payment',
			5 => 'Payment Collection', 6 => 'Discount', 15 => 'Cash In', 16 => 'Cash Out',
		];

		$balance = $openingBalance;
		$lines = $rows()
			->where('inv_pro_inv_issue_date', '>=', $startDate)
			->where('inv_pro_inv_issue_date', '<=', $endDate)
			->orderBy('inv_pro_inv_issue_date')
			->orderBy('inv_pro_inv_id')
			->get()
			->groupBy(function ($row) {
				return $row->inv_pro_inv_invoice_no ?: 'row-' . $row->inv_pro_inv_id;
			})
			->map(function ($group) use (&$balance, $typeLabels) {
				$first = $group->first();
				$debit = $group->sum('inv_pro_inv_debit');
				$credit = $group->sum('inv_pro_inv_credit');
				$balance += $credit - $debit;

				return (object) [
					'date'        => Carbon::parse($first->inv_pro_inv_issue_date)->format('Y-m-d'),
					'invoice'     => $first->inv_pro_inv_invoice_no,
					'type'        => $group->pluck('inv_pro_inv_tran_type')->unique()->map(function ($type) use ($typeLabels) {
						return $typeLabels[$type] ?? 'Other';
					})->implode(' + '),
					'description' => $group->pluck('inv_pro_inv_tran_desc')->filter()->unique()->implode(', '),
					'items'       => $group->count(),
					'debit'       => $debit,
					'credit'      => $credit,
					'balance'     => $balance,
				];
			})
			->values();

		return (object) [
			'supplier'       => $supplier,
			'startDate'      => $startDate,
			'endDate'        => $endDate,
			'openingBalance' => $openingBalance,
			'lines'          => $lines,
			'totalDebit'     => $lines->sum('debit'),
			'totalCredit'    => $lines->sum('credit'),
			'closingBalance' => $balance,
		];
	}

  public function ajaxLoadBankBalance(Request $request)
    {
        $bank_id = $request->bank_id;
        
        if (empty($bank_id)) {
            return '<span class="text-danger">Select a Bank</span>';
        }

        $companyId = Auth::user()->au_company_id;
        $branchId = Auth::user()->au_branch_id;

        // Calculate Total Credit (Money IN)
        $totalCredit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)
                        ->where('inv_abs_company_id', $companyId)
                        ->where('inv_abs_branch_id', $branchId)
                        ->where('inv_abs_status', 1) 
                        ->sum('inv_abs_credit');

        // Calculate Total Debit (Money OUT, including supplier payments)
        $totalDebit = Inv_acc_bank_statement::where('inv_abs_bank_id', $bank_id)
                        ->where('inv_abs_company_id', $companyId)
                        ->where('inv_abs_branch_id', $branchId)
                        ->where('inv_abs_status', 1) 
                        ->sum('inv_abs_debit');

        // Balance = Credit - Debit (Automatically reduces on payment)
        $balance = $totalCredit - $totalDebit;

        // Format nicely and return
        $balanceFormatted = number_format($balance, 2);

        return '<span style="color: ' . ($balance >= 0 ? 'green' : 'red') . '; font-weight: bold;">Balance: ' . $balanceFormatted . '</span>';
    }
	public function ajaxLoadCustomerBalance(Request $request)
	{

            $companyId = Auth::user()->au_company_id;
            $branchId = Auth::user()->au_branch_id;
            
            $creditSum = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                            ->where('inv_pro_inv_isproject', 0)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->where('inv_pro_inv_com_id', $companyId)
                            ->where('inv_pro_inv_branch_id', $branchId)
                            ->sum('inv_pro_inv_credit');
            
            $debitSum = Inv_product_inventory::where('inv_pro_inv_party_id', $request->customer_id)
                            ->where('inv_pro_inv_isproject', 0)
                            ->where('inv_pro_inv_deal_type', 2)
                            ->where('inv_pro_inv_com_id', $companyId)
                            ->where('inv_pro_inv_branch_id', $branchId)
                            ->sum('inv_pro_inv_debit');
            
            $customerBalance = $creditSum - $debitSum;


			 return view('pages.ajax.available_balance_customer',compact('customerBalance'));
	}
	public function ajaxLoadSupplierBalance(Request $request)
	{

			 $supplierBalance=(Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_com_id',Auth::user()->au_company_id)->sum('inv_pro_inv_credit'))-(Inv_product_inventory::where('inv_pro_inv_party_id',$request->supplier_id)->where('inv_pro_inv_deal_type',1)->where('inv_pro_inv_com_id',Auth::user()->au_company_id)->sum('inv_pro_inv_debit'));

			 return view('pages.ajax.available_balance_supplier',compact('supplierBalance'));
	}


	public function bankStatementDetails(Request $request,$id)
	{
		$bank_info = Inv_acc_bank_info::where('inv_abi_company_id',Auth::user()->au_company_id)
		 ->where('inv_abi_branch_id',Auth::user()->au_branch_id)
		
			->where('inv_abi_id', $id)
			->first();
		if(empty($bank_info)) {
			return redirect()->back()->with(['errmsg' => 'Invalid Bank']);
		}
		$statements = null;
		if ($request->has('searchbtn')) {
            $request->validate([
                   'start_date' => 'required',
                   'end_date' => 'required',
               ]);
               $start_date = $request->start_date;
               $end_date = $request->end_date;

       }else {
           $start_date = Carbon::now()->format('Y-m-d');
           $end_date = Carbon::now()->format('Y-m-d');
       }
		$statements = Inv_acc_bank_statement::where('inv_abs_company_id',Auth::user()->au_company_id)
		 ->where('inv_abs_branch_id',Auth::user()->au_branch_id)
		
											->where('inv_abs_bank_id', $id)
											->where('inv_abs_transaction_date','>=',$start_date)
											->where('inv_abs_transaction_date','<=',$end_date)
											->get();

		return view('inventory.accounts.bank.statement-details', compact('start_date','end_date','bank_info', 'statements'));
	}

public function expensesVoucherForm()
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $expenseCategories = Inv_acc_expense_category::where('inv_acc_exp_cat_company_id', $companyId)
                            ->where('inv_acc_exp_cat_branch_id', $branchId)
                            ->where('inv_acc_exp_cat_status', 1)
                            ->orderBy('inv_acc_exp_cat_category_name')
                            ->get();

    $cash = Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID();

    return view('inventory.accounts.voucher.expense_voucher', compact('expenseCategories', 'cash'));
}

	public function expensesVoucherStore(Request $request)
	{
		try{
			 $cashBankid=Inv_acc_bank_statement::getCashBankIdByCompanyID();
			$cashBalance=Inv_acc_bank_statement::getAvailableCashBalanceByCompanyID();
			if($cashBalance>=$request->paid_amount)
			{
				$request->validate([
                    'paid_amount' => 'required|numeric|min:0',
                    'trans_date' => 'required|date_format:d/m/Y',
                ]);

				$inv_Acc_Statement=new Inv_acc_bank_statement();
				$inv_Acc_Statement->inv_abs_company_id=Auth::user()->au_company_id;
				$inv_Acc_Statement->inv_abs_branch_id=Auth::user()->au_branch_id;
				$inv_Acc_Statement->inv_abs_reference_id=$request->expense;
				$inv_Acc_Statement->inv_abs_reference_type= 3; // for expenses will credit
				$inv_Acc_Statement->inv_abs_bank_id=$cashBankid;

				$inv_Acc_Statement->inv_abs_transaction_date = Carbon::createFromFormat('d/m/Y', $request->trans_date)->format('Y-m-d');

				$inv_Acc_Statement->inv_abs_voucher_no=Carbon::now()->format('YmdHis');
				$inv_Acc_Statement->inv_abs_description=$request->reference;
				$inv_Acc_Statement->inv_abs_submit_by=Auth::user()->au_id;
				$inv_Acc_Statement->inv_abs_submit_at=Carbon::now();


				$inv_Acc_Statement->inv_abs_credit=0;
				$inv_Acc_Statement->inv_abs_debit=$request->paid_amount;

				if($inv_Acc_Statement->save())
				{
					Session::flash('msg','Successfully Added.');
					return redirect()->back();
				}
				else
				{
					Session::flash('errmsg','Failed To Add.Please Try Again.');
					return redirect()->back()->withInput();
				}
			}
			else
			{
				Session::flash('errmsg','Insufficent Balance Found.Your Current Cash Balance is '.$cashBalance);
					return redirect()->back()->withInput();
			}


		}
		catch(Exception $err)
		{
			Session::flash('errmsg','Something Goes Wrong.Please Try Again Later');
			return redirect()->back();
		}
	}
        // Show list of expense vouchers
 // List all expense vouchers
public function expensesVoucherList()
{
    $vouchers = \DB::table('inv_acc_bank_statements')
        ->select(
            'inv_acc_bank_statements.inv_abs_id as id',
            'inv_acc_bank_statements.inv_abs_voucher_no',
            'inv_acc_bank_statements.inv_abs_description',
            'inv_acc_bank_statements.inv_abs_transaction_date',
            'inv_acc_bank_statements.inv_abs_debit',
            'inv_acc_bank_statements.inv_abs_bank_id',
            'inv_acc_bank_statements.inv_abs_submit_at',
            'inv_acc_expenses.inv_acc_exp_expense_name as expense_name',
            'inv_acc_expense_categories.inv_acc_exp_cat_category_name as category_name'
        )
        ->leftJoin('inv_acc_expenses', 'inv_acc_bank_statements.inv_abs_reference_id', '=', 'inv_acc_expenses.inv_acc_exp_id')
        ->leftJoin('inv_acc_expense_categories', 'inv_acc_expenses.inv_acc_exp_category_id', '=', 'inv_acc_expense_categories.inv_acc_exp_cat_category_id')
        ->where('inv_acc_bank_statements.inv_abs_company_id', Auth::user()->au_company_id)
        ->where('inv_acc_bank_statements.inv_abs_branch_id', Auth::user()->au_branch_id)
        ->where('inv_acc_bank_statements.inv_abs_status', 1)
        ->where('inv_acc_bank_statements.inv_abs_reference_type', 3) // only expenses
        ->orderBy('inv_acc_bank_statements.inv_abs_transaction_date', 'desc') // most recent first
        ->orderBy('inv_acc_bank_statements.inv_abs_id', 'desc')
        ->get();

    // Bank / cash account each expense was paid from
    $banks = Inv_acc_bank_info::with('bank_info')
        ->where('inv_abi_company_id', Auth::user()->au_company_id)
        ->where('inv_abi_branch_id', Auth::user()->au_branch_id)
        ->get()
        ->keyBy('inv_abi_id');

    return view('inventory.accounts.voucher.expense_voucher_list', compact('vouchers', 'banks'));
}


public function expensesVoucherEdit($id)
{
    $voucher = Inv_acc_bank_statement::findOrFail($id);

    $companyId = Auth::user()->au_company_id;
    $branchId  = Auth::user()->au_branch_id;

    // Get all active categories
    $expenseCategories = Inv_acc_expense_category::where('inv_acc_exp_cat_company_id', $companyId)
        ->where('inv_acc_exp_cat_branch_id', $branchId)
        ->where('inv_acc_exp_cat_status', 1)
        ->orderBy('inv_acc_exp_cat_category_name')
        ->get();

    // Get selected expense
    $selectedExpense = Inv_acc_expense::where('inv_acc_exp_id', $voucher->inv_abs_reference_id)->first();
    $selectedCategoryId = $selectedExpense ? $selectedExpense->inv_acc_exp_category_id : null;

    // Get all expenses under selected category
    $expenses = $selectedCategoryId
        ? Inv_acc_expense::where('inv_acc_exp_category_id', $selectedCategoryId)
            ->where('inv_acc_exp_company_id', $companyId)
            ->where('inv_acc_exp_branch_id', $branchId)
            ->where('inv_acc_exp_status', 1)
            ->get()
        : collect();

    return view('inventory.accounts.voucher.expense_voucher_edit', compact(
        'voucher',
        'expenseCategories',
        'expenses',
        'selectedCategoryId'
    ));
}

public function expensesVoucherUpdate(Request $request, $id)
{
    $voucher = Inv_acc_bank_statement::findOrFail($id);

    $request->validate([
        'paid_amount' => 'required|numeric|min:0',
        'trans_date'  => 'required|date_format:d/m/Y',
        'expense_id'  => 'required|integer'
    ]);

    $voucher->inv_abs_reference_id     = $request->expense_id;
    $voucher->inv_abs_transaction_date = Carbon::createFromFormat('d/m/Y', $request->trans_date)->format('Y-m-d');
    $voucher->inv_abs_description      = $request->reference;
    $voucher->inv_abs_debit            = $request->paid_amount;
    $voucher->inv_abs_submit_by        = Auth::user()->au_id;
    $voucher->inv_abs_submit_at        = Carbon::now();

    if ($voucher->save()) {
        Session::flash('msg', 'Voucher Updated Successfully.');
        return redirect()->route('accounts.expenses-voucher-list');
    } else {
        Session::flash('errmsg', 'Failed To Update. Please Try Again.');
        return redirect()->back()->withInput();
    }
}
 	//=========================17-11-19 ====================


	 public function downLoadGeneralLedger(Request $request)
	 {
		 $request->validate([
			 'supid' => 'required|integer',
			 'sdate' => 'required|date_format:Y-m-d',
			 'edate' => 'required|date_format:Y-m-d|after_or_equal:sdate',
		 ]);

		 $ledger = $this->buildSupplierLedger($request->supid, $request->sdate, $request->edate);
		 if (!$ledger) {
			 abort(404);
		 }

		 if($request->has('print_btn'))
		 {
			 return view('inventory.accounts.voucher.general_ledger_print',compact('ledger'));
		 }
		 else
		 {
			 $pdf = PDF::loadView('inventory.accounts.voucher.general_ledger_download',compact('ledger'));
			 return $pdf->download("general_ledger_".Auth::user()->au_company_id."_".Carbon::now()->format('d_m_Y').".pdf");
		 }
	 }

	 //======================18-11-19 =======================
	 public function showAccountStatementForm()
	 {
		 // Opens on the current month
		 return $this->renderAccountStatement(Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
	 }

public function showAccountStatementData(Request $request)
{
    $request->validate([
        'start_date' => 'required|date_format:Y-m-d',
        'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
    ]);

    return $this->renderAccountStatement($request->start_date, $request->end_date);
}

private function renderAccountStatement($startDate, $endDate)
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $statements = Inv_acc_bank_statement::where('inv_abs_company_id', $companyId)
                    ->where('inv_abs_branch_id', $branchId)
                    ->where('inv_abs_status', 1)
                    ->where('inv_abs_transaction_date', '>=', $startDate)
                    ->where('inv_abs_transaction_date', '<=', $endDate)
                    ->orderBy('inv_abs_transaction_date')
                    ->orderBy('inv_abs_id')
                    ->get();

    $banks = Inv_acc_bank_info::with('bank_info')
                    ->where('inv_abi_company_id', $companyId)
                    ->where('inv_abi_branch_id', $branchId)
                    ->get()
                    ->keyBy('inv_abi_id');

    $openingBalance = Inv_acc_bank_statement::getOpenningBalance($startDate);

    return view('inventory.accounts.bank.account_statement', compact('statements', 'banks', 'openingBalance', 'startDate', 'endDate'));
}

public function downloadAccountStatement(Request $request)
{
    $companyId = Auth::user()->au_company_id;
    $branchId = Auth::user()->au_branch_id;

    $statements = Inv_acc_bank_statement::where('inv_abs_company_id', $companyId)
                    ->where('inv_abs_branch_id', $branchId)
                    ->where('inv_abs_status', 1)
                    ->where('inv_abs_transaction_date', '>=', $request->sdate)
                    ->where('inv_abs_transaction_date', '<=', $request->edate)
                    ->orderBy('inv_abs_transaction_date')
                    ->orderBy('inv_abs_id')
                    ->get();

    if ($request->has('download_btn')) {
        $pdf = PDF::loadView('inventory.accounts.bank.account_statement_download', compact('statements'));
        return $pdf->download("account_statement_" . $companyId . "_" . Carbon::now()->format('d_m_Y') . ".pdf");
    }

    if ($request->has('print_btn')) {
        return view('inventory.accounts.bank.account_statement_print', compact('statements'));
    }
}

}
