<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeActivity extends Model
{
    use HasFactory;

    protected $table = 'employee_activity';

    protected $fillable = [
        'company_id',
          'branch_id',
        'user_id',
        'w_title',
        'w_d',
        'status',
    ];

    // Optional: relationships if needed later
    public function user()
    {
        return $this->belongsTo(Admin_user::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Admin_user::class, 'company_id');
    }
      public function branch()
    {
        return $this->belongsTo(Admin_user::class, 'branch_id');
    }

    // Optional: Accessor for readable status
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }
}
