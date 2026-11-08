<?php

namespace App\Http\Controllers\Lc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_supplier;
use App\Models\Inv_product_detail;
use App\Models\Lc_currency;
use App\Models\Lc_file;
use App\Models\Lc_file_item;
use Carbon\Carbon;
use Auth;
use DB;

/**
 * File Opening: an import file per supplier proforma invoice (PI) with its products.
 */
class LcFileController extends Controller
{
    use LcHelpers;

    public function index()
    {
        $files = Lc_file::with(['supplier', 'lc', 'items'])
            ->where('lcf_company_id', $this->com())
            ->where('lcf_branch_id', $this->branch())
            ->orderBy('lcf_id', 'desc')
            ->get();

        return view('lc.files.index', compact('files'));
    }

    public function create()
    {
        return $this->form(new Lc_file([
            'lcf_file_no'   => Lc_file::nextFileNo($this->com()),
            'lcf_file_date' => date('Y-m-d'),
            'lcf_currency'  => 'USD',
        ]));
    }

    public function edit($id)
    {
        $file = $this->findFile($id);
        if ($file->lcf_status != 1) {
            return redirect()->route('lc.files.show', $id)->with('errmsg', 'Only open files (before the LC is opened) can be edited.');
        }

        return $this->form($file);
    }

    private function form(Lc_file $file)
    {
        $suppliers = Inv_supplier::where('inv_sup_com_id', $this->com())
            ->where('inv_sup_status', 1)
            ->orderBy('inv_sup_com_name')
            ->get();
        $currencies = Lc_currency::forCompany($this->com());
        $products = $this->productOptions();

        return view('lc.files.form', compact('file', 'suppliers', 'currencies', 'products'));
    }

    public function store(Request $request)
    {
        return $this->save($request, new Lc_file());
    }

    public function update(Request $request, $id)
    {
        $file = $this->findFile($id);
        if ($file->lcf_status != 1) {
            return redirect()->route('lc.files.show', $id)->with('errmsg', 'Only open files (before the LC is opened) can be edited.');
        }

        return $this->save($request, $file);
    }

