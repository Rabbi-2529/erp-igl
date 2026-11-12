<script src="{{ asset('assets/js/jquery-ui.min.js') }}" type="text/javascript"></script> 
<!-- bootstrap js -->
<script src="{{ asset('assets/js/bootstrap.min.js') }}" type="text/javascript"></script>  
<!-- pace js -->
<script src="{{ asset('assets/js/pace.min.js') }}" type="text/javascript"></script>  
<!-- SlimScroll -->
<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}" type="text/javascript"></script>  
<!-- bootstrap timepicker -->
<script src="{{ asset('assets/js/jquery-ui-timepicker-addon.min.js') }}" type="text/javascript"></script>
<!-- Calendars across the ERP: month and year lists in every calendar, and they keep working inside pop-up windows -->
<script>
(function ($) {
    if ($.datepicker) {
        // read when a calendar opens, so this reaches every page; a page's own options still win
        $.datepicker.setDefaults({
            changeMonth: true,
            changeYear: true,
            yearRange: '-80:+20',      // date of birth to future due dates
            showOtherMonths: true,
            selectOtherMonths: true
        });
    }
    // Bootstrap 3 pulls focus back into an open pop-up; the calendar sits outside it (on <body>),
    // so its month / year lists closed at once. Let the calendar, select2 and alerts keep focus.
    if ($.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype.enforceFocus = function () {
            $(document).off('focusin.bs.modal').on('focusin.bs.modal', $.proxy(function (e) {
                if (document !== e.target && this.$element[0] !== e.target && !this.$element.has(e.target).length
                    && !$(e.target).closest('.ui-datepicker, .select2-container, .select2-dropdown, .swal2-container').length) {
                    this.$element.trigger('focus');
                }
            }, this));
        };
    }
})(jQuery);
</script>
<!-- select2 js -->
<script src="{{ asset('assets/js/select2.min.js') }}" type="text/javascript"></script>
<!-- ChartJs JavaScript -->
<script src="{{ asset('assets/js/Chart.min.js?v=2.5') }}" type="text/javascript"></script>
<script>
// Dark mode: chart text and grid lines in light ink
if (window.Chart && document.documentElement.getAttribute('data-theme') === 'dark') {
    Chart.defaults.global.defaultFontColor = '#b9c4be';
    Chart.defaults.scale.gridLines.color = 'rgba(255, 255, 255, 0.08)';
    Chart.defaults.scale.gridLines.zeroLineColor = 'rgba(255, 255, 255, 0.25)';
}
</script>
<!-- SweetAlert JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
<!-- DataTables JavaScript -->
<script src="{{ asset('assets/datatables/dataTables.min.js') }}"></script>
<!-- Table Head Fixer -->
<script src="{{ asset('assets/js/tableHeadFixer.js') }}" type="text/javascript"></script> 
<!-- Admin Script -->
<script src="{{ asset('assets/js/frame.js') }}" type="text/javascript"></script> 
<script src="{{ asset('assets/js/bootstrap-toggle.min.js') }}" type="text/javascript"></script> 
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert/sweetalert.min.js') }}" type="text/javascript"></script>
<!-- Custom Theme JavaScript -->
<script src="{{ asset('assets/js/custom.js') }}" type="text/javascript"></script>
<!-- summernote js -->
<script src="{{ asset('assets/js/mousetrap-master/mousetrap.js') }}" ></script>
<script src="{{ asset('assets/js/print.js') }}" ></script>
<script src="{{ asset('assets/js/jQuery-print/jQuery.print.min.js') }}"></script>
<script src="{{ asset('assets/js/jstree.min.js') }}" ></script>
{{-- Sidebar: keep its scroll position between pages and keep the current menu item in view.
     Runs after frame.js has set up the sidebar scroll area. --}}
