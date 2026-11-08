<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MexCustomer extends Model
{
    protected $fillable =
    [
        'com_id',
        'branch_id',
        'name',
        'com_name',
        'mobile',
        'email',
        'address',
        'type',
        'identity',
        'status',
        'submit_by',
        'submit_at',
        'update_by',
        'update_at',
    ];
    protected $table = 'mex_customers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
}
