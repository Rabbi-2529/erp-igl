{{-- Ledger Categories > Show: the ledgers of one category with their cash in / cash out (from Ledger > Cash) --}}
@php
    $totals = $totals ?? collect();
    $sumIn = 0;
    $sumOut = 0;
@endphp
<div class="row">
    <div class="col-sm-12">
        <b>Ledger Category:</b> {{ optional($category ?? null)->inv_ledg_cat_category_name ?? '' }}
    </div>
</div>

<div class="row" style="margin-top: 15px;">
    <div class="col-sm-12">
        @if($ledgers->isEmpty())
            <p class="text-muted text-center" style="padding: 15px 0;">No active ledgers in this category yet. Add them on Accounts &gt; Ledger &gt; Ledger.</p>
        @else
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: center;">Ledger Name</th>
                        <th style="text-align: center;">Cash In</th>
                        <th style="text-align: center;">Cash Out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ledgers as $ledger)
                        @php
                            $t = $totals->get($ledger->inv_ledg_id);
                            $in = $t ? (float) $t->cash_in : 0;
                            $out = $t ? (float) $t->cash_out : 0;
                            $sumIn += $in;
                            $sumOut += $out;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td>{{ $ledger->inv_ledg_ledger_name }}</td>
                            <td class="text-right">{{ number_format($in, 2) }}</td>
                            <td class="text-right">{{ number_format($out, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: center; font-weight: bolder;">#</td>
                        <td style="text-align: right; font-weight: bolder;">Total:</td>
                        <td style="text-align: right; font-weight: bolder;">{{ number_format($sumIn, 2) }}</td>
                        <td style="text-align: right; font-weight: bolder;">{{ number_format($sumOut, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>
