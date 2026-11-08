<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    protected $table = 'auth_branch';


    protected $primaryKey = 'branch_id';


    protected $fillable = [
        'branch_name',
        'branch_code',
        'branch_address',
        'branch_city',
        'branch_state',
        'branch_zip',
        'branch_hotline',
        'branch_email',
        'company_id ',
        'status',
    ];


}
