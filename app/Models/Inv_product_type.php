<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_product_type extends Model
{
    protected $table = 'inv_product_types';
    protected $fillable =
    [
        'inv_pro_type_id',
        'inv_pro_type_com_id',
           'inv_pro_type_branch_id',
        'inv_pro_type_grp_id',
        'inv_pro_type_name',
        'inv_pro_type_status',
        'inv_pro_type_submit_by',
        'inv_pro_type_submit_at',
        'inv_pro_type_updated_by',
        'inv_pro_type_updated_at',
    ];
    protected $primaryKey = 'inv_pro_type_id';
    public $incrementing = true;
    public $timestamps = false;

    public function type_submit_by(){
        return $this->belongsTo('App\Models\Admin_user','inv_pro_type_submit_by','au_id');
    }

    public function type_category(){
        return $this->belongsTo('App\Models\Inv_product_groups','inv_pro_type_grp_id','inv_pro_grp_id');
    }


}
