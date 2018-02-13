<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        return $next($request)
              ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Headers', 'mg-platform,mg-version,mg-channel,mg-client-token,mg-sys-version,mg-screen,Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With')
            ->header('Access-Control-Expose-Headers', 'Authorization')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        // Access-Control-Allow-Headers

    }
}
