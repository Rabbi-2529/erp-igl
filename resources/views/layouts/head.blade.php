<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- Light / Dark mode: "only light" stops phones and browsers forcing their own dark colours (that hid text);
     the header button switches to the ERP's own dark design. Set before any CSS so the page never flashes. -->
<meta name="color-scheme" content="only light" id="erp-color-scheme">
<script>
(function () {
    var mode = 'light';
    try { mode = localStorage.getItem('erp.mode') === 'dark' ? 'dark' : 'light'; } catch (e) {}
    if (mode === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.getElementById('erp-color-scheme').setAttribute('content', 'dark');
    }
})();
</script>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>

<!-- Favicon and touch icons -->
<link rel="apple-touch-icon" type="image/x-icon" href="{{ asset('assets/dist/img/ico/apple-touch-icon-57-precomposed.png') }}">

<link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="{{ asset('assets/dist/img/ico/apple-touch-icon-72-precomposed.png') }}">
<link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="{{ asset('assets/dist/img/ico/apple-touch-icon-114-precomposed.png') }}">
<link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="{{ asset('assets/dist/img/ico/apple-touch-icon-144-precomposed.png') }}">

<!-- Start Global Mandatory Style-->
<link rel="shortcut icon" href="{{ asset('assets/img/icons/favicon.ico') }}" type="image/x-icon">

<!-- jquery ui css -->
<link href="{{ asset('assets/css/jquery-ui.min.css') }}" rel="stylesheet" type="text/css"/>
<!-- Bootstrap --> 
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>

<!-- Font Awesome 4.7.0 -->
<link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>

<!-- sliderAccess css -->
<link href="{{ asset('assets/css/jquery-ui-timepicker-addon.min.css') }}" rel="stylesheet" type="text/css"/> 
<link href="{{ asset('assets/css/wickedpicker.min.css') }}" rel="stylesheet" type="text/css"/>
<!-- slider  -->
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/> 
<!-- DataTables CSS -->
<link href="{{ asset('assets/datatables/dataTables.min.css') }}" rel="stylesheet" type="text/css"/> 
    <!-- pe-icon-7-stroke -->
<link href="{{ asset('assets/css/pe-icon-7-stroke.css') }}" rel="stylesheet" type="text/css"/> 
<!-- themify icon css -->
<link href="{{ asset('assets/css/themify-icons.css') }}" rel="stylesheet" type="text/css"/> 
<!-- Pace css -->
<link href="{{ asset('assets/plugins/toastr/toastr.css') }}" rel=stylesheet type="text/css"/>

<link href="{{ asset('assets/css/bootstrap-toggle.min.css') }}" rel="stylesheet" type="text/css"/>
<!-- Theme style -->
<link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css"/>

<link href="{{ asset('assets/js/sweetalert/sweetalert.css') }}" rel="stylesheet" type="text/css"/>

<!-- summernote css -->
<link href="{{ asset('assets/css/print.css') }}" rel="stylesheet" type="text/css"/>
<!-- FelnaTech theme (red, green, white; Arial Black headings): loaded last so it overrides -->
<!--   ("Previous style" on Theme Colors switches it off with media="not all": the original ERP look comes back) -->
<link id="erp-theme-css" href="{{ asset('assets/css/theme-felna.css') }}?v={{ @filemtime(base_path('assets/css/theme-felna.css')) }}" rel="stylesheet" type="text/css" @unless(\App\Models\Erp_theme_setting::usesNewStyle()) media="not all" @endunless/>
<!-- Company colours from Theme Colors (nothing is printed while the default theme is used) -->
{!! \App\Models\Erp_theme_setting::styleTag() !!}
<!-- Dark mode (only used while the header button is on dark) and phone / tablet layout -->
<link href="{{ asset('assets/css/theme-dark.css') }}?v={{ @filemtime(base_path('assets/css/theme-dark.css')) }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('assets/css/erp-responsive.css') }}?v={{ @filemtime(base_path('assets/css/erp-responsive.css')) }}" rel="stylesheet" type="text/css"/>
<!-- jQuery -->
<script src="{{ asset('assets/js/jquery-3.4.1.min.js?v=3.4.1') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/wickedpicker.min.js') }}" ></script>
<script src="{{ asset('assets/js/jquery.validate.min.js') }}" type="text/javascript"></script>