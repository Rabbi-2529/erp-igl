<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $fillable =
    [
        'id',
        'name'
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;


    // One to One relationship with admin_user

    public function admin_user(){
        return $this->belongsTo(Admin_user::Class, 'id');
    }

    public static function departmentName($dept){
        $name = Department::whereIn('id',$dept)
                          ->get();
        $names = array();
        foreach ($name as $value) {
            $names[] = $value->name;
        }
        $name = implode(', ',$names);
        return $name;
    }

    public static function deptName($deptId){
        $name = Department::where('id',$deptId)
                          ->first();

        if(isset($name->name))  {
            return $name->name;
        }else{
            return $name = '';
        }

    }
}
