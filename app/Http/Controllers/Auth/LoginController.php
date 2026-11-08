<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin_user;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }


    public function login(Request $request) {
        //$this->validateLogin($request);
        $request->session()->put('key', 'iglweb123');
        $mobile = $request->au_mobile;
           $password = md5($request->au_password);

         $user = Admin_user::where('au_mobile', $mobile)
                          ->where('au_password', $password)
                          ->where('au_status',1)
                          ->first();
         if(!empty($user)) {


          
          if ($user->au_user_type == 6) {

            $teamL = Admin_user::where('au_user_type',5)
                               ->where('au_team_id',$user->au_team_id)
                               ->where('au_status',1)
                               ->first();
            if(empty($teamL)){
              session()->flash('login', 'Team Suspend!!');
              return redirect()->back()->withInput();
            }
          }

          if ($user->au_user_type == 5 || $user->au_user_type == 6) {

            $com = Admin_user::where('au_user_type',4)
                             ->where('au_company_id',$user->au_company_id)
                             ->where('au_status',1)
                             ->first();
           if(empty($com)){


             session()->flash('login', 'Company Suspend!!');
             return redirect()->back()->withInput();
           }

          }

            Auth::login($user,true);
            if(Auth::check()) {

                session()->flash('login', 'Welcome to Your profile');
                return redirect()->route('dashboard');
            } else {
                session()->flash('login', 'Wrong Credential!');
                return redirect()->back()->withInput();
            }

        } else {
            session()->flash('login', 'Wrong Credential!');
            return redirect()->back()->withInput();
        }
    }
    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }

}
