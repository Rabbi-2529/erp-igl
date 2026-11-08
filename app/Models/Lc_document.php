<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An uploaded LC paper (stored under storage/app/lc_documents).
 */
class Lc_document extends Model
{
    protected $table = 'lc_documents';
    protected $primaryKey = 'lcd_id';
    public $timestamps = false;
    protected $guarded = [];

    const TYPES = [
        'Proforma Invoice', 'LC Copy', 'Commercial Invoice', 'Packing List',
        'Bill of Lading', 'Insurance Cover Note', 'Bill of Entry', 'Other',
    ];

    public function lc()
    {
        return $this->belongsTo('App\Models\Lc_letter', 'lcd_lc_id', 'lc_id');
    }
}
