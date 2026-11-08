<?php

namespace App\Http\Controllers\Lc;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Lc_document;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Auth;

/**
 * LC Documents: PI, LC copy, B/L, invoices ... kept outside the public folder
 * and downloaded through the app, so only this company can open them.
 */
class LcDocumentController extends Controller
{
    use LcHelpers;

    public function index(Request $request)
    {
        $lcs = $this->lcList();
        $lc = $request->lc ? $this->findLc($request->lc) : null;
        $documents = $lc
            ? Lc_document::where('lcd_lc_id', $lc->lc_id)->where('lcd_status', 1)->orderBy('lcd_id', 'desc')->get()
            : collect();
        $types = Lc_document::TYPES;

        return view('lc.documents', compact('lcs', 'lc', 'documents', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lc_id' => 'required|integer',
            'type'  => 'required|in:' . implode(',', Lc_document::TYPES),
            'title' => 'nullable|string|max:150',
            'file'  => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);
        $lc = $this->findLc($request->lc_id);

        $upload = $request->file('file');
        $path = $upload->store('lc_documents/' . $this->com() . '/' . $lc->lc_id);

        Lc_document::create([
            'lcd_company_id'    => $this->com(),
            'lcd_branch_id'     => $this->branch(),
            'lcd_lc_id'         => $lc->lc_id,
            'lcd_type'          => $request->type,
            'lcd_title'         => $request->title,
            'lcd_file'          => $path,
            'lcd_original_name' => mb_substr($upload->getClientOriginalName(), 0, 255),
            'lcd_status'        => 1,
            'lcd_submit_by'     => Auth::user()->au_id,
            'lcd_submit_at'     => Carbon::now(),
        ]);

        return redirect()->route('lc.documents', ['lc' => $lc->lc_id])->with('msg', $request->type . ' uploaded.');
    }

    public function download($id)
    {
        $document = $this->findDocument($id);
        if (!Storage::exists($document->lcd_file)) {
            return redirect()->back()->with('errmsg', 'The file is missing on the server.');
        }

        return Storage::download($document->lcd_file, $document->lcd_original_name ?: basename($document->lcd_file));
    }

    public function delete($id)
    {
        $document = $this->findDocument($id);
        Storage::delete($document->lcd_file);
        $document->lcd_status = 0;
        $document->save();

        return redirect()->route('lc.documents', ['lc' => $document->lcd_lc_id])->with('msg', $document->lcd_type . ' deleted.');
    }

    private function findDocument($id)
    {
        return Lc_document::where('lcd_id', $id)
            ->where('lcd_company_id', $this->com())
            ->where('lcd_branch_id', $this->branch())
            ->where('lcd_status', 1)
            ->firstOrFail();
    }
}
