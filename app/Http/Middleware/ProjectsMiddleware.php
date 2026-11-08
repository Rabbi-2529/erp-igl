<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ProjectsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $permission = Auth::user()->au_permission_status;
        $per = explode('-',$permission);
        if (in_array(7,$per)) {
            return $next($request);
        }else {
            session()->flash('type', 'danger');
            session()->flash('message', 'Warning! Missing permission. Please contact with admin!');
            return redirect()->route('dashboard');
        }
    }
}
