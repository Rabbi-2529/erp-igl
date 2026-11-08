<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Client;
use App\Models\Sds_temp;
use App\Models\Admin_user;
use App\Models\Sds_reason_com;
use App\Models\Sds_reason;

class SmsController extends Controller
{
public function sms_setup(Request $request){
    try {
        $user_id = Auth::user()->au_id;
        $com = Auth::user()->au_company_id;
          $branch_id = Auth::user()->au_branch_id;
        $sms_setup = Admin_user::where('au_company_id',$com)
                          ->where('au_branch_id', $branch_id)
                               ->where('au_user_type',4)
                               ->first();
        $api_balance = $sms_setup->au_api_key;
        $client = new \GuzzleHttp\Client();
        $balance = "http://sms.iglweb.com/api/v2/balance?api_key=". $api_balance;

        $response = $client->request('GET', "$balance");

        $json_response = $response->getBody()->getContents();
        $dec = json_decode($json_response);
        $available_bal = @$dec->balance;
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Log error message for debugging
        \Log::error('API Request Error: ' . $e->getMessage());
        // Optionally, set a default value for available balance
        $available_bal = 'Error';
    }

    return view('pages.sms_setup',compact('sms_setup','available_bal'));
}
    public function sms_setup_submit(Request $request){
      $user_id = Auth::user()->au_id;
      $com = Auth::user()->au_company_id;
      $branch_id = Auth::user()->au_branch_id;
      $sms_setup = Admin_user::where('au_company_id',$com)
       ->where('au_branch_id', $branch_id)
                             ->where('au_user_type',4)
                             ->first();
      $message =
      [
          'au_api_key.required' => 'API key is Required',
          'au_sender_id.required' => 'Sender ID is Required',
      ];
      $request->validate([
        'au_api_key' => 'required',
        'au_sender_id' => 'required',
      ],$message);
      $sms_setup->au_api_key = $request->input('au_api_key');
      $sms_setup->au_sender_id = $request->input('au_sender_id');
      $sms_setup->save();
      return redirect('/sms_setup')->with(['msg_sms_setup' => 'API and Sender ID Added']);
    }
    public function sms_campaign(){
      $com = Auth::user()->au_company_id;
        $branch_id = Auth::user()->au_branch_id;
      $sender_id = Admin_user::where('au_company_id',$com)
       ->where('au_branch_id', $branch_id)

                             ->where('au_user_type',4)
                             ->get();
      $tl_name = Admin_user::where('au_user_type',5)
                            ->where('au_company_id',$com)
                             ->where('au_branch_id', $branch_id)
                            ->get();

      $sms_reason = Sds_reason_com::where('src_company_id',$com)->get();
    	return view('pages.sms_campaign',compact('sender_id','tl_name','sms_reason'));
    }
    public function sms_campaign_send(Request $request){
      //
    }
public function sms_temp_edit(){
    $com = Auth::user()->au_company_id;

    $templates = Sds_temp::where('st_com_id', $com)->get();

    return view('pages.sms_temp_edit', compact('templates'));
}

public function sms_temp_modify($id){
    $template = Sds_temp::find($id);
    return view('pages.sms_temp_modify', compact('template'));
}

public function sms_temp_modify_edit(Request $request, $id){
    $temp_edit = Sds_temp::where('st_sl_id', $id)->first();

    $message = [
        'st_title.required' => 'Title is Required',
        'st_message.required' => 'Message is Required',
    ];

    $request->validate([
        'st_title' => 'required',
        'st_message' => 'required',
    ], $message);

    $temp_edit->st_title = $request->input('st_title');
    $temp_edit->st_message = $request->input('st_message');
    $temp_edit->save();

    return redirect('/sms_temp_edit');
}

public function create_template(Request $request){
    $sms_com = Auth::user()->au_company_id;

    $text = $request->input('st_message');
    $sms_count = SmsController::get_sms_quantity($text);

    $table = new Sds_temp;
    $table->st_com_id = $sms_com;
    $table->st_title = $request->input('st_title');
    $table->st_message = $request->input('st_message');
    $table->st_quantity = $sms_count;
    $table->save();

    return redirect('/sms_temp_edit');
}

    public function temp_del($id){
        $template = Sds_temp::find($id);
        $template->delete();
        return redirect('/sms_temp_edit');
    }

    public static function is_unicode($string)
    {
        if (strlen($string) != strlen(utf8_decode($string))) {
            return true;
        } else {
            return false;
        }
    }

/*unicode sms count*/
    public static function unicode_sms_count($string)
    {
        $strLength = mb_strlen(urldecode($string));

        /*get sms count*/
        if ($strLength <= 70) {
            $smsCount = 1;
        } else {
            $smsCount = ceil($strLength / 67);
        }
        return $smsCount;
    }

/*text sms count*/
    public static function text_sms_count($string)
    {
        $strLength = mb_strlen(urldecode($string));
        /*get sms count*/
        if ($strLength <= 160) {
            $smsCount = 1;
        } else {
            $smsCount = ceil($strLength / 153);
        }
        return $smsCount;
    }
    public static function get_sms_quantity($text)
    {
      $aux = 0;
       if ( SmsController::is_unicode($text) ){
         return SmsController::unicode_sms_count( $text );
       }else{
         return SmsController::text_sms_count($text);
       }

    }
}
