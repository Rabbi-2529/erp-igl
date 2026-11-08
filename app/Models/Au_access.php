<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Au_access extends Model
{
    //
    protected $fillable =
    [
    	'access_id',
    	'code',
      'parent_menu',
    	'permission_name',
    	'permission_title',
      'permission_step',
      'permission_type',
      'permission_user_type',
      'au_access_company_id',
    	'status',
    ];
    protected $table = 'au_accesses';
    protected $primaryKey = 'access_id';
    public $incrementing = true;
    public $timestamps = true;


    public function sub_menues()
    {
      return $this->hasMany(Au_access::class, 'parent_menu', 'access_id');
    }
}
