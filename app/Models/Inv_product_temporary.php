<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_product_temporary extends Model
{
    protected $fillable = [
        'inv_pro_temp_id',
        'inv_pro_temp_user_id',
        'inv_pro_temp_cus_id',
        'inv_pro_temp_pro_id',
        'inv_pro_temp_pro_name',
        'inv_pro_temp_type_name',
        'inv_pro_temp_total_qty',
        'inv_pro_temp_short_qty',
        'inv_pro_temp_short_remarks',
        'inv_pro_temp_qty',
        'inv_pro_temp_invoice_no',
        'inv_pro_temp_unit_price',
        'inv_pro_temp_exp_date',
        'inv_pro_temp_slno',
        'inv_pro_temp_deal_type',
        'inv_pro_temp_type',
        'inv_pro_temp_status',
        'inv_pro_temp_created_at',
        'inv_pro_temp_updated_at',
    ];
    
    protected $table = 'inv_product_temporaries';
    protected $primaryKey = 'inv_pro_temp_id';
    public $incrementing = true;
    public $timestamps = false;

    // Relationship to product detail (inv_pro_temp_pro_id is the detail ID)
    public function pro_warranty(){
        return $this->belongsTo('App\Models\Inv_product_detail', 'inv_pro_temp_pro_id', 'inv_pro_det_id');
    }

    // Relationship to user who added to cart
    public function sold_by(){
        return $this->belongsTo('App\Models\Admin_user', 'inv_pro_temp_user_id', 'au_id');
    }

    // Add relationship to get product through detail
    public function productDetail()
    {
        return $this->belongsTo('App\Models\Inv_product_detail', 'inv_pro_temp_pro_id', 'inv_pro_det_id');
    }

    // Get product name with fallback
    public function getProductNameAttribute()
    {
        // First check if we have the name stored in temporary table
        if (!empty($this->inv_pro_temp_pro_name)) {
            return $this->inv_pro_temp_pro_name;
        }
        
        // If not, try to get it through relationships
        if ($this->productDetail && $this->productDetail->product) {
            return $this->productDetail->product->inv_pro_name ?? 'Unknown Product';
        }
        
        return 'Product #' . $this->inv_pro_temp_pro_id;
    }

    // Get product description
    public function getProductDescriptionAttribute()
    {
        if ($this->productDetail) {
            return $this->productDetail->inv_pro_det_pro_description ?? '';
        }
        return '';
    }

    // Get warranty
    public function getWarrantyAttribute()
    {
        if ($this->productDetail) {
            return $this->productDetail->inv_pro_det_pro_warranty ?? 0;
        }
        return 0;
    }
}