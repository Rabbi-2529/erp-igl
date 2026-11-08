<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\Admin_user;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
{
    return $this->showForgetForm(); // Redirect to your custom mobile-based form
}
   public function showForgetForm()
    {
        return view('auth.forgot_password');
    }

public function sendReset(Request $request)
{
    $request->validate([
        'au_mobile' => 'required|digits:11',
    ]);

    // Find user by mobile number
    $user = Admin_user::where('au_mobile', $request->au_mobile)->first();

    if (!$user) {
        return back()->withErrors(['au_mobile' => 'No account found with this mobile number.']);
    }

    // Generate random 8-character raw password
    $rawPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

    // MD5 hash the password
    $hashedPassword = md5($rawPassword);

    // Save hashed password to user
    $user->au_password = $hashedPassword;
    $user->save();

    // Get company admin (user_type 4) for API key and sender ID
    $companyAdmin = Admin_user::where('au_company_id', $user->au_company_id)
                    ->where('au_user_type', 4)
                    ->first();

    $apiKey = $companyAdmin->au_api_key ?? '';
    $senderId = $companyAdmin->au_sender_id ?? '';
    $mobile = $user->au_mobile;

    $messageContent = "Your new password is: $rawPassword. Please login at https://erp.iglweb.com";

    $message = urlencode($messageContent);

    $url = "http://sms.iglweb.com/api/v1/send?api_key=$apiKey&contacts=$mobile&senderid=$senderId&msg=$message";

    // Log info for debugging
    \Log::info('Sending password SMS', [
        'api_key' => $apiKey,
        'sender_id' => $senderId,
        'password' => $rawPassword,
        'mobile' => $mobile,
        'url' => $url
    ]);

    try {
        $response = file_get_contents($url);
        \Log::info('SMS API Response: ' . $response);

        return back()->with('status', 'Your new password has been sent to your mobile.');
    } catch (\Exception $e) {
        \Log::error('SMS sending failed: ' . $e->getMessage());
        return back()->withErrors(['au_mobile' => 'Failed to send SMS. Please try again later.']);
    }
}

}
