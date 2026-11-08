<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
            'branch_id',
        'date',
        'description',
        'year',
        'month',
    ];

    public $incrementing = true;
}
