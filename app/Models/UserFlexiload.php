<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFlexiload extends Model
{
    protected $table = 'user_flexiloads';
    protected $fillable =
    [
        'user_id',
        'amount'
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    public function userDetails(){
        return $this->belongsTo('App\Models\Admin_user','user_id','au_id');
    }
}
