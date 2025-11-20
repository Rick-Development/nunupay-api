<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRememberToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->remember_token === null) {
            Auth::logout();
            return redirect(route('login'))->with('warning', 'Logged out from all devices.');
        }

        return $next($request);
    }


}
