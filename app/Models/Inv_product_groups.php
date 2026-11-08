<?php

namespace App;namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inv_product_groups extends Model
{
    //
    protected $table = 'inv_product_groups';
    protected $fillable =
    [
        'inv_pro_grp_id',
        'inv_pro_grp_com_id',
                'inv_pro_grp_branch_id',
        'inv_pro_grp_name',
        'inv_pro_grp_status',
        'inv_pro_grp_submit_by',
        'inv_pro_grp_submit_at',
        'inv_pro_grp_updated_at',
    ];
    protected $primaryKey = 'inv_pro_grp_id';
    public $incrementing = true;
    public $timestamps = false;

    public function grp_submit_by(){
        return $this->belongsTo('App\Models\Admin_user','inv_pro_grp_submit_by','au_id');
    }
}
