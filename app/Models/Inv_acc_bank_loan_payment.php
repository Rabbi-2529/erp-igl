<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;

class Inv_acc_bank_loan_payment extends Model
{
    protected $fillable = [
        'inv_ablp_id',
        'inv_ablp_loan_id',
        'inv_ablp_company_id',
        'inv_ablp_branch_id',
        'inv_ablp_bank_statement_id',
        'inv_ablp_payment_type',
        'inv_ablp_principal_amount',
        'inv_ablp_interest_amount',
        'inv_ablp_total_amount',
        'inv_ablp_payment_date',
        'inv_ablp_voucher_no',
        'inv_ablp_description',
        'inv_ablp_status',
        'inv_ablp_submit_by',
        'inv_ablp_submit_at',
    ];

    protected $dates = [
        'inv_ablp_payment_date',
    ];

    protected $table = 'inv_acc_bank_loan_payments';
    protected $primaryKey = 'inv_ablp_id';
    public $incrementing = true;
    public $timestamps = false;

    // Relationships
    public function loan()
    {
        return $this->belongsTo('App\Models\Inv_acc_bank_loan', 'inv_ablp_loan_id', 'inv_abl_id');
    }

    public function bankStatement()
    {
        return $this->belongsTo('App\Models\Inv_acc_bank_statement', 'inv_ablp_bank_statement_id', 'inv_abs_id');
    }

    /**
     * Get payment type label
     */
    public function getPaymentTypeLabelAttribute()
    {
        $types = [
            1 => 'EMI Payment',
            2 => 'Principal Only',
            3 => 'Interest Only',
            4 => 'Full Settlement',
        ];
        return $types[$this->inv_ablp_payment_type] ?? 'Unknown';
    }
}