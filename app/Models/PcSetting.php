<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PcSetting extends Model
{
    use HasFactory;

    // Define the table name if it's not the default plural of the model name
    protected $table = 'pc_setting';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'company_id',
          'branch_id',
        'password', // plain-text password
    ];

    // No need to hash passwords here
    // You may want to add mutators if needed

    /**
     * Get the user that owns the PC setting.
     */
    public function user()
    {
        return $this->belongsTo(Admin_user::class, 'user_id', 'au_id');
    }

    /**
     * Get the company associated with the PC setting.
     */
    public function company()
    {
        return $this->belongsTo(Admin_user::class, 'company_id', 'au_company_id');
    }
        public function branch()
    {
        return $this->belongsTo(Admin_user::class, 'branch_id', 'au_branch_id');
    }
}
