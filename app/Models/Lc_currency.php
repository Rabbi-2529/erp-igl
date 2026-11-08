<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lc_currency extends Model
{
    protected $table = 'lc_currencies';
    protected $primaryKey = 'lcc_id';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Active currencies of a company; BDT and USD are added the first time.
     */
    public static function forCompany($companyId)
    {
        if (!Lc_currency::where('lcc_company_id', $companyId)->exists()) {
            foreach ([['BDT', 'Bangladeshi Taka', 1], ['USD', 'US Dollar', 120]] as [$code, $name, $rate]) {
                Lc_currency::create([
                    'lcc_company_id' => $companyId,
                    'lcc_code'       => $code,
                    'lcc_name'       => $name,
                    'lcc_rate'       => $rate,
                    'lcc_status'     => 1,
                    'lcc_created_at' => Carbon::now(),
                ]);
            }
        }

        return Lc_currency::where('lcc_company_id', $companyId)
            ->where('lcc_status', 1)
            ->orderByRaw("lcc_code = 'BDT' desc")
            ->orderBy('lcc_code')
            ->get();
    }
}
