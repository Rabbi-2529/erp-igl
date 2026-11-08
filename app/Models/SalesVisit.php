<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesVisit extends Model
{
    use HasFactory;

    protected $table = 'sales_visits';

    protected $fillable = [
        'company_id',
         'branch_id',
        'cf_id',
        'emp_id',
        'client_name',
        'client_phone',
        'entry_by',
        'visit_date',
        'reason',
    ];
}
