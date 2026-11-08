<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Sds_team_name extends Model
{
    //
    protected $fillable = ['tl_team_name'];
    protected $table = 'sds_team_name';
    protected $primaryKey = 'tl_sl_id';
    public $incrementing = true;
    public $timestamps = false;

    public static function is_team_leader_exists($team_id) {
    	$leader = Admin_user::where('au_team_id', $team_id)
    			->where('au_user_type', 5)
    			->where('au_status', 1)
    			->first();

    	if (!empty($leader)) {
    		return true;
    	} else {
    		return false;
    	}
    }

    public static function is_team_leader_no_exists($team_id) {
        $leader = Admin_user::where('au_team_id', $team_id)
                ->where('au_user_type', 5)
                ->first();

        if (!empty($leader)) {
            return true;
        } else {
            return false;
        }
    }
}
