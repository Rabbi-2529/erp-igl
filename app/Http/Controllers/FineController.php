<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Ensure this line is present
use Auth;

class FineController extends Controller
{
    /**
     * Display a listing of fines.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fines = Fine::all(); // Fetch all fines from the database
        
        // Debugging with dd() to check $fines data
     
        
        // Return the view with the fetched fines
        return view('inventory.accounts.fines.index', compact('fines'));
    }
    /**
     * Show the form for creating a new fine.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Return view for creating a new fine
        return view('inventory.accounts.fines.create');
    }

    /**
     * Store a newly created fine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Create a new fine record
        $fine = Fine::create($request->only('title'));

        // Redirect to the index page with success message
        return redirect()->route('fines.index')->with('success', 'Fine created successfully.');
    }
    /**
     * Show the form for editing the specified fine.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $fine = Fine::findOrFail($id); // Find the fine by its ID
        // You can return the fine data as JSON if needed for AJAX requests
        return response()->json($fine);
    }

    /**
     * Update the specified fine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            // Add validation rules for other fields as needed
        ]);

        // Find the fine by its ID and update its attributes
        $fine = Fine::findOrFail($id);
        $fine->title = $request->input('title');
        // Update other fields as needed
        $fine->save();

        return redirect()->route('fines.index')->with('success', 'Fine updated successfully.');
    }

    /**
     * Remove the specified fine from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the fine by its ID and delete it
        $fine = Fine::findOrFail($id);
        $fine->delete();

        return response()->json(['message' => 'Fine deleted successfully.']);
    }
}