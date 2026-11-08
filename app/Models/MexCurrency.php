<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MexCurrency extends Model
{
    protected $table = 'mex_currencies';
    protected $fillable =
    [
        'country_id',
        'currency_note',
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    public function country_name(){
        return $this->belongsTo('App\Models\MexCountry','country_id','id');
    }
}
