<?php

namespace App\Http\Controllers\Lc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inv_product_detail;
use App\Models\Inv_product_inventory;
use App\Models\Inv_product_inventory_detail;
use App\Models\Lc_file_item;
use App\Models\Lc_shipment;
use App\Models\Lc_shipment_item;
use Carbon\Carbon;
use Auth;
use DB;

/**
 * Shipments under an LC and receiving them into stock (same records as a normal purchase).
 */
class LcShipmentController extends Controller
{
    use LcHelpers;

    public function index()
    {
        $shipments = Lc_shipment::with(['lc.file.supplier', 'items'])
            ->where('lcs_company_id', $this->com())
            ->where('lcs_branch_id', $this->branch())
            ->orderBy('lcs_id', 'desc')
            ->get();

        return view('lc.shipments.index', compact('shipments'));
    }

    public function create(Request $request)
    {
        $lcs = $this->lcList();
        $lc = $request->lc ? $this->findLc($request->lc) : null;

        return view('lc.shipments.create', compact('lcs', 'lc'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lc_id'        => 'required|integer',
            'bl_no'        => 'nullable|string|max:100',
            'vessel'       => 'nullable|string|max:100',
            'invoice_no'   => 'nullable|string|max:100',
            'ship_date'    => 'nullable|date_format:Y-m-d',
            'eta'          => 'nullable|date_format:Y-m-d',
            'arrival_date' => 'nullable|date_format:Y-m-d',
            'remarks'      => 'nullable|string|max:255',
            'qty'          => 'required|array',
            'qty.*'        => 'nullable|numeric|min:0',
        ]);
        $lc = $this->findLc($request->lc_id);
        $items = $lc->file->items->keyBy('lcfi_id');

        $lines = [];
        foreach ((array) $request->qty as $itemId => $qty) {
            if ($qty === null || $qty == 0) {
                continue;
            }
            $item = $items[(int) $itemId] ?? null;
            if (!$item) {
                return redirect()->back()->withInput()->with('errmsg', 'A product does not belong to this LC.');
            }
            if ($qty > $item->pending_ship_qty) {
                return redirect()->back()->withInput()->with('errmsg', $item->product_name . ': only ' . ($item->pending_ship_qty + 0) . ' left to ship.');
            }
            if ($item->is_warranty && floor($qty) != $qty) {
                return redirect()->back()->withInput()->with('errmsg', $item->product_name . ': a warranty product needs a whole-number quantity.');
            }
            $lines[] = [$item, $qty];
        }
        if (empty($lines)) {
            return redirect()->back()->withInput()->with('errmsg', 'Enter the quantity of at least one product in this shipment.');
        }

        $shipment = DB::transaction(function () use ($request, $lc, $lines) {
            $shipment = Lc_shipment::create([
                'lcs_company_id'   => $this->com(),
                'lcs_branch_id'    => $this->branch(),
                'lcs_lc_id'        => $lc->lc_id,
                'lcs_no'           => Lc_shipment::where('lcs_lc_id', $lc->lc_id)->count() + 1,
                'lcs_bl_no'        => $request->bl_no,
                'lcs_vessel'       => $request->vessel,
                'lcs_invoice_no'   => $request->invoice_no,
                'lcs_ship_date'    => $request->ship_date,
                'lcs_eta'          => $request->eta,
                'lcs_arrival_date' => $request->arrival_date,
                'lcs_remarks'      => $request->remarks,
                'lcs_status'       => 1,
                'lcs_submit_by'    => Auth::user()->au_id,
                'lcs_submit_at'    => Carbon::now(),
            ]);

            foreach ($lines as [$item, $qty]) {
                Lc_shipment_item::create([
                    'lcsi_shipment_id'  => $shipment->lcs_id,
                    'lcsi_file_item_id' => $item->lcfi_id,
                    'lcsi_qty'          => $qty,
                ]);
                $item->lcfi_shipped_qty += $qty;
                $item->save();
            }

            return $shipment;
        });

        return redirect()->route('lc.shipments.show', $shipment->lcs_id)->with('msg', 'Shipment ' . $shipment->lcs_no . ' added to LC ' . $lc->lc_no . '.');
    }

