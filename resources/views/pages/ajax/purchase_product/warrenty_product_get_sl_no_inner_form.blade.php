
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{ $product_details->inv_pro_name ?? 'N/A' }}</h4>
</div>
<div class="modal-body">
    <div class="box">
        <div class="box-body">
                <div class="col-sm-offset-1 col-sm-10">
                    <div id="all-added-warrenty-product-id">
                        <div class="col-sm-12" style="margin-top: 5px;">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    {{-- Scanner box: each scan ends with Enter. Scans are queued and saved one at a time,
                                         so fast scanning (30+ serials) does not lose or merge serial numbers. --}}
                                    <input type="text" class="form-control pur_warrenty_pro_sl_no_s" id="pur_warrenty_pro_sl_no"
                                           data-pro-id="{{ $product->inv_pro_det_id }}" autocomplete="off"
                                           placeholder="Scan or type serial number and press Enter (you can also paste many lines)">
                                    <div style="margin-top: 6px; overflow: hidden;">
                                        <span id="sl_scan_msg" style="font-weight: bold;"></span>
                                        <span class="pull-right">
                                            Added: <b id="sl_scan_count">{{ count(array_filter($product_sl_no ?? [])) }}</b>
                                            <span id="sl_scan_pending" style="color: #8a6d3b;"></span>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                                <ul class="list-group show-added-list list-inline">
                                    @if(!empty($product_sl_no))
                                        @foreach (array_values(array_filter($product_sl_no)) as $sl_no)
                                            <li class="list-group-item" data-sl="{{ $sl_no }}" style="margin-top: 5px;">{{ $loop->iteration }} | {{ $sl_no }} |
                                            <a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="remove_product_sl('{{ $product->inv_pro_det_id }}', '{{ $sl_no }}')"><span class="fa fa-remove" style="font-size: 20px; color: red;"></span></a>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>

<script>
    (function () {
        var $input = $('#pur_warrenty_pro_sl_no');
        var proId = $input.data('pro-id');
        var queue = [];
        var busy = false;

        window.slScanMsg = function (text, ok) {
            $('#sl_scan_msg').text(text).css('color', ok ? '#3c763d' : '#a94442');
        };

        function addedSerials() {
            return $('#all-added-warrenty-product-id .show-added-list li[data-sl]').map(function () {
                return String($(this).attr('data-sl')).toUpperCase(); // attr, not data(): keeps "00123" as text
            }).get();
        }

        function refreshCounter() {
            $('#sl_scan_count').text(addedSerials().length);
            $('#sl_scan_pending').text(queue.length || busy ? '(saving ' + (queue.length + (busy ? 1 : 0)) + '...)' : '');
        }

        function processQueue() {
            if (busy) return;
            if (!queue.length) {
                refreshCounter();
                if (typeof getCartProduct === 'function') getCartProduct();
                $input.focus();
                return;
            }
            busy = true;
            refreshCounter();
            var sl = queue.shift();
            $.when(check_sl_no(sl, proId)).always(function () {
                busy = false;
                processQueue();
            });
        }

        function takeInput(raw) {
            // a scanner may send several serials (QR code / paste) separated by new lines, commas or tabs
            var parts = String(raw).split(/[\r\n,\t]+/);
            $input.val('');
            var added = addedSerials();
            parts.forEach(function (part) {
                var sl = $.trim(part);
                if (sl === '') return; // double Enter / empty scan: ignore
                var key = sl.toUpperCase();
                if (added.indexOf(key) !== -1 || queue.map(function (q) { return q.toUpperCase(); }).indexOf(key) !== -1) {
                    slScanMsg('Duplicate ignored: ' + sl, false);
                    return;
                }
                queue.push(sl);
            });
            processQueue();
        }

        $input.on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault(); // never submit the page form
                takeInput($input.val());
            }
        });
        $input.on('paste', function (e) {
            // single-line inputs drop line breaks on paste, so read the clipboard text directly
            var cb = e.originalEvent && e.originalEvent.clipboardData;
            var text = cb ? cb.getData('text') : '';
            if (/[\r\n,\t]/.test(text)) {
                e.preventDefault();
                takeInput($input.val() + '\n' + text); // not via the input: it would strip the line breaks again
            }
        });

        // keep the counter right when a serial is removed from the list
        var list = document.querySelector('#all-added-warrenty-product-id .show-added-list');
        if (list && window.MutationObserver) {
            new MutationObserver(refreshCounter).observe(list, { childList: true });
        }

        $('#warrenty_product_get_sl_no').one('shown.bs.modal', function () { $input.focus(); });
        $input.focus();
    })();
</script>
