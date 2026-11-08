<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollReferance extends Model
{
    protected $table = 'payroll_reference';
    protected $fillable =
    [
        'user_id',
        'ref_name',
        'ref_number',
        'company_name',
                'branch_name',
        'company_address',
        'designation'

    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
}
