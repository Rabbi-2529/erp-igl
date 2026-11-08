<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\Au_access;

class UserAccessMiddleware
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
      $permission = Au_access::where("permission_name", $permission_name)->first();
      if (!empty($permission)) {
          $permission_code = $permission->code;
      } else {
          session()->flash('type', 'danger');
          session()->flash('message', 'Warning! Missing permission. Please contact with admin!');
          return redirect()->route('dashboard');
      }

      $user_access = auth()->user()->au_permission_status;
      $user_accesses = explode('-', $user_access);

    if (in_array($permission_code, $user_accesses) || Auth::user()->au_user_type == 4 || Auth::user()->au_user_type == 3) {
    return $next($request);
} else {
    session()->flash('type', 'danger');
    session()->flash('message', 'Warning! You have no access to use this route');
    return redirect()->route('dashboard');
}
    }
}
