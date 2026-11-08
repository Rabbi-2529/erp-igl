<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Import file opened against a supplier's proforma invoice (PI).
 */
class Lc_file extends Model
{
    protected $table = 'lc_files';
    protected $primaryKey = 'lcf_id';
    public $timestamps = false;
    protected $guarded = [];

    const STATUS_LABELS = [0 => 'Cancelled', 1 => 'Open', 2 => 'LC Opened'];

    public function items()
    {
        return $this->hasMany('App\Models\Lc_file_item', 'lcfi_file_id', 'lcf_id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Inv_supplier', 'lcf_supplier_id', 'inv_sup_id');
    }

    public function lc()
    {
        return $this->hasOne('App\Models\Lc_letter', 'lc_file_id', 'lcf_id');
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->lcf_status] ?? 'Unknown';
    }

    /**
     * Next file number for a company: LCF-YYYY-0001
     */
    public static function nextFileNo($companyId)
    {
        $prefix = 'LCF-' . date('Y') . '-';
        $last = Lc_file::where('lcf_company_id', $companyId)
            ->where('lcf_file_no', 'like', $prefix . '%')
            ->orderBy('lcf_id', 'desc')
            ->value('lcf_file_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
