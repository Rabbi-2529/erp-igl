<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_product_detail extends Model
{
    //
    protected $table = 'inv_product_details';
    protected $fillable =
    [
        'inv_pro_det_id',
        'inv_pro_det_com_id',
        'inv_pro_det_branch_id',

        'inv_pro_det_type_id',
        'inv_pro_det_sup',
        'inv_pro_det_pro_id',
        'inv_pro_det_buy_price',
        'inv_pro_det_sell_price',
        'inv_pro_det_pro_warranty',
       
        'inv_pro_det_available_qty',
        'inv_pro_det_short_qty',
        'inv_pro_det_status',
        'inv_pro_det_submit_by',
        'inv_pro_det_submit_at',
        'inv_pro_det_update_by',
        'inv_pro_det_update_at',
    ];
    protected $primaryKey = 'inv_pro_det_id';
    public $incrementing = true;
    public $timestamps = false;

    public function type_info(){
        return $this->belongsTo('App\Models\Inv_product_type','inv_pro_det_type_id','inv_pro_type_id');
    }

    public function submit_type(){
        return $this->belongsTo('App\Models\Admin_user','inv_pro_det_submit_by','au_id');
    }
public function product()
{
    return $this->belongsTo('App\Models\Inv_product', 'inv_pro_det_pro_id', 'inv_pro_id');
}
     public static function suppliers_info($id){
        $product = Inv_product_detail::where('inv_pro_det_id',$id)
                                     ->first();
        $supplier_id = explode('-',$product->inv_pro_det_sup);

        if (!empty($supplier_id)) {
            $suppliers = Inv_supplier::whereIn('inv_sup_id',$supplier_id)->get();
            if (count($suppliers) <= 0) {
                return null;
            }

            return $suppliers;
        }else {
            return null;
        }
    }


    public static function get_type_name($pro_id)
    {
        $product = Inv_product_detail::where('inv_pro_det_id', $pro_id)->first();
        if(!empty($product)) {
            return Inv_product_type::select('inv_pro_type_name')->where('inv_pro_type_id', $product->inv_pro_det_type_id)->first()->inv_pro_type_name;
        }
        return '';
    }


    public static function get_total_buy_qty($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 1)
                ->sum('inv_pro_inv_total_qty');

            return $total_buy_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_buy_return_qty($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 2)
                ->sum('inv_pro_inv_qty');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_sell_qty($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_tran_type', 1)
                ->sum('inv_pro_inv_qty');

            return $total_buy_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_sell_return_qty($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_tran_type', 2)
                ->sum('inv_pro_inv_qty');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_buy_price($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 1)
                ->sum('inv_pro_inv_debit');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_sell_price($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_tran_type', 1)
                ->sum('inv_pro_inv_debit');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_buy_return_price($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 1)
                ->where('inv_pro_inv_tran_type', 2)
                ->sum('inv_pro_inv_credit');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public static function get_total_sell_return_price($pro_id) {
        $com = Auth::user()->au_company_id;

        $product = Inv_product_detail::where('inv_pro_det_com_id', $com)
            ->where('inv_pro_det_id', $pro_id)
            ->first();
        if(!empty($product)) {
            $total_buy_ret_qty = Inv_product_inventory::where('inv_pro_inv_com_id', $com)
                ->where('inv_pro_inv_project_id', $product->inv_pro_det_id)
                ->where('inv_pro_inv_deal_type', 2)
                ->where('inv_pro_inv_tran_type', 2)
                ->sum('inv_pro_inv_credit');

            return $total_buy_ret_qty;
        } else {
            return 0;
        }
    }

    public function getInvoiceNo(){
        return $this->belongsTo('App\Inv_product_inventory','inv_pro_det_id','inv_pro_inv_project_id');
    }





}
