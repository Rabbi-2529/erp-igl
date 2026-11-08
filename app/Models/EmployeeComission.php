<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmployeeComission extends Model
{
    protected $fillable =
    [
        'company_id',
             'branch_id',
        'emp_id',
        'comission',
        'invoice_no',
        'invoice_id',
        'invoice_pro_qty',
        'invoice_pro_price',
        'created_at',
        'updated_at',
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    public function user_name(){
        return $this->belongsTo('App\Models\Admin_user','emp_id','au_id');
    }
}
