<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollShiftManagement extends Model
{
    protected $table = 'payroll_shift_management';
    protected $fillable =
    [
        'company_id',
         'branch_id',
        'name',
        'working_hour',
        'shift_hour',
        'late_entry',
        'status'

    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
}
