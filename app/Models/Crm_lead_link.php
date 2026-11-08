<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An employee's personal lead form link: leads sent through it belong to that employee. */
class Crm_lead_link extends Model
{
    protected $table = 'crm_lead_links';
    protected $primaryKey = 'cll_id';
    public $timestamps = false;
    protected $guarded = [];
}
