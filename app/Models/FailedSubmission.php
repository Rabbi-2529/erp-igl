<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedSubmission extends Model
{
    protected $table = 'failed_submissions';
    protected $primaryKey = 'fs_id';
    public $timestamps = true;
    
    protected $fillable = [
        'fs_mobile', 'fs_phone', 'fs_name', 'fs_company_name', 'fs_find_us',
        'fs_address', 'fs_birth_date', 'fs_marriage_date', 'fs_reason',
        'fs_email', 'fs_result', 'fs_cus_type', 'fs_staff_id',
        'fs_attempted_by', 'fs_attempted_date', 'fs_company_id',
        'fs_branch_id', 'fs_status'
    ];
    
    // Relationship with the user who attempted
    public function attempted_by_user()
    {
        return $this->belongsTo(Admin_user::class, 'fs_attempted_by', 'au_id');
    }
    
    // Relationship with the find us option
    public function find_us_info()
    {
        return $this->belongsTo(Sds_find_us::class, 'fs_find_us', 'sf_slid');
    }
    
    // Relationship with reason
    public function reason_info()
    {
        return $this->belongsTo(Sds_reason::class, 'fs_reason', 'sr_slid');
    }
    
    // Relationship with staff
    public function staff_info()
    {
        return $this->belongsTo(Admin_user::class, 'fs_staff_id', 'au_id');
    }
}