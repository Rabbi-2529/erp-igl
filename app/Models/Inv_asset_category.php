<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_asset_category extends Model
{
    protected $table = 'inv_asset_category';
    protected $primaryKey = 'category_id';
    public $incrementing = true;
    public $timestamps = false; // <-- CHANGE THIS TO false

    protected $fillable = [
        'category_com_id',
        'category_branch_id',
        'category_name',
        'category_code',
        'category_description',
        'category_status',
        'category_submit_by',
        'category_submit_at',
        'category_update_by',
        'category_update_at',
    ];

    public function assets()
    {
        return $this->hasMany('App\Models\Inv_asset_fixed', 'asset_category_id', 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('category_status', 1);
    }

    public function scopeByCompany($query)
    {
        return $query->where('category_com_id', Auth::user()->au_company_id);
    }
}