<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inv_bank extends Model
{
    protected $fillable =
    [
    	'bank_name',
    	'status'
    ];
    protected $table = 'inv_banks';

    public static function get_All_Bank_Info(){

        return Inv_bank::where('status',1)->get();
    }
}
