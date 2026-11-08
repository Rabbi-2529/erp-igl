<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Public lead form of a branch (CRM > Auto Lead Form). */
class Crm_lead_form extends Model
{
    protected $table = 'crm_lead_forms';
    protected $primaryKey = 'clf_id';
    public $timestamps = false;
    protected $guarded = [];

    /** Sales people the leads go to, in turn (au_id list) */
    public function assigneeIds()
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) $this->clf_assignees))));
    }
}
