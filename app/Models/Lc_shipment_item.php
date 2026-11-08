<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lc_shipment_item extends Model
{
    protected $table = 'lc_shipment_items';
    protected $primaryKey = 'lcsi_id';
    public $timestamps = false;
    protected $guarded = [];

    public function shipment()
    {
        return $this->belongsTo('App\Models\Lc_shipment', 'lcsi_shipment_id', 'lcs_id');
    }

    public function fileItem()
    {
        return $this->belongsTo('App\Models\Lc_file_item', 'lcsi_file_item_id', 'lcfi_id');
    }
}
