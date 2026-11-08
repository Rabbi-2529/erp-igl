<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGenerate extends Model
{
    use HasFactory;

    protected $table = 'emp_salary_generates';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'basic_salary',
        'total_late_cut',
        'total_absent_cut',
        'total_paid_leave',
        'final_salary',
        'salary_advance_id',
        'salary_loan_id',
        'company_id',
         'branch_id',
        'status', // New field
        'approved_by', // New field
        'approved_date', // New field
        'generated_by' // Added field
    ];

    protected $casts = [
        'status' => 'integer', // Ensure status is treated as integer
        'approved_date' => 'datetime', // Ensure correct date formatting
    ];

    public function user()
    {
        return $this->belongsTo(Admin_user::class, 'user_id');
    }

    public function salaryAdvance()
    {
        return $this->belongsTo(SalaryRecord::class, 'salary_advance_id');
    }

    public function company()
    {
        return $this->belongsTo(Admin_user::class, 'company_id');
    }
    public function branch()
    {
        return $this->belongsTo(Admin_user::class, 'branch_id');
    }

    // Mutator to set default status to 0
    protected static function booted()
    {
        static::creating(function ($salaryGenerate) {
            if (is_null($salaryGenerate->status)) {
                $salaryGenerate->status = 0; // Default to Not Approved
            }
        });
    }

    // Accessor to get status as readable string
    public function getStatusAttribute($value)
    {
        return $value == 1 ? 'Approved' : 'Not Approved';
    }

    // Relationship with Admin_user model for approved_by
    public function approvedBy()
    {
        return $this->belongsTo(Admin_user::class, 'approved_by');
    }

    // Relationship with Admin_user model for generated_by
    public function generatedBy()
    {
        return $this->belongsTo(Admin_user::class, 'generated_by');
    }
}
