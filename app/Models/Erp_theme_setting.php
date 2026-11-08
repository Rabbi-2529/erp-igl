<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

/**
 * Company theme colours (Theme Colors page). No row = the default FelnaTech theme.
 * The colours you pick feed the CSS variables of assets/css/theme-felna.css;
 * every shade, tint and readable text colour is worked out from them here.
 */
class Erp_theme_setting extends Model
{
    protected $table = 'erp_theme_settings';
    protected $primaryKey = 'ets_id';
    public $timestamps = false;
    protected $guarded = [];

    /** Default theme (same values as the top of theme-felna.css) */
    const DEFAULTS = [
        'primary'      => '#0e8a43', // buttons, links, table header line
        'accent'       => '#e8202a', // delete buttons, markers, red cards
        'sidebar'      => '#0a4a26', // sidebar background
        'search'       => '#36a22a', // search / filter buttons
        'page'         => '#eef2ef', // page background
        'text'         => '#2b2f2c', // text and the dark cards
        'heading_font' => 'arial-black',
        'style'        => 'felna',       // felna = new FelnaTech style, classic = the previous ERP look
    ];

    const STYLES = [
        'felna'   => 'New FelnaTech style',
        'classic' => 'Previous style',
    ];

    /**
     * Previous style: theme-felna.css is switched off, so the original ERP look comes back.
     * These values stand in for the --erp-* variables that a few views (dashboard, sidebar badge,
     * this settings page) use, set to the colours those spots had before the redesign.
     */
    const CLASSIC_TOKENS = [
        '--erp-style' => 'classic',
        '--erp-red' => '#dc3545', '--erp-red-dark' => '#c82333',
        '--erp-green' => '#37a000', '--erp-green-btn' => '#37a000', '--erp-green-btn-dark' => '#2e8a00',
        '--erp-green-dark' => '#2c3136', '--erp-green-darker' => '#1c1f22', '--erp-green-hover' => '#1c1f22',
        '--erp-green-tint' => '#eef7e6', '--erp-green-light' => '#37a000', '--erp-green-light-dark' => '#2e8a00',
        '--erp-black' => '#374767', '--erp-text' => '#374767', '--erp-muted' => '#6c757d',
        '--erp-border' => '#e4e5e7', '--erp-page' => '#f1f3f6', '--erp-white' => '#ffffff',
        '--erp-block-green' => '#37a000', '--erp-block-red' => '#dc3545', '--erp-green-rgb' => '55, 160, 0',
        '--erp-on-primary' => '#ffffff', '--erp-on-accent' => '#ffffff', '--erp-on-sidebar' => '#ffffff',
        '--erp-on-block-green' => '#ffffff', '--erp-on-block-red' => '#ffffff', '--erp-on-black' => '#ffffff',
        '--erp-on-search' => '#ffffff', '--erp-on-search-hover' => '#ffffff',
        '--erp-sidebar-text' => 'rgba(255, 255, 255, .65)', '--erp-sidebar-sub-text' => 'rgba(255, 255, 255, .6)',
        '--erp-sidebar-strong' => '#ffffff', '--erp-sidebar-line' => 'rgba(255, 255, 255, .15)', '--erp-sidebar-scroll' => 'rgba(255, 255, 255, .45)',
        '--erp-alert-success-text' => '#3c763d', '--erp-alert-success-bg' => '#dff0d8', '--erp-alert-success-border' => '#d6e9c6',
        '--erp-alert-danger-text' => '#a94442', '--erp-alert-danger-bg' => '#f2dede', '--erp-alert-danger-border' => '#ebccd1',
        '--erp-font' => '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
        '--erp-font-heading' => '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
        // spots that had their own colour before
        '--erp-notice' => '#177fbf',
        '--erp-badge' => 'linear-gradient(135deg, #ff6a00, #ee0979)',
        '--erp-dash-ok' => '#37a000', '--erp-dash-ok-rgb' => '55, 160, 0', '--erp-dash-ok-dark' => '#2e8a00',
        '--erp-dash-bad' => '#e74c3c', '--erp-dash-bad-rgb' => '231, 76, 60',
        '--erp-dash-late' => '#f39c12', '--erp-dash-late-rgb' => '243, 156, 18',
        '--erp-dash-info' => '#3498db', '--erp-dash-info-rgb' => '52, 152, 219',
        '--erp-dash-title' => '#f1c40f', '--erp-dash-in' => '#00fa19', '--erp-dash-out' => '#d32b01',
    ];

