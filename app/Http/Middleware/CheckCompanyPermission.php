<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Au_access_company;
use Auth;
use App\Models\Admin_user;

class CheckCompanyPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$permission_name)
    {
        if(auth()->user()->au_user_type == 1) {
            return $next($request);
        }
      $permission = Au_access_company::where("permission_name", $permission_name)->first();

      if (!empty($permission)) {
          $permission_code = $permission->code;

      } else {
          session()->flash('type', 'danger');
          session()->flash('message', 'Warning! Missing permission. Please contact with admin!');
          return redirect()->route('dashboard');
      }

      $company = Admin_user::where('au_company_id', auth()->user()->au_company_id)->where('au_user_type',4)->first();


      $company_access = $company->au_permission_status;
    //   dd($company);
      $user_accesses = explode('-', $company_access);
      if (in_array($permission_code, $user_accesses)) {
          return $next($request);
      } else {
          session()->flash('type', 'danger');
          session()->flash('message', 'Warning! You have no access to use this route');
          return redirect()->route('dashboard');
      }
    }
}
