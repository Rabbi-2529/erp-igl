<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One request sent through the public lead form (new lead, or a mobile already in the CRM). */
class Crm_lead_submission extends Model
{
    protected $table = 'crm_lead_submissions';
    protected $primaryKey = 'cls_id';
    public $timestamps = false;
    protected $guarded = [];
}