    const FONTS = [
        'arial-black' => '"Arial Black", "Arial Bold", Gadget, Arial, sans-serif',
        'arial'       => 'Arial, Helvetica, sans-serif',
    ];

    private static $cache = [];

    /** Saved settings of a company merged over the defaults */
    public static function forCompany($companyId)
    {
        if (!array_key_exists($companyId, self::$cache)) {
            try {
                $row = Erp_theme_setting::where('ets_company_id', $companyId)->first();
            } catch (\Throwable $e) {
                $row = null; // table not created yet: keep the default theme instead of breaking every page
            }
            self::$cache[$companyId] = self::clean(array_merge(self::DEFAULTS, $row ? (array) json_decode($row->ets_settings, true) : []));
        }
        return self::$cache[$companyId];
    }

    /** Keeps only known keys with valid values (colours must be #rrggbb, so nothing else can reach the CSS) */
    public static function clean(array $settings)
    {
        $clean = [];
        foreach (self::DEFAULTS as $key => $default) {
            $value = $settings[$key] ?? $default;
            if ($key === 'heading_font') {
                $clean[$key] = array_key_exists($value, self::FONTS) ? $value : $default;
            } elseif ($key === 'style') {
                $clean[$key] = array_key_exists($value, self::STYLES) ? $value : $default;
            } else {
                $clean[$key] = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value) ? strtolower($value) : $default;
            }
        }
        return $clean;
    }

    /** False when the company chose the previous ERP look (then theme-felna.css is switched off) */
    public static function usesNewStyle()
    {
        return !Auth::check() || self::forCompany(Auth::user()->au_company_id)['style'] !== 'classic';
    }

    /** <style> with the CSS variables that differ from the default theme (empty when nothing changed) */
    public static function styleTag()
    {
        if (!Auth::check()) {
            return '';
        }
        $settings = self::forCompany(Auth::user()->au_company_id);
        if ($settings['style'] === 'classic') {
            $vars = self::CLASSIC_TOKENS;
        } else {
            $vars = self::cssVariables($settings);
        }
        if (empty($vars)) {
            return '';
        }
        $css = '';
        foreach ($vars as $name => $value) {
            $css .= $name . ':' . $value . ';';
        }
        return '<style id="erp-theme-colors">:root{' . $css . '}</style>';
    }

    /** Forget the cached settings of a company (after save / reset) */
    public static function forget($companyId)
    {
        unset(self::$cache[$companyId]);
    }

    /** CSS variables for every setting that differs from the default */
    public static function cssVariables(array $s)
    {
        $d = self::DEFAULTS;
        $v = [];

        if ($s['primary'] !== $d['primary']) {
            $p = $s['primary'];
            $v['--erp-green-btn'] = $p;
            $v['--erp-green-btn-dark'] = self::shade($p, .18);
            $v['--erp-green'] = $p;
            $v['--erp-green-rgb'] = implode(', ', self::rgb($p));
            $v['--erp-selection'] = self::tint($p, .7);
            $v['--erp-green-tint'] = self::tint($p, .9);
            $v['--erp-block-green'] = self::shade($p, .08);
            $v['--erp-on-primary'] = self::bestText($p);
            $v['--erp-on-block-green'] = self::bestText(self::shade($p, .08));
            $v['--erp-alert-success-text'] = self::shade($p, .55);
            $v['--erp-alert-success-bg'] = self::tint($p, .9);
            $v['--erp-alert-success-border'] = self::tint($p, .7);
        }
        if ($s['accent'] !== $d['accent']) {
            $a = $s['accent'];
            $v['--erp-red'] = $a;
            $v['--erp-red-dark'] = self::shade($a, .18);
            $v['--erp-block-red'] = self::shade($a, .12);
            $v['--erp-on-accent'] = self::bestText($a);
            $v['--erp-on-block-red'] = self::bestText(self::shade($a, .12));
            $v['--erp-alert-danger-text'] = self::shade($a, .5);
            $v['--erp-alert-danger-bg'] = self::tint($a, .9);
            $v['--erp-alert-danger-border'] = self::tint($a, .7);
        }
        if ($s['sidebar'] !== $d['sidebar']) {
            $sb = $s['sidebar'];
            $dark = self::bestText($sb) === '#ffffff';
            $sub = $dark ? self::shade($sb, .25) : self::shade($sb, .08);
            $v['--erp-green-dark'] = $sb;
            $v['--erp-green-darker'] = $sub;
            $v['--erp-green-hover'] = $dark ? self::tint($sb, .12) : self::shade($sb, .12);
            $v['--erp-on-sidebar'] = self::bestText($sb);
            $v['--erp-sidebar-text'] = self::rgba(self::bestText($sb), .88);
            $v['--erp-sidebar-sub-text'] = self::rgba(self::bestText($sub), .8);
            $v['--erp-sidebar-strong'] = self::bestText($sb);
            $v['--erp-sidebar-line'] = self::rgba(self::bestText($sb), .15);
            $v['--erp-sidebar-scroll'] = self::rgba(self::bestText($sb), .45);
        }
        if ($s['search'] !== $d['search']) {
            $g = $s['search'];
            $v['--erp-green-light'] = $g;
            $v['--erp-green-light-dark'] = self::shade($g, .22);
            $v['--erp-on-search'] = self::bestText($g);
            $v['--erp-on-search-hover'] = self::bestText(self::shade($g, .22));
        }
        if ($s['page'] !== $d['page']) {
            $v['--erp-page'] = $s['page'];
        }
        if ($s['text'] !== $d['text']) {
            $v['--erp-text'] = $s['text'];
            $v['--erp-black'] = $s['text'];
            $v['--erp-on-black'] = self::bestText($s['text']);
        }
        if ($s['heading_font'] !== $d['heading_font']) {
            $v['--erp-font-heading'] = self::FONTS[$s['heading_font']];
        }

        return $v;
    }

    // ---------------- colour maths ----------------

    private static function rgb($hex)
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    private static function hex(array $rgb)
    {
        return sprintf('#%02x%02x%02x', ...array_map(function ($c) { return max(0, min(255, (int) round($c))); }, $rgb));
    }

    /** Mix with black: amount 0..1 */
    public static function shade($hex, $amount)
    {
        return self::hex(array_map(function ($c) use ($amount) { return $c * (1 - $amount); }, self::rgb($hex)));
    }

    /** Mix with white: amount 0..1 */
    public static function tint($hex, $amount)
    {
        return self::hex(array_map(function ($c) use ($amount) { return $c + (255 - $c) * $amount; }, self::rgb($hex)));
    }

    private static function rgba($hex, $alpha)
    {
        [$r, $g, $b] = self::rgb($hex);
        return "rgba($r, $g, $b, $alpha)";
    }

    private static function luminance($hex)
    {
        $lin = array_map(function ($c) { $c /= 255; return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4); }, self::rgb($hex));
        return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
    }

    public static function contrast($a, $b)
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** White or near-black, whichever reads better on the colour */
    public static function bestText($bg)
    {
        return self::contrast('#ffffff', $bg) >= self::contrast('#1a1a1a', $bg) ? '#ffffff' : '#1a1a1a';
    }
}
