<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_product_inventory_detail extends Model
{
    //
    protected $fillable =
    [
        'inv_pro_invdet_id',
        'inv_pro_invdet_com_id',
         'inv_pro_invdet_branch_id',
        'inv_pro_invdet_proinv_id',
        'inv_pro_invdet_proinv_sell_id',
        'inv_pro_invdet_pro_id',
        'inv_pro_invdet_buy_id',
        'inv_pro_invdet_sell_id',
        'inv_pro_invdet_slno',
        'inv_pro_invdet_sell_status',
        'inv_pro_invdet_status',
        'inv_pro_invdet_submit_by',
        'inv_pro_invdet_submit_at',
        'inv_pro_invdet_update_by',
        'inv_pro_invdet_update_at',
    ];
    protected $table = 'inv_product_inventory_details';
    protected $primaryKey = 'inv_pro_invdet_id';
    public $incrementing = true;
    public $timestamps = false;

    public function getProductName(){
        return $this->belongsTo('App\Models\Inv_product_detail','inv_pro_invdet_pro_id','inv_pro_det_id');
    }

    public function getSupplierName(){
        return $this->belongsTo('App\Models\Inv_supplier','inv_pro_invdet_buy_id','inv_sup_id');
    }

    public function getCustomerName(){
        return $this->belongsTo('App\Models\Inv_customer','inv_pro_invdet_sell_id','inv_cus_id');
    }

    public function getInvoiceNumber(){
        return $this->belongsTo('App\Models\Inv_product_inventory','inv_pro_invdet_proinv_id','inv_pro_inv_id');
    }
}
