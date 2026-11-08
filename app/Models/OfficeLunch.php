<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLunch extends Model
{
    protected $fillable =
    [
        'id',
        'company_id',
                'branch_id',

        'user_id',
        'total_meal',
        'lunch_in',
        'lunch_cancel',
        'user_ip',
        'notes',
        'cancel_reason',
        'status',
    ];


    protected $primaryKey = 'id';
    public $incrementing = true;
}
