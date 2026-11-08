<?php

namespace App\Http\Controllers\Lc;

use Auth;
use App\Models\Inv_acc_bank_info;
use App\Models\Inv_product_detail;
use App\Models\Lc_letter;

/**
 * Shared lookups for the LC Management controllers (everything is per company + branch).
 */
trait LcHelpers
{
    protected function com()
    {
        return Auth::user()->au_company_id;
    }

    protected function branch()
    {
        return Auth::user()->au_branch_id;
    }

    /** Active bank and cash accounts as [id => "Bank name (account no)"] */
    protected function bankOptions($banksOnly = false)
    {
        return Inv_acc_bank_info::with('bank_info')
            ->where('inv_abi_company_id', $this->com())
            ->where('inv_abi_branch_id', $this->branch())
            ->where('inv_abi_status', 1)
            ->when($banksOnly, function ($q) {
                $q->where('inv_abi_account_type', '!=', 2); // 2 = cash
            })
            ->get()
            ->mapWithKeys(function ($bank) {
                return [$bank->inv_abi_id => ($bank->bank_info->bank_name ?? $bank->inv_abi_account_name) . ' (' . $bank->inv_abi_account_no . ')'];
            });
    }

    /** Product details of this branch as [id => "Product (Type)"], with the warranty flag */
    protected function productOptions()
    {
        return Inv_product_detail::with(['product', 'product.type_info', 'type_info'])
            ->where('inv_pro_det_com_id', $this->com())
            ->where('inv_pro_det_branch_id', $this->branch())
            ->where('inv_pro_det_status', 1)
            ->get()
            ->map(function ($detail) {
                $type = $detail->type_info->inv_pro_type_name ?? ($detail->product->type_info->inv_pro_type_name ?? null);
                return (object) [
                    'id'       => $detail->inv_pro_det_id,
                    'name'     => ($detail->product->inv_pro_name ?? 'N/A') . ($type ? ' (' . $type . ')' : ''),
                    'warranty' => $detail->inv_pro_det_pro_warranty != 0,
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /** An LC of this company / branch, or 404 */
    protected function findLc($id)
    {
        return Lc_letter::with(['file.supplier', 'file.items.productDetail.product', 'bank.bank_info'])
            ->where('lc_id', $id)
            ->where('lc_company_id', $this->com())
            ->where('lc_branch_id', $this->branch())
            ->firstOrFail();
    }

    /** All LCs of this company / branch for the pickers, newest first */
    protected function lcList()
    {
        return Lc_letter::with('file.supplier')
            ->where('lc_company_id', $this->com())
            ->where('lc_branch_id', $this->branch())
            ->orderBy('lc_id', 'desc')
            ->get();
    }
}
