<?php

namespace App\Http\Controllers;

use App\Models\Erp_theme_setting;
use Illuminate\Http\Request;
use Auth;

/**
 * Theme Colors: the company admin can keep the default FelnaTech theme
 * or pick the company's own colours. Saved per company; Reset deletes the row.
 */
class ThemeSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Only the company admin (au_user_type 4) may change the colours */
    private function canChange()
    {
        return Auth::user() && (int) Auth::user()->au_user_type === 4;
    }

    public function edit()
    {
        $companyId = Auth::user()->au_company_id;
        $settings = Erp_theme_setting::forCompany($companyId);
        $defaults = Erp_theme_setting::DEFAULTS;
        $fonts = Erp_theme_setting::FONTS;
        $classicTokens = Erp_theme_setting::CLASSIC_TOKENS;
        $canChange = $this->canChange();
        try {
            $saved = Erp_theme_setting::where('ets_company_id', $companyId)->first();
        } catch (\Throwable $e) {
            $saved = null;
        }

        return view('page.theme_settings', compact('settings', 'defaults', 'fonts', 'classicTokens', 'canChange', 'saved'));
    }

    public function update(Request $request)
    {
        if (!$this->canChange()) {
            return redirect()->route('theme-settings')->withErrors(['Only the company admin can change the theme colours.']);
        }

        $hex = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $data = $request->validate([
            'primary'      => $hex,
            'accent'       => $hex,
            'sidebar'      => $hex,
            'search'       => $hex,
            'page'         => $hex,
            'text'         => $hex,
            'heading_font' => ['required', 'in:' . implode(',', array_keys(Erp_theme_setting::FONTS))],
            'style'        => ['required', 'in:' . implode(',', array_keys(Erp_theme_setting::STYLES))],
        ], [
            'regex' => 'The :attribute colour must look like #1a2b3c.',
        ]);

        $companyId = Auth::user()->au_company_id;
        $settings = Erp_theme_setting::clean($data);

        if ($settings === Erp_theme_setting::DEFAULTS) {
            // Same as the default theme: nothing to keep
            Erp_theme_setting::where('ets_company_id', $companyId)->delete();
        } else {
            Erp_theme_setting::updateOrCreate(
                ['ets_company_id' => $companyId],
                [
                    'ets_settings'   => json_encode($settings),
                    'ets_updated_by' => Auth::user()->au_id,
                    'ets_updated_at' => now(),
                ]
            );
        }
        Erp_theme_setting::forget($companyId);

        return redirect()->route('theme-settings')->with('msg', 'Theme colours saved.');
    }

    public function reset()
    {
        if (!$this->canChange()) {
            return redirect()->route('theme-settings')->withErrors(['Only the company admin can change the theme colours.']);
        }

        $companyId = Auth::user()->au_company_id;
        Erp_theme_setting::where('ets_company_id', $companyId)->delete();
        Erp_theme_setting::forget($companyId);

        return redirect()->route('theme-settings')->with('msg', 'Default theme restored.');
    }
}