    private function save(Request $request, Lc_file $file)
    {
        $request->validate([
            'file_no'      => 'required|string|max:50',
            'file_date'    => 'required|date_format:Y-m-d',
            'supplier_id'  => 'required|integer',
            'pi_no'        => 'nullable|string|max:100',
            'pi_date'      => 'nullable|date_format:Y-m-d',
            'currency'     => 'required|string|max:10',
            'rate'         => 'required|numeric|gt:0',
            'remarks'      => 'nullable|string|max:255',
            'pro_det_id'   => 'required|array|min:1',
            'pro_det_id.*' => 'required|integer',
            'qty'          => 'required|array|size:' . count((array) $request->pro_det_id),
            'qty.*'        => 'required|numeric|gt:0',
            'unit_price'   => 'required|array|size:' . count((array) $request->pro_det_id),
            'unit_price.*' => 'required|numeric|min:0',
        ]);

        if (!Inv_supplier::where('inv_sup_id', $request->supplier_id)->where('inv_sup_com_id', $this->com())->exists()) {
            return redirect()->back()->withInput()->with('errmsg', 'Select a supplier of your company.');
        }
        if (!Lc_currency::forCompany($this->com())->contains('lcc_code', $request->currency)) {
            return redirect()->back()->withInput()->with('errmsg', 'Select a currency from LC Currency Settings.');
        }

        $duplicateNo = Lc_file::where('lcf_company_id', $this->com())
            ->where('lcf_file_no', $request->file_no)
            ->where('lcf_id', '!=', $file->lcf_id ?? 0)
            ->exists();
        if ($duplicateNo) {
            return redirect()->back()->withInput()->with('errmsg', 'File no ' . $request->file_no . ' is already used.');
        }

        $detailIds = array_map('intval', $request->pro_det_id);
        $validDetails = Inv_product_detail::where('inv_pro_det_com_id', $this->com())
            ->where('inv_pro_det_branch_id', $this->branch())
            ->where('inv_pro_det_status', 1)
            ->whereIn('inv_pro_det_id', $detailIds)
            ->get()
            ->keyBy('inv_pro_det_id');

        foreach ($detailIds as $i => $detailId) {
            if (!isset($validDetails[$detailId])) {
                return redirect()->back()->withInput()->with('errmsg', 'Row ' . ($i + 1) . ': select a product.');
            }
            // Warranty products are received with one serial number per unit
            if ($validDetails[$detailId]->inv_pro_det_pro_warranty != 0 && floor($request->qty[$i]) != $request->qty[$i]) {
                return redirect()->back()->withInput()->with('errmsg', 'Row ' . ($i + 1) . ': a warranty product needs a whole-number quantity.');
            }
        }

        $rate = $request->currency === 'BDT' ? 1 : $request->rate;
        $isNew = !$file->exists;

        DB::transaction(function () use ($request, $file, $detailIds, $rate, $isNew) {
            $total = 0;
            foreach ($detailIds as $i => $detailId) {
                $total += round($request->qty[$i] * $request->unit_price[$i], 2);
            }

            $file->fill([
                'lcf_company_id'  => $this->com(),
                'lcf_branch_id'   => $this->branch(),
                'lcf_file_no'     => $request->file_no,
                'lcf_file_date'   => $request->file_date,
                'lcf_supplier_id' => $request->supplier_id,
                'lcf_pi_no'       => $request->pi_no,
                'lcf_pi_date'     => $request->pi_date,
                'lcf_currency'    => $request->currency,
                'lcf_rate'        => $rate,
                'lcf_total_fc'    => $total,
                'lcf_total_bdt'   => round($total * $rate, 2),
                'lcf_remarks'     => $request->remarks,
            ]);
            if ($isNew) {
                $file->lcf_status = 1;
                $file->lcf_submit_by = Auth::user()->au_id;
                $file->lcf_submit_at = Carbon::now();
            } else {
                $file->lcf_updated_by = Auth::user()->au_id;
                $file->lcf_updated_at = Carbon::now();
            }
            $file->save();

            // Items are replaced as a whole while the file is still open
            Lc_file_item::where('lcfi_file_id', $file->lcf_id)->delete();
            foreach ($detailIds as $i => $detailId) {
                Lc_file_item::create([
                    'lcfi_file_id'       => $file->lcf_id,
                    'lcfi_pro_det_id'    => $detailId,
                    'lcfi_qty'           => $request->qty[$i],
                    'lcfi_unit_price_fc' => $request->unit_price[$i],
                    'lcfi_total_fc'      => round($request->qty[$i] * $request->unit_price[$i], 2),
                ]);
            }
        });

        return redirect()->route('lc.files.show', $file->lcf_id)
            ->with('msg', 'File ' . $file->lcf_file_no . ($isNew ? ' opened.' : ' updated.'));
    }

    public function show($id)
    {
        $file = $this->findFile($id);
        $file->load(['items.productDetail.product', 'items.productDetail.type_info', 'lc.bank.bank_info', 'lc.costs', 'lc.shipments.items']);

        return view('lc.files.show', compact('file'));
    }

    public function cancel($id)
    {
        $file = $this->findFile($id);
        if ($file->lcf_status != 1) {
            return redirect()->route('lc.files.show', $id)->with('errmsg', 'Only open files (before the LC is opened) can be cancelled.');
        }

        $file->lcf_status = 0;
        $file->lcf_updated_by = Auth::user()->au_id;
        $file->lcf_updated_at = Carbon::now();
        $file->save();

        return redirect()->route('lc.files.index')->with('msg', 'File ' . $file->lcf_file_no . ' cancelled.');
    }

    private function findFile($id)
    {
        return Lc_file::with(['supplier', 'items'])
            ->where('lcf_id', $id)
            ->where('lcf_company_id', $this->com())
            ->where('lcf_branch_id', $this->branch())
            ->firstOrFail();
    }
}
