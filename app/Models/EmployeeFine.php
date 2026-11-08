<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeFine extends Model
{
    protected $fillable = [
        'au_user_id',
        'au_company_id',
        'au_branch_id',
        'amount',
        'date',
        'title'
    ];

    public function user()
    {
        return $this->belongsTo(Admin_User::class, 'au_user_id', 'au_user_id');
    }

    public function fine()
    {
        return $this->belongsTo(Fine::class, 'title', 'id');
    }
}