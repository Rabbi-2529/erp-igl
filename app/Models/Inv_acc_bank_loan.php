<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use DB;

class Inv_acc_bank_loan extends Model
{
    protected $fillable = [
        'inv_abl_id',
        'inv_abl_company_id',
        'inv_abl_branch_id',
        'inv_abl_bank_id',
        'inv_abl_loan_name',
        'inv_abl_loan_type',
        'inv_abl_loan_account_no',
        'inv_abl_principal_amount',
        'inv_abl_interest_rate',
        'inv_abl_interest_type',
        'inv_abl_tenure_months',
        'inv_abl_emi_amount',
        'inv_abl_disbursement_date',
        'inv_abl_first_emi_date',
        'inv_abl_maturity_date',
        'inv_abl_outstanding_balance',
        'inv_abl_total_paid',
        'inv_abl_total_interest_paid',
        'inv_abl_status',
        'inv_abl_remarks',
        'inv_abl_submit_by',
        'inv_abl_submit_at',
        'inv_abl_updated_by',
        'inv_abl_updated_at'
    ];

    protected $dates = [
        'inv_abl_disbursement_date',
        'inv_abl_first_emi_date',
        'inv_abl_maturity_date',
    ];

    protected $table = 'inv_acc_bank_loans';
    protected $primaryKey = 'inv_abl_id';
    public $incrementing = true;
    public $timestamps = false;

    /**
     * Loan Type → months per period mapping
     *   1 = Monthly      → 1 month
     *   2 = Quarterly    → 3 months
     *   3 = Half-Yearly  → 6 months
     *   4 = Yearly       → 12 months
     */
    const LOAN_TYPE_MAP = [
        1 => 1,
        2 => 3,
        3 => 6,
        4 => 12,
    ];

    // ========== Relationships ==========

    /**
     * Related bank account (inv_acc_bank_infos row)
     */
    public function bank_info()
    {
        return $this->belongsTo('App\Models\Inv_acc_bank_info', 'inv_abl_bank_id', 'inv_abi_id');
    }

    /**
     * Payment history
     */
    public function payments()
    {
        return $this->hasMany('App\Models\Inv_acc_bank_loan_payment', 'inv_ablp_loan_id', 'inv_abl_id');
    }

    // ========== Helper Attributes ==========

    /**
     * Human-readable loan type label
     */
    public function getLoanTypeLabelAttribute()
    {
        $types = [
            1 => 'Monthly',
            2 => 'Quarterly',
            3 => 'Half-Yearly',
            4 => 'Yearly',
        ];
        return $types[$this->inv_abl_loan_type] ?? 'Unknown';
    }

    /**
     * Human-readable status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            0 => 'Inactive',
            1 => 'Active',
            2 => 'Closed',
        ];
        return $statuses[$this->inv_abl_status] ?? 'Unknown';
    }

    /**
     * Months per repayment period based on loan type
     */
    public function getMonthsPerPeriodAttribute()
    {
        return self::LOAN_TYPE_MAP[$this->inv_abl_loan_type] ?? 1;
    }

    /**
     * Total number of installments
     */
    public function getTotalInstallmentsAttribute()
    {
        $monthsPerPeriod = $this->months_per_period;
        if ($monthsPerPeriod <= 0 || $this->inv_abl_tenure_months <= 0) {
            return 0;
        }
        return (int) ceil($this->inv_abl_tenure_months / $monthsPerPeriod);
    }

    /**
     * Total payable (EMI × number of installments)
     */
    public function getTotalLoanAmountAttribute()
    {
        return round($this->inv_abl_emi_amount * $this->total_installments, 2);
    }

    /**
     * Total interest payable over the whole loan
     */
    public function getTotalInterestPayableAttribute()
    {
        return round($this->total_loan_amount - $this->inv_abl_principal_amount, 2);
    }

    /**
     * Remaining installments count
     */
    public function getRemainingInstallmentsAttribute()
    {
        $paidInstallments = $this->payments()->where('inv_ablp_status', 1)->count();
        return max($this->total_installments - $paidInstallments, 0);
    }