<script>
$(function () {
    var sidebar = $('.main-sidebar .sidebar');
    var key = 'erp.sidebarScroll';
    if (!sidebar.length) {
        return;
    }

    var lastTop = 0;

    function scrollSidebarTo(top) {
        top = lastTop = Math.max(0, Math.round(top));
        if ($.fn.slimScroll && sidebar.parent().hasClass('slimScrollDiv')) {
            sidebar.slimScroll({ scrollTo: top + 'px' });
        } else {
            sidebar.scrollTop(top);
        }
    }

    var saved = null;
    try { saved = sessionStorage.getItem(key); } catch (e) {}
    if (saved !== null) {
        scrollSidebarTo(parseInt(saved, 10) || 0);
    }

    // If none of the current page's menu items is in sight, centre the last one
    var viewTop = sidebar.scrollTop();
    var viewHeight = sidebar.innerHeight();
    var itemTops = sidebar.find('li.active:not(.treeview) > a:visible').map(function () {
        return $(this).offset().top - sidebar.offset().top + sidebar.scrollTop();
    }).get();
    var inSight = itemTops.some(function (top) {
        return top >= viewTop && top + 40 <= viewTop + viewHeight;
    });
    if (itemTops.length && !inSight) {
        scrollSidebarTo(itemTops[itemTops.length - 1] - viewHeight / 2);
    }

    // Remember where the sidebar is scrolled to
    sidebar.on('slimscrolling scroll', function () {
        lastTop = sidebar.scrollTop();
    });

    // frame.js rebuilds the sidebar scroll area on every resize, which sends it back to the top
    $(window).on('resize', function () {
        scrollSidebarTo(lastTop);
    });

    // Collapsing hides the submenus (and the scroll), so go back to the same place when it opens again
    var beforeCollapse = 0;
    $('body').on('collapsed.pushMenu', function () {
        beforeCollapse = lastTop;
    }).on('expanded.pushMenu', function () {
        setTimeout(function () { scrollSidebarTo(beforeCollapse); }, 350);
    });

    $(window).on('pagehide beforeunload', function () {
        try { sessionStorage.setItem(key, sidebar.scrollTop()); } catch (e) {}
    });
});
</script>

<script>
$(document).ready(function(){
    $('.opener').on('click', function(e){
        e.preventDefault(); // stop default link behavior
        var url = $(this).attr('href');
        window.open(url, '_blank'); // opens in a new tab reliably
    });
});
</script>

