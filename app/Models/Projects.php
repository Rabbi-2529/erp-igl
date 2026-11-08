<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    protected $fillable =
    [
        'company_id',
          'branch_id',
        'project_name',
        'customer_id',
        'address',
        'project_date',
        'project_engineer',
        'entry_by',
        'status',
    ];
    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public function cusInfo(){
        return $this->belongsTo('App\Models\Inv_customer','customer_id','inv_cus_id');
    }
}