    /**
     * Repayment progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        $totalPayable = $this->total_loan_amount;
        if ($totalPayable <= 0) {
            return 0;
        }
        return round(($this->inv_abl_total_paid / $totalPayable) * 100, 2);
    }

    // ========== EMI & Schedule ==========

    /**
     * Calculate EMI (payment per period) using reducing balance method.
     *
     * @param  float $principal          Loan amount
     * @param  float $annualInterestRate Annual interest rate %
     * @param  int   $tenureMonths       Total tenure in months
     * @param  int   $loanType           1=Monthly, 2=Quarterly, 3=Half-Yearly, 4=Yearly
     * @return float
     */
    public static function calculateEMI($principal, $annualInterestRate, $tenureMonths, $loanType = 1)
    {
        $monthsPerPeriod = self::LOAN_TYPE_MAP[$loanType] ?? 1;

        // Number of installments
        $n = (int) ceil($tenureMonths / $monthsPerPeriod);

        if ($n <= 0) {
            return 0;
        }

        // Rate per period
        $ratePerPeriod = ($annualInterestRate / 100) / (12 / $monthsPerPeriod);

        // Zero interest → straight division
        if ($ratePerPeriod == 0) {
            return round($principal / $n, 2);
        }

        // Standard amortization formula
        $emi = $principal * $ratePerPeriod * pow(1 + $ratePerPeriod, $n)
               / (pow(1 + $ratePerPeriod, $n) - 1);

        return round($emi, 2);
    }

    /**
     * Generate full amortization schedule.
     *
     * @return array
     */
    public function getAmortizationSchedule()
    {
        $schedule = [];
        $balance  = (float) $this->inv_abl_principal_amount;

        $monthsPerPeriod   = $this->months_per_period;
        $totalInstallments = $this->total_installments;
        $emi               = (float) $this->inv_abl_emi_amount;

        if ($totalInstallments <= 0 || $monthsPerPeriod <= 0 || $emi <= 0) {
            return $schedule;
        }

        $ratePerPeriod = ($this->inv_abl_interest_rate / 100) / (12 / $monthsPerPeriod);

        // First payment date: one period after disbursement (or use stored first_emi_date)
        $paymentDate = $this->inv_abl_first_emi_date
            ? Carbon::parse($this->inv_abl_first_emi_date)
            : Carbon::parse($this->inv_abl_disbursement_date)->addMonths($monthsPerPeriod);

        for ($i = 1; $i <= $totalInstallments; $i++) {
            $interestComponent  = round($balance * $ratePerPeriod, 2);
            $principalComponent = round($emi - $interestComponent, 2);

            // Last installment → adjust to close the loan exactly
            if ($i == $totalInstallments || $principalComponent > $balance) {
                $principalComponent = $balance;
                $emi = $principalComponent + $interestComponent;
            }

            $balance -= $principalComponent;

            $schedule[] = [
                'installment_no'      => $i,
                'payment_date'        => $paymentDate->format('Y-m-d'),
                'emi_amount'          => round($emi, 2),
                'principal_component' => $principalComponent,
                'interest_component'  => $interestComponent,
                'remaining_balance'   => round(max($balance, 0), 2),
            ];

            // Move to next period
            $paymentDate->addMonths($monthsPerPeriod);
        }

        return $schedule;
    }

    // ========== Static Helpers ==========

    /**
     * Active loans for the current user's company & branch
     */
    public static function getActiveLoans()
    {
        return self::where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->get();
    }

    /**
     * Total outstanding loan balance for a specific bank
     */
    public static function getTotalLoanBalanceByBankId($bankId)
    {
        return self::where('inv_abl_bank_id', $bankId)
            ->where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->sum('inv_abl_outstanding_balance');
    }

    /**
     * Total outstanding loan balance for current company & branch
     */
    public static function getTotalLoanAmountByCompany()
    {
        return self::where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->sum('inv_abl_outstanding_balance');
    }

    /**
     * Sum of EMI amounts (commitment per period — not strictly monthly anymore)
     */
    public static function getTotalMonthlyEMI()
    {
        return self::where('inv_abl_company_id', Auth::user()->au_company_id)
            ->where('inv_abl_branch_id', Auth::user()->au_branch_id)
            ->where('inv_abl_status', 1)
            ->sum('inv_abl_emi_amount');
    }

    // ========== Mutation ==========

    /**
     * Update loan balances after a payment is recorded.
     */
    public function updateAfterPayment($principalPaid, $interestPaid)
    {
        $this->inv_abl_total_paid          += ($principalPaid + $interestPaid);
        $this->inv_abl_total_interest_paid += $interestPaid;
        $this->inv_abl_outstanding_balance -= $principalPaid;

        if ($this->inv_abl_outstanding_balance <= 0) {
            $this->inv_abl_outstanding_balance = 0;
            $this->inv_abl_status = 2; // Closed
        }

        $this->inv_abl_updated_at = Carbon::now();
        $this->inv_abl_updated_by = Auth::user()->au_id;
        $this->save();

        return $this;
    }

    // ========== Scopes ==========

    /**
     * Scope: filter by current user's company & branch
     */
    public function scopeForCurrentCompany($query)
    {
        return $query->where('inv_abl_company_id', Auth::user()->au_company_id)
                     ->where('inv_abl_branch_id', Auth::user()->au_branch_id);
    }
}