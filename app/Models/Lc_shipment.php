<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A shipment under an LC; receiving it adds the goods to stock.
 */
class Lc_shipment extends Model
{
    protected $table = 'lc_shipments';
    protected $primaryKey = 'lcs_id';
    public $timestamps = false;
    protected $guarded = [];

    const STATUS_LABELS = [1 => 'In Transit', 2 => 'Received'];

    public function lc()
    {
        return $this->belongsTo('App\Models\Lc_letter', 'lcs_lc_id', 'lc_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Lc_shipment_item', 'lcsi_shipment_id', 'lcs_id');
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->lcs_status] ?? 'Unknown';
    }
}
