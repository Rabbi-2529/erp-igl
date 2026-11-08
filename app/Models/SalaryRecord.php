<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRecord extends Model
{
    // Define the table associated with the model
    protected $table = 'emp_salary_advance';

    // Disable automatic timestamps management
    public $timestamps = false;

    // Define the fillable attributes
    protected $fillable = [
        'employee_id',
        'salary_month',
        'amount',
        'release_amount',
        'paid',
        'create_date',
        'create_by',
        'update_date',
        'update_by',
        'com_id',
        'branch_id'
    ];

    // Define the types of attributes
    protected $casts = [
        'amount' => 'decimal:0',
        'release_amount' => 'decimal:0',
        'paid' => 'integer',
        'create_date' => 'date',
        'update_date' => 'date',
    ];

    // Define the attributes that should be treated as dates
    protected $dates = ['create_date', 'update_date'];
}
