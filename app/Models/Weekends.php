<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weekends extends Model
{
    protected $fillable =
    [
        'id',
        'company_id',
        'weekend'
    ];

    public static function isWeekend($date)
    {
    return (date('N', strtotime($date)) >= 6);
    }

    protected $primaryKey = 'id';
    public $incrementing = true;
}
