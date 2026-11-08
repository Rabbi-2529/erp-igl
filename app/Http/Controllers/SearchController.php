<?php

namespace App\Http\Controllers;

use App\Models\Sds_query_book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $exist = Sds_query_book::all();
        $q = $request->input('q');

        $validator = Validator::make($request->all(), [
            'q' => ['required', 'regex:/^[0-9]{11}$/']
        ], [
            'q.required' => 'Please enter a mobile number',
            'q.regex' => 'Please enter a valid 11-digit mobile number'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        // Check if the mobile number already exists in the table
        foreach ($exist as $row) {
            if ($q == $row->qb_mobile || $q == $row->qb_mobile1 || $q == $row->qb_mobile2) {
                return response()->json([
                    'status' => 'success',
                    'url' => route('pages.client_feedback', ['id' => $row->qb_id])
                ]);
            }
        }


        // If the loop completes without returning, no matching mobile number was found
        return response()->json([
            'status' => 'error',
            'message' => 'No matching records found',
            'url' => "create_customer"
        ]);
    }
}
