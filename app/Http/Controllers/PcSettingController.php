<?php

namespace App\Http\Controllers;

use App\Models\PcSetting;
use App\Models\Admin_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PcSettingController extends Controller
{
    /**
     * Display the PC settings for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $authUser = auth()->user();

        if ($authUser->au_user_type == 4) {
            // Fetch all PC settings for all users for user_type 4
            $pcSettings = PcSetting::all();
        } else {
            // Fetch the pc_setting record for the authenticated user
            $pcSettings = PcSetting::where('user_id', $authUser->au_id)
                ->where('company_id', $authUser->au_company_id)
                ->where('branch_id', $authUser->au_branch_id)
                ->first();
        }

        return view('page.pc-setting', compact('pcSettings'));
    }
    /**
     * Create a new password in pc_setting.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordCreate(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);
    
        $authUser = auth()->user();
    
        // Ensure only one password is created per user
        $existingPcSetting = PcSetting::where('user_id', $authUser->au_id)
            ->where('company_id', $authUser->au_company_id)
            ->where('branch_id', $authUser->au_branch_id)
            ->first();
    
        if ($existingPcSetting) {
            return redirect()->route('pc_setting.index')->with('error', 'You already have a password set.');
        }
    
        PcSetting::create([
            'user_id' => $authUser->au_id,
            'company_id' => $authUser->au_company_id,
               'branch_id' => $authUser->au_branch_id,
            'password' => $validated['password'], // Save the password in plain text
        ]);
    
        return redirect()->route('pc_setting.index')->with('success', 'Password created successfully.');
    }
    

    /**
     * Edit an existing password in pc_setting.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordEdit(Request $request, $id)
    {
        $setting = PcSetting::findOrFail($id);
    
        $validatedData = $request->validate([
            'password' => 'required|string', // Ensure validation is correct
        ]);
    
        // Store the password in plain text (without hashing)
        $setting->password = $validatedData['password'];
        $setting->save();
    
        // Return success message as JSON
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!',
        ]);
    }
    

    
    /**
     * Delete a password in pc_setting.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordDelete($id)
    {
        $authUser = auth()->user();

        $pcSetting = PcSetting::where('user_id', $authUser->au_id)
            ->where('company_id', $authUser->au_company_id)
              ->where('branch_id', $authUser->au_branch_id)
            ->findOrFail($id);

        $pcSetting->delete();

        return redirect()->route('pc_setting.index')->with('success', 'Password deleted successfully.');
    }

    public function verifyPanelPassword(Request $request)
    {
        // Retrieve user by ID (or use any other appropriate identifier)
        $user = Admin_user::where('au_id', auth()->user()->au_id)->first(); 
    
        // Verify if the password matches using md5 hash
        if ($user && md5($request->panelPassword) === $user->au_password) {
            // If password is correct, set 'authenticated' session and redirect
            return redirect()->route('pc_setting.index')->with('panelauthenticated', true);
        } else {
            // If password is incorrect, redirect back with an error
            return back()->with('error', 'Incorrect panel password');
        }
    }
    
    
}
