<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PayrollExperience extends Model
{
    protected $table = 'payroll_experience';

    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
}
