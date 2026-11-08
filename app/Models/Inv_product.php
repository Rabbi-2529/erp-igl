<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Inv_product extends Model
{
    protected $table = 'inv_product';

    protected $primaryKey = 'inv_pro_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $casts = [
        'inv_pro_id' => 'integer',
        'inv_pro_com_id' => 'integer',
        'inv_pro_type_id' => 'integer',
        'inv_pro_buy_price' => 'decimal:2',
        'inv_pro_sell_price' => 'decimal:2',
        'inv_pro_warranty' => 'integer',
        'inv_pro_status' => 'integer',
    ];

    protected $fillable = [
        'inv_pro_com_id',
        'inv_pro_type_id',
        'inv_pro_sup',
        'inv_pro_name',
        'inv_pro_buy_price',
        'inv_pro_sell_price',
        'inv_pro_warranty',
        'inv_pro_description',
        'inv_pro_status',
    ];
    public function product()
    {
        return $this->belongsTo('App\Models\Inv_product', 'inv_pro_det_pro_id', 'inv_pro_id');
    }
    // Product Type Relation
    public function type_info(){
        return $this->belongsTo('App\Models\Inv_product_type','inv_pro_type_id','inv_pro_type_id');
    }
    // Product Details Relation
    public function details()
    {
        return $this->hasMany(Inv_product_detail::class, 'inv_pro_det_pro_id', 'inv_pro_id');
    }

    // Accessor to get branch-specific quantity
public function getBranchQuantityAttribute()
{
    $branch = Auth::check() ? Auth::user()->au_branch_id : null;

    if (!$branch) {
        return 0;
    }

    $detail = $this->details()
                   ->where('inv_pro_det_branch_id', $branch)
                   ->first();

    return $detail ? $detail->inv_pro_det_available_qty : 0;
}


}