    public function show($id)
    {
        $shipment = $this->findShipment($id);
        $lc = $this->findLc($shipment->lcs_lc_id);
        $lc->load('costs');
        $shipment->load(['items.fileItem.productDetail.product', 'items.fileItem.productDetail.type_info']);

        return view('lc.shipments.show', compact('shipment', 'lc'));
    }

    /**
     * Receives a shipment into stock, the same way the purchase page does: an inventory purchase row per
     * product (supplier ledger, at the LC goods price), serial numbers for warranty products and the stock quantity.
     */
    public function receive(Request $request, $id)
    {
        $shipment = $this->findShipment($id);
        if ($shipment->lcs_status != 1) {
            return redirect()->route('lc.shipments.show', $id)->with('errmsg', 'This shipment is already received.');
        }
        $request->validate([
            'receive_date'     => 'required|date_format:Y-m-d',
            'update_buy_price' => 'nullable|in:1',
            'serials'          => 'nullable|array',
        ]);

        $lc = $this->findLc($shipment->lcs_lc_id);
        $lc->load('costs');
        $shipment->load('items.fileItem');
        $landedFactor = $lc->landedFactor();
        $invoiceNo = mb_substr('LC-' . $lc->lc_no . '-S' . $shipment->lcs_no, 0, 100);

        $exists = Inv_product_inventory::where('inv_pro_inv_com_id', $this->com())
            ->where('inv_pro_inv_branch_id', $this->branch())
            ->where('inv_pro_inv_invoice_no', $invoiceNo)
            ->exists();
        if ($exists) {
            return redirect()->back()->with('errmsg', 'Purchase invoice ' . $invoiceNo . ' already exists.');
        }

        // Check every line (and its serial numbers) before saving anything
        $lines = [];
        foreach ($shipment->items as $shipItem) {
            $fileItem = $shipItem->fileItem;
            $detail = Inv_product_detail::where('inv_pro_det_id', $fileItem->lcfi_pro_det_id)
                ->where('inv_pro_det_com_id', $this->com())
                ->where('inv_pro_det_branch_id', $this->branch())
                ->where('inv_pro_det_status', 1)
                ->first();
            if (!$detail) {
                return redirect()->back()->withInput()->with('errmsg', $fileItem->product_name . ' is no longer an active product of this branch.');
            }

            $qty = (float) $shipItem->lcsi_qty;
            $serials = [];
            if ($detail->inv_pro_det_pro_warranty != 0) {
                $serials = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) ($request->serials[$shipItem->lcsi_id] ?? '')))));
                if (count($serials) != $qty) {
                    return redirect()->back()->withInput()->with('errmsg', $fileItem->product_name . ': enter ' . (int) $qty . ' serial numbers (' . count($serials) . ' entered).');
                }
                if (count(array_unique($serials)) != count($serials)) {
                    return redirect()->back()->withInput()->with('errmsg', $fileItem->product_name . ': the same serial number is entered twice.');
                }
                $taken = Inv_product_inventory_detail::where('inv_pro_invdet_com_id', $this->com())
                    ->where('inv_pro_invdet_pro_id', $detail->inv_pro_det_id)
                    ->whereIn('inv_pro_invdet_slno', $serials)
                    ->pluck('inv_pro_invdet_slno');
                if ($taken->isNotEmpty()) {
                    return redirect()->back()->withInput()->with('errmsg', $fileItem->product_name . ': serial already in stock records: ' . $taken->implode(', '));
                }
            }

            $goodsUnit = round($fileItem->lcfi_unit_price_fc * $lc->lc_rate, 2);
            $lines[] = (object) [
                'shipItem'   => $shipItem,
                'fileItem'   => $fileItem,
                'detail'     => $detail,
                'qty'        => $qty,
                'serials'    => $serials,
                'goodsUnit'  => $goodsUnit,
                'landedUnit' => round($goodsUnit * $landedFactor, 2),
            ];
        }

        DB::transaction(function () use ($request, $shipment, $lc, $lines, $invoiceNo) {
            $userId = Auth::user()->au_id;
            $supplierId = $lc->file->lcf_supplier_id;

            foreach ($lines as $line) {
                $inventory = new Inv_product_inventory();
                $inventory->inv_pro_inv_com_id = $this->com();
                $inventory->inv_pro_inv_branch_id = $this->branch();
                $inventory->inv_pro_inv_project_id = $line->detail->inv_pro_det_id; // detail id
                $inventory->inv_pro_inv_party_id = $supplierId;
                $inventory->inv_pro_inv_invoice_no = $invoiceNo;
                $inventory->inv_pro_inv_total_qty = $line->qty;
                $inventory->inv_pro_inv_short_qty = 0;
                $inventory->inv_pro_inv_qty = $line->qty;
                $inventory->inv_pro_inv_unit_price = $line->goodsUnit;
                $inventory->inv_pro_inv_debit = round($line->qty * $line->goodsUnit, 2);
                $inventory->inv_pro_inv_credit = 0;
                $inventory->inv_pro_inv_issue_date = $request->receive_date;
                $inventory->inv_pro_inv_tran_desc = 'LC Purchase (' . $lc->lc_no . ')';
                $inventory->inv_pro_inv_deal_type = 1;
                $inventory->inv_pro_inv_tran_type = 1;
                $inventory->inv_pro_inv_status = 1;
                $inventory->inv_pro_inv_submit_by = $userId;
                $inventory->inv_pro_inv_submit_at = Carbon::now();
                $inventory->save();

                foreach ($line->serials as $serial) {
                    $serialRow = new Inv_product_inventory_detail();
                    $serialRow->inv_pro_invdet_com_id = $this->com();
                    $serialRow->inv_pro_invdet_branch_id = $this->branch();
                    $serialRow->inv_pro_invdet_proinv_id = $inventory->inv_pro_inv_id;
                    $serialRow->inv_pro_invdet_pro_id = $line->detail->inv_pro_det_id;
                    $serialRow->inv_pro_invdet_buy_id = $supplierId;
                    $serialRow->inv_pro_invdet_slno = $serial;
                    $serialRow->inv_pro_invdet_sell_status = 0;
                    $serialRow->inv_pro_invdet_status = 1;
                    $serialRow->inv_pro_invdet_submit_by = $userId;
                    $serialRow->inv_pro_invdet_submit_at = Carbon::now();
                    $serialRow->save();
                }

                $line->detail->inv_pro_det_available_qty += $line->qty;
                if ($request->update_buy_price) {
                    $line->detail->inv_pro_det_buy_price = $line->landedUnit;
                }
                $line->detail->save();

                $line->fileItem->lcfi_received_qty += $line->qty;
                $line->fileItem->save();
            }

            $shipment->lcs_status = 2;
            $shipment->lcs_purchase_invoice_no = $invoiceNo;
            $shipment->lcs_received_by = $userId;
            $shipment->lcs_received_at = Carbon::now();
            $shipment->save();
        });

        return redirect()->route('lc.shipments.show', $shipment->lcs_id)
            ->with('msg', 'Shipment received into stock as purchase invoice ' . $invoiceNo . '.');
    }

    /** Deletes a shipment that is not received yet (entered by mistake) */
    public function delete($id)
    {
        $shipment = $this->findShipment($id);
        if ($shipment->lcs_status != 1) {
            return redirect()->route('lc.shipments.show', $id)->with('errmsg', 'A received shipment can not be deleted.');
        }

        DB::transaction(function () use ($shipment) {
            foreach ($shipment->items as $shipItem) {
                Lc_file_item::where('lcfi_id', $shipItem->lcsi_file_item_id)->decrement('lcfi_shipped_qty', $shipItem->lcsi_qty);
                $shipItem->delete();
            }
            $shipment->delete();
        });

        return redirect()->route('lc.shipments.index')->with('msg', 'Shipment deleted.');
    }

    private function findShipment($id)
    {
        return Lc_shipment::with('items')
            ->where('lcs_id', $id)
            ->where('lcs_company_id', $this->com())
            ->where('lcs_branch_id', $this->branch())
            ->firstOrFail();
    }
}
