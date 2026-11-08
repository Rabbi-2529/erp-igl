<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The LC opened at the bank for an import file (table lc_lcs).
 */
class Lc_letter extends Model
{
    protected $table = 'lc_lcs';
    protected $primaryKey = 'lc_id';
    public $timestamps = false;
    protected $guarded = [];

    const HEADS = [
        1 => 'LC Margin',
        2 => 'Bank Charge / Commission',
        3 => 'Insurance',
        4 => 'Retirement / Bill Payment',
        5 => 'Customs Duty & VAT',
        6 => 'C&F Agent',
        7 => 'Port & Shipping',
        8 => 'Transport',
        9 => 'Other',
    ];

    // Margin and retirement pay for the goods (supplier); the rest are import costs
    const GOODS_HEADS = [1, 4];

    const STATUS_LABELS = [1 => 'Open', 2 => 'Closed'];

    public function file()
    {
        return $this->belongsTo('App\Models\Lc_file', 'lc_file_id', 'lcf_id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Inv_acc_bank_info', 'lc_bank_id', 'inv_abi_id');
    }

    public function costs()
    {
        return $this->hasMany('App\Models\Lc_cost', 'lcx_lc_id', 'lc_id')->where('lcx_status', 1);
    }

    public function shipments()
    {
        return $this->hasMany('App\Models\Lc_shipment', 'lcs_lc_id', 'lc_id');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\Lc_document', 'lcd_lc_id', 'lc_id')->where('lcd_status', 1);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->lc_status] ?? 'Unknown';
    }

    public function goodsPaid()
    {
        return $this->costs->whereIn('lcx_head', self::GOODS_HEADS)->sum('lcx_amount');
    }

    public function importCosts()
    {
        return $this->costs->whereNotIn('lcx_head', self::GOODS_HEADS)->sum('lcx_amount');
    }

    /** Still owed for the goods: LC value (BDT) minus margin and retirement payments */
    public function payable()
    {
        return round($this->lc_value_bdt - $this->goodsPaid(), 2);
    }

    /** Goods value plus every import cost */
    public function landedTotal()
    {
        return round($this->lc_value_bdt + $this->importCosts(), 2);
    }

    /** Each BDT of goods costs this much once the import costs are added */
    public function landedFactor()
    {
        return $this->lc_value_bdt > 0 ? $this->landedTotal() / $this->lc_value_bdt : 1;
    }

    /** Label used in dropdowns */
    public function getLabelAttribute()
    {
        return $this->lc_no . ' - ' . ($this->file->supplier->inv_sup_com_name ?? '') . ' (' . ($this->file->lcf_file_no ?? '') . ')';
    }
}