<style>
    /* a number opened from a list (.opener link) stays marked, so after working in the new tab you can see where you were */
    :root { --erp-opener-last: #fff1b8; --erp-opener-line: #e0a800; }
    html[data-theme="dark"] { --erp-opener-last: #4a3d10; --erp-opener-line: #f0c040; }
    .table > tbody > tr.opener-last-row > td { background-color: var(--erp-opener-last) !important; }
    .table > tbody > tr.opener-last-row > td:first-child { box-shadow: inset 4px 0 0 var(--erp-opener-line); }
    a.opener.opener-last { font-weight: 700; }
    a.opener.opener-seen::after { content: "\f00c"; font-family: FontAwesome; font-size: 11px; margin-left: 5px; color: var(--erp-muted); font-weight: normal; }
    .opener-tag { display: inline-block; margin-left: 6px; padding: 1px 6px; border-radius: 3px; font-size: 11px; line-height: 1.5; white-space: nowrap;
        background: var(--erp-opener-line); color: #1a1a1a; }
</style>
<script>
// Remembers which numbers were opened from this list today: the last one is highlighted with its row, the others get a tick.
// Kept per page and per day in this browser (cleared the next day). The links still open exactly as before.
$(function () {
    if (!$('a.opener').length) { return; }
    var d = new Date(), today = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    var KEY = 'erp.opened.' + location.pathname, mem = { day: today, seen: [], last: null };   // mem: used when the browser keeps nothing (private window)
    function load() {
        try {
            var raw = localStorage.getItem(KEY);
            if (raw === null) { return mem; }
            var s = JSON.parse(raw);
            return s && s.day === today && s.seen ? s : { day: today, seen: [], last: null };
        } catch (e) { return mem; }
    }
    function paint() {
        var s = load();
        $('a.opener').each(function () {
            var $a = $(this), k = $a.attr('href'), last = k === s.last;
            $a.toggleClass('opener-last', last).toggleClass('opener-seen', !last && s.seen.indexOf(k) !== -1);
            $a.siblings('.opener-tag').remove();
            if (last) { $a.after('<span class="opener-tag">Last opened</span>'); }
            $a.closest('tr').toggleClass('opener-last-row', last);
        });
    }
    function remember(a) {
        var s = load(), k = $(a).attr('href');
        s.last = k;
        if (s.seen.indexOf(k) === -1) { s.seen.push(k); if (s.seen.length > 500) { s.seen.shift(); } }
        mem = s;
        try { localStorage.setItem(KEY, JSON.stringify(s)); } catch (e) { /* private window: shown until the page is reloaded */ }
        paint();
    }
    $(document).on('click', 'a.opener', function () { remember(this); });
    $(document).on('auxclick', 'a.opener', function (e) { if (e.which === 2) { remember(this); } });   // middle click
    $(document).on('draw.dt', paint);   // next page / search in the table
    $(window).on('storage', function (e) { if (e.originalEvent && e.originalEvent.key === KEY) { paint(); } });   // same list open in another tab
    paint();
});
</script>

<script>
$(document).ready(function () {
    $('#search-input').on('input', function () {
        var q = $(this).val();
        if (q.length == 11) {
            $.ajax({
                type: 'GET',
                url: '{{ route('search') }}',
                data: {q: q},
                success: function (data) {
                    var buttonHtml = '';
                    if (data.status == 'success') {
                        var url = data.url;
                        buttonHtml = '<button id="follow-up-btn" class="btn btn-success">Follow Up</button>';
                    } else if (data.status == 'error') {
                        var message = data.message;
                        if (message == 'No matching records found') {
                            var url = data.url;
                            buttonHtml = '<button id="add-btn" class="btn btn-primary">Add</button>';
                        } else {
                            alert(message);
                            return;
                        }
                    }
                    $('#search-result').html(buttonHtml);

                    // Attach click event to open in new tab
                    $('#follow-up-btn, #add-btn').on('click', function () {
                        window.open(url, '_blank');
                    });
                },
                error: function () {
                    alert('An error occurred while searching');
                }
            });
        } else {
            $('#search-result').empty();
        }
    });
});
</script>
@if(auth()->check() && auth()->user()->au_user_type != 4)
<script>
$(document).ready(function(){

    // Disable text selection
    $('body').css({
        '-webkit-user-select': 'none',
        '-moz-user-select': 'none',
        '-ms-user-select': 'none',
        'user-select': 'none'
    });

    $(document).on('contextmenu', function(e) {
        e.preventDefault();
        toastr.warning('Copying is disabled');
        return false;
    });

    $(document).on('copy cut paste', function(e) {
        e.preventDefault();
        toastr.warning('Copy/Paste is disabled');
        return false;
    });

    $(document).on('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88) {
                e.preventDefault();
                toastr.warning('Copying is disabled');
                return false;
            }
        }
    });

    $(document).on('dragstart selectstart', function(e) {
        e.preventDefault();
        return false;
    });

    // Print Screen
    $(document).on('keydown', function(e) {
        if (e.keyCode === 44) {
            e.preventDefault();
            toastr.warning('Screenshot is disabled');
            return false;
        }
    });

    // F12
    $(document).on('keydown', function(e) {
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
    });

});
</script>
@endif

<!-- Phones and tablets: wide tables scroll sideways inside their panel instead of running off the screen -->
<script>
(function ($) {
    function wrapTables(scope) {
        $(scope || document).find('table.table').each(function () {
            var $t = $(this);
            if ($t.parent().hasClass('erp-table-scroll') || $t.closest('.table-responsive, .dataTables_scroll, .ui-datepicker, .erp-no-scroll').length) { return; }
            $t.wrap('<div class="erp-table-scroll"></div>');
        });
    }
    $(window).on('load', function () { wrapTables(); });
    $(document).on('init.dt', function (e, settings) { if (settings && settings.nTable) { wrapTables($(settings.nTable).parent()); } });
})(jQuery);
</script>
