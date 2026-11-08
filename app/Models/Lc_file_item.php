<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A product on an import file's PI.
 */
class Lc_file_item extends Model
{
    protected $table = 'lc_file_items';
    protected $primaryKey = 'lcfi_id';
    public $timestamps = false;
    protected $guarded = [];

    public function file()
    {
        return $this->belongsTo('App\Models\Lc_file', 'lcfi_file_id', 'lcf_id');
    }

    public function productDetail()
    {
        return $this->belongsTo('App\Models\Inv_product_detail', 'lcfi_pro_det_id', 'inv_pro_det_id');
    }

    public function getProductNameAttribute()
    {
        $detail = $this->productDetail;
        if (!$detail) {
            return 'N/A';
        }
        $type = $detail->type_info->inv_pro_type_name ?? ($detail->product->type_info->inv_pro_type_name ?? null);
        return ($detail->product->inv_pro_name ?? 'N/A') . ($type ? ' (' . $type . ')' : '');
    }

    public function getIsWarrantyAttribute()
    {
        return $this->productDetail && $this->productDetail->inv_pro_det_pro_warranty != 0;
    }

    public function getPendingShipQtyAttribute()
    {
        return max($this->lcfi_qty - $this->lcfi_shipped_qty, 0);
    }
}
