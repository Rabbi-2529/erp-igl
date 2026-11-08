<?php

namespace App\Http\Controllers\Lc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lc_currency;
use Carbon\Carbon;
use Auth;

/**
 * LC currency settings: the currencies files can use and their default rate to BDT.
 */
class LcSettingController extends Controller
{
    use LcHelpers;

    public function currencies()
    {
        Lc_currency::forCompany($this->com()); // adds BDT and USD the first time

        $currencies = Lc_currency::where('lcc_company_id', $this->com())
            ->orderByRaw("lcc_code = 'BDT' desc")
            ->orderBy('lcc_code')
            ->get();

        return view('lc.currencies', compact('currencies'));
    }

    public function storeCurrency(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);
        $request->validate([
            'code' => 'required|alpha|size:3',
            'name' => 'nullable|string|max:50',
            'rate' => 'required|numeric|gt:0',
        ]);

        if (Lc_currency::where('lcc_company_id', $this->com())->where('lcc_code', $request->code)->exists()) {
            return redirect()->back()->withInput()->with('errmsg', $request->code . ' is already in the list.');
        }

        Lc_currency::create([
            'lcc_company_id' => $this->com(),
            'lcc_code'       => $request->code,
            'lcc_name'       => $request->name,
            'lcc_rate'       => $request->code === 'BDT' ? 1 : $request->rate,
            'lcc_status'     => 1,
            'lcc_created_by' => Auth::user()->au_id,
            'lcc_created_at' => Carbon::now(),
        ]);

        return redirect()->route('lc.currencies')->with('msg', $request->code . ' added.');
    }

    public function updateCurrency(Request $request, $id)
    {
        $request->validate([
            'name'   => 'nullable|string|max:50',
            'rate'   => 'required|numeric|gt:0',
            'status' => 'required|in:0,1',
        ]);

        $currency = Lc_currency::where('lcc_id', $id)->where('lcc_company_id', $this->com())->firstOrFail();
        $currency->lcc_name = $request->name;
        $currency->lcc_rate = $currency->lcc_code === 'BDT' ? 1 : $request->rate; // BDT is always 1
        $currency->lcc_status = $currency->lcc_code === 'BDT' ? 1 : $request->status;
        $currency->lcc_updated_at = Carbon::now();
        $currency->save();

        return redirect()->route('lc.currencies')->with('msg', $currency->lcc_code . ' updated.');
    }
}
