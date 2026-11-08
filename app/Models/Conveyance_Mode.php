<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conveyance_Mode extends Model
{
    protected $table = 'conveyance_modes';

    protected $fillable =
        [
            'id',
            'name',
            'value'

        ];
}
