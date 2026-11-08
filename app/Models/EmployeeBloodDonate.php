<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeBloodDonate extends Model
{
    protected $table = 'employee_blood_donate';

    protected $fillable = [
        'employee_id',
        'donate_date',
        'for_whom',
        'status',
    ];

    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    /**
     * Relations
     */

    // Relation with Admin_user model (assuming 'employee_id' is related to 'au_user_id' in Admin_user model)
    public function adminUser()
    {
        return $this->belongsTo(Admin_user::class, 'employee_id', 'au_user_id');
    }

    /**
     * Accessors & Mutators
     */

    // Mutator for automatically setting the status based on the donate date
    public function setDonateDateAttribute($value)
    {
        $this->attributes['donate_date'] = $value;

        // Determine the status of the donation (Available if 3 months have passed)
        $threeMonthsLater = Carbon::parse($value)->addMonths(3);
        $this->attributes['status'] = now()->gte($threeMonthsLater) ? 'Available' : 'Not Available';
    }

    /**
     * Methods
     */

    // Static method to get blood donors by their blood group
    public static function getBloodDonors($bloodGroup)
    {
        // Get all users with the specified blood group
        return Admin_user::where('au_blood_group', $bloodGroup)
                         ->get();
    }

    // Static method to check the status based on the donate date
    public function checkStatus($status, $donateDate)
    {
        // Your logic here
        // Example: Check if the status is valid for a given donation date
        if ($status == 'approved' && $donateDate > now()) {
            return true;
        }
        return false;
    }
}
