@extends('layouts.master')

@section('title', 'Sell Report')
@section('report_class','active')
@section('sales_report_class','active')

@section('content')

<style>
    /* ===== IGL Web · Sell Reports theme ===== */
    .igl-page { --igl-primary:#4f46e5; --igl-primary-dark:#3730a3; --igl-accent:#06b6d4;
        --igl-ink:#1e293b; --igl-muted:#64748b; --igl-line:#e2e8f0; --igl-soft:#f8fafc; }

    .igl-card { border:0; border-radius:16px; overflow:hidden;
        box-shadow:0 10px 30px -12px rgba(30,41,59,.18); background:#fff; }

    .igl-card-head { position:relative;
        background:linear-gradient(120deg,var(--igl-primary) 0%,var(--igl-primary-dark) 55%,#1e1b4b 100%);
        color:#fff; padding:22px 26px; }
    .igl-card-head::after { content:""; position:absolute; right:-40px; top:-60px;
        width:180px; height:180px; border-radius:50%; background:rgba(255,255,255,.08); }
    .igl-brand { display:flex; align-items:center; gap:14px; }
    .igl-logo { width:46px; height:46px; border-radius:12px; display:grid; place-items:center;
        background:rgba(255,255,255,.15); backdrop-filter:blur(4px); font-size:22px; }
    .igl-brand h4 { margin:0; font-weight:700; letter-spacing:.3px; line-height:1.15; }
    .igl-brand small { color:rgba(255,255,255,.75); font-weight:500; letter-spacing:.5px; }

    .igl-body { padding:24px 26px; }

    /* Search / filter bar */
    .igl-filter { background:var(--igl-soft); border:1px solid var(--igl-line);
        border-radius:14px; padding:18px 20px; margin-bottom:22px; }
    .igl-filter label { font-size:12px; font-weight:600; text-transform:uppercase;
        letter-spacing:.6px; color:var(--igl-muted); margin-bottom:6px; display:block; }
    .igl-filter .form-control { height:44px; border-radius:10px; border:1px solid var(--igl-line);
        box-shadow:none; transition:border-color .15s, box-shadow .15s; }
    .igl-filter .form-control:focus { border-color:var(--igl-primary);
        box-shadow:0 0 0 3px rgba(79,70,229,.15); }
    .igl-btn { height:44px; border-radius:10px; font-weight:600; padding:0 22px;
        display:inline-flex; align-items:center; gap:8px; border:0; }
    .igl-btn-search { background:var(--igl-primary); color:#fff; }
    .igl-btn-search:hover { background:var(--igl-primary-dark); color:#fff; }
    .igl-btn-download { background:#fff; color:var(--igl-primary);
        border:1.5px solid var(--igl-primary); }
    .igl-btn-download:hover { background:var(--igl-primary); color:#fff; }

    /* Table */
    #example6 { border-collapse:separate !important; border-spacing:0; }
    #example6 thead th { background:var(--igl-ink); color:#fff; border:0 !important;
        font-size:12px; text-transform:uppercase; letter-spacing:.5px; font-weight:600;
        padding:14px 12px; vertical-align:middle; }
    #example6 tbody td { border-top:0 !important; border-bottom:1px solid var(--igl-line) !important;
        padding:12px; vertical-align:middle; color:var(--igl-ink); }
    #example6 tbody tr:hover td { background:#f5f7ff; }
    .igl-invoice { font-weight:600; color:var(--igl-primary); }
    .igl-amount { font-weight:700; color:#0f766e; white-space:nowrap; }
    .igl-foot td { background:var(--igl-soft); font-weight:800 !important;
        color:var(--igl-ink); border-top:2px solid var(--igl-primary) !important; padding:14px 12px; }
    .igl-total-badge { display:inline-block; background:#ecfdf5; color:#047857;
        padding:6px 14px; border-radius:999px; font-weight:800; }

    /* Dropdown action button */
    .igl-action .btn { border-radius:8px; }

    /* Footer note */
    .igl-footnote { text-align:center; color:var(--igl-muted); font-size:12px;
        margin-top:20px; padding-top:16px; border-top:1px dashed var(--igl-line); }
    .igl-footnote b { color:var(--igl-primary); }

    /* Alerts */
    .igl-page .alert { border:0; border-radius:12px; }
</style>

<div class="igl-page">
    <br>
    <div class="row">
        <div class="col-sm-12">
            <div class="igl-card">

                <div class="igl-card-head">
                    <div class="igl-brand">
                        <div class="igl-logo">💠</div>
                        <div>
                            <h4>IGL Web <span style="opacity:.6;font-weight:400;">— Sell Reports</span></h4>
                            <small>Sales overview &amp; invoice history</small>
                        </div>
                    </div>
                </div>

                <div class="igl-body">

                    @if(session()->has('check'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('check') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    @endif
                    @if(session()->has('sub_err'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('sub_err') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    @endif
                    @if(session()->has('confirm'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('confirm') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    @endif

                    {{-- Filter bar --}}
                    <div class="igl-filter">
                        <form action="" method="get">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="_token">
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="start_date">Start Date</label>
                                    <input type="text" name="start_date" data-date-format="yyyy-mm-dd" autocomplete="off"
                                           value="{{ request('start_date') }}" class="form-control" id="start_date" placeholder="yyyy-mm-dd">
                                </div>
                                <div class="col-sm-4">
                                    <label for="end_date">End Date</label>
                                    <input type="text" name="end_date" data-date-format="yyyy-mm-dd" autocomplete="off"
                                           value="{{ request('end_date') }}" class="form-control" id="end_date" placeholder="yyyy-mm-dd">
                                </div>
                                <div class="col-sm-4">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="igl-btn igl-btn-search" name="searchbtn">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                        <a href="{{ route('reports.sell-reports-download') }}" class="igl-btn igl-btn-download">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table id="example6" class="table table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Invoice No</th>
                                    <th>Issue Date</th>
                                    <th>Issue By</th>
                                    <th>Cus Name</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sl = 0;
                                    $balance = 0;
                                    $total = 0;
                                    $total_amount = 0;
                                @endphp
                                @foreach ($sell_reports as $sell)
                                    @php($total = App\Models\Inv_product_inventory::getTotalDebit($sell->inv_pro_inv_invoice_no))
                                    @php($total_amount = $total - App\Models\Inv_product_inventory::get_discount_amount($sell->inv_pro_inv_invoice_no))
                                    <tr>
                                        <td>{{ ++$sl }}</td>
                                        <td class="igl-invoice">{{ $sell->inv_pro_inv_invoice_no }}</td>
                                        <td>{{ $sell->inv_pro_inv_issue_date }}</td>
                                        <td>{{ isset($sell->users->au_name) ? $sell->users->au_name : '' }}</td>
                                        <td>{{ $sell->getCustomerInfo->inv_cus_name ?? 'N/A' }}</td>
                                        <td>{{ $sell->inv_pro_inv_tran_desc }}</td>
                                        <td class="text-right igl-amount">{{ number_format($total_amount ?? 0, 2) }}</td>
                                        <td class="text-center igl-action">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-info btn-xs">Action</button>
                                                <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="sell_reports('{{ $sell->inv_pro_inv_invoice_no }}')">Details</a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    @if (Auth::user()->au_user_type == 4)
                                                    <li>
                                                        <a href="{{ route('sell_edit.sell-pro-edit',['invoice' => $sell->inv_pro_inv_invoice_no]) }}">Edit</a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    @endif
                                                    <li>
                                                        <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}?print=1" target="_blank">Print</a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    <li>
                                                        <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}?view=1" target="_blank">View</a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    <li>
                                                        <a href="{{ route('reports.sell-pdf',['invoice' => $sell->inv_pro_inv_invoice_no]) }}" target="_blank">Download</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @php($balance = $balance + App\Models\Inv_product_inventory::getTotalDebit($sell->inv_pro_inv_invoice_no))
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="igl-foot">
                                    <td colspan="6" style="text-align:right;">Grand Total</td>
                                    <td class="text-right"><span class="igl-total-badge">{{ number_format($balance,2) }}</span></td>
                                    <td class="text-center">—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="igl-footnote">
                        Powered by <b>IGL Web</b> · Sell Reports Module
                    </div>

                </div>{{-- /.igl-body --}}
            </div>{{-- /.igl-card --}}
        </div>
    </div>
</div>{{-- /.igl-page --}}

{{-- Modal --}}
<div class="modal fade" id="sellReports" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border:0;border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:#4f46e5;color:#fff;border:0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;">&times;</button>
                <h4 class="modal-title">Invoice Details</h4>
            </div>
            <div class="modal-body" id="sellModalDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#start_date").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });
        $("#end_date").datepicker({
            daysOfWeekHighlighted: "7",
            todayHighlight: true,
            autoclose: true,
        });

        $('#example6').DataTable({
            rowReorder: { selector: 'td:nth-child(2)' },
            responsive: true
        });
    });

    function sell_reports(sell_id) {
        let url = "{{ route('reports.sell-reports-ajax') }}";
        var _token = $("#_token").val();
        $.ajax({
            type: "GET",
            url: url,
            data: { sell_id: sell_id, _token: _token },
            success: function (result) {
                $("#sellModalDetails").html(result);
                $("#sellReports").modal("show");
            }
        });
    }
</script>

@endsection
