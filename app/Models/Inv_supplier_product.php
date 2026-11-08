<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_supplier_product extends Model
{
    //
    protected $fillable =
    [
        'inv_sup_pro_id',
        'inv_sup_pro_com_id',
          'inv_sup_pro_branch_id',
        'inv_sup_pro_sup_id',
        'inv_sup_pro_pro_id',
        'inv_sup_pro_status',
        'inv_sup_pro_submit_by',
        'inv_sup_pro_submit_at',
        'inv_sup_pro_update_by',
        'inv_sup_pro_update_at',
    ];
    protected $primaryKey = 'inv_sup_pro_id';
    protected $table = 'inv_supplier_products';
    public $incrementing = true;
    public $timestamps = false;

    public function supplier_info(){
        return $this->belongsTo('App\Models\Inv_supplier','inv_sup_pro_sup_id','inv_sup_id');
    }

    public function product_info(){
        return $this->belongsTo('App\Models\Inv_product_detail','inv_sup_pro_pro_id','inv_pro_det_id');
    }

    public function submit_by(){
        return $this->belongsTo('App\Models\Admin_user','inv_sup_pro_submit_by','au_id');
    }
}
