<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Inv_asset_fixed extends Model
{
    protected $table = 'inv_asset_fixed';
    protected $primaryKey = 'asset_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'asset_com_id',
        'asset_branch_id',
        'asset_category_id',
        'asset_name',
        'asset_code',
        'asset_description',
        'asset_purchase_date',
        'asset_purchase_price',
        'asset_current_value',
        'asset_depreciation_rate',
        'asset_depreciation_method',
        'asset_serial_no',
        'asset_model_no',
        'asset_supplier_id',
        'asset_location',
        'asset_assigned_to',
        'asset_status',
        'asset_warranty_expiry',
        'asset_image',
        'asset_notes',
        'asset_submit_by',
        'asset_submit_at',
        'asset_update_by',
        'asset_update_at',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo('App\Models\Admin_user', 'asset_com_id', 'au_company_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch', 'asset_branch_id', 'branch_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Inv_asset_category', 'asset_category_id', 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Inv_supplier', 'asset_supplier_id', 'inv_sup_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo('App\Models\Admin_user', 'asset_assigned_to', 'au_id');
    }

    public function submitBy()
    {
        return $this->belongsTo('App\Models\Admin_user', 'asset_submit_by', 'au_id');
    }

    public function updateBy()
    {
        return $this->belongsTo('App\Models\Admin_user', 'asset_update_by', 'au_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('asset_status', 1);
    }

    public function scopeByCompany($query)
    {
        return $query->where('asset_com_id', Auth::user()->au_company_id);
    }

    public function scopeByBranch($query)
    {
        return $query->where('asset_branch_id', Auth::user()->au_branch_id);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Under Maintenance',
            3 => 'Disposed'
        ];
        return $statuses[$this->asset_status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            0 => 'danger',
            1 => 'success',
            2 => 'warning',
            3 => 'secondary'
        ];
        return $badges[$this->asset_status] ?? 'secondary';
    }
}