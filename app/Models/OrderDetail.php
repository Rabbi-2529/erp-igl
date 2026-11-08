<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $fillable = [
        'id',
        'company',
        'branch_id',
        'user_id',
        'order_client',
        'description',
        'order_price_credit',
        'order_price_debit',
        'status'
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public function orderInfo(){
        return $this->belongsTo('App\Models\Sds_query_book','order_client','qb_id');
    }

    public function userDetailInfo() {
        return $this->belongsTo('App\Models\Admin_user', 'user_id', 'au_id');
    }
}
