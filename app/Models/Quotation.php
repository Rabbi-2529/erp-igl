<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;

class Quotation extends Model
{
    protected $fillable = [
        'qt_company_id',
        'qt_branch_id',
        'qt_entry_by',
        'qt_customer_id', // This will reference Sds_query_book
        'qt_total_amount',
        'qt_discount',
        'qt_final_amount',
        'qt_status',
        'qt_notes',
        'qt_created_at',
        'qt_product_id',
        'quotation_id' // Column for storing array of product references
    ];

    protected $table = 'quotations';
    protected $primaryKey = 'qt_id';
    public $incrementing = true;
    public $timestamps = false;

    // Mutator to save product IDs as a JSON array
    public function setQtProductIdAttribute($value)
    {
        $this->attributes['qt_product_id'] = json_encode($value);
    }
    public function product()
{
    return $this->belongsTo('App\Models\Inv_product', 'qt_product_id', 'inv_pro_id');
}

    // Accessor to retrieve product names based on the product ID
    // Accessor to retrieve product names based on the product ID
    public function getQtProductIdAttribute($value)
    {
        // Decode the JSON string to an array of product IDs
        $productIds = json_decode($value, true);

        // If there are no product IDs, return an empty array
        if (empty($productIds)) {
            return [];
        }

        // Fetch product details based on the product IDs
        $products = Inv_product_detail::whereIn('inv_pro_det_id', $productIds)
            ->get(['inv_pro_det_id', '']); // Fetch product IDs and names

        // Combine product details into a readable format
        $productDetails = [];
        foreach ($products as $product) {
            $productDetails[] = [
                'product_id' => $product->inv_pro_det_id,
                'product_name' => $product->inv_pro_det_pro_id // Ensure the product_name key exists
            ];
        }

        return $productDetails; // Return product details array
    }


    // Relationship with Sds_query_book (Customer Information)
    public function customer()
    {
        return $this->belongsTo(Sds_query_book::class, 'qt_customer_id', 'qb_id');
    }

    // Relationship with Admin_user (Entry By)
    public function entryBy()
    {
        return $this->belongsTo(Admin_user::class, 'qt_entry_by', 'au_id');
    }

    // Get all quotations for a specific company
    public static function companyQuotations($company_id)
    {
        return self::where('qt_company_id', $company_id)->get();
    }

    // Get all quotations created by a specific user
    public static function userQuotations($au_id, $company_id)
    {
        return self::where('qt_company_id', $company_id)
            ->where('qt_entry_by', $au_id)
            ->get();
    }

    // Get today's quotations for a specific user
    public static function userTodayQuotations($au_id, $company_id)
    {
        $today = Carbon::now()->format('Y-m-d');
        return self::where('qt_company_id', $company_id)
            ->where('qt_entry_by', $au_id)
            ->whereDate('qt_created_at', $today)
            ->get();
    }
}
