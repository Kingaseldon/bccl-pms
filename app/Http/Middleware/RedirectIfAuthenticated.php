<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            $redirect = $request->input('redirect');
            $roleId = (int)Auth::user()->RoleId;
            switch($roleId):
                case 2: //USER
                case 5:
                    return redirect($redirect?$redirect:'viewprofile');
                    break;
                case 1: //ADMIN
                    return redirect($redirect?$redirect:'departmentindex');
                    break;
                default:
                    Auth::logout();
                    return redirect($redirect?('/?redirect='.$redirect):'/');
            endswitch;
        }

        return $next($request);
    }
}
