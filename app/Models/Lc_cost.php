<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A payment / cost on an LC. Each one is also a bank statement row (type 13).
 */
class Lc_cost extends Model
{
    protected $table = 'lc_costs';
    protected $primaryKey = 'lcx_id';
    public $timestamps = false;
    protected $guarded = [];

    public function lc()
    {
        return $this->belongsTo('App\Models\Lc_letter', 'lcx_lc_id', 'lc_id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Inv_acc_bank_info', 'lcx_bank_id', 'inv_abi_id');
    }

    public function getHeadLabelAttribute()
    {
        return Lc_letter::HEADS[$this->lcx_head] ?? 'Other';
    }

    public function getIsGoodsPaymentAttribute()
    {
        return in_array($this->lcx_head, Lc_letter::GOODS_HEADS);
    }
}
