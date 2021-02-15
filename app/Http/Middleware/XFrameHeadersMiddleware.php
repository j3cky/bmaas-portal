<?php

namespace App\Http\Middleware;

use Closure;

class XFrameHeadersMiddleware
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
	    //return $next($request);
        $response = $next($request);
	//$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
	$response->headers->set('X-Frame-Options', 'ALLOW FROM https://bmaas.arch.biznetgio.xyz:4443/irc.html?gui=true&lang=en', false);
        return $response;	    
    }
}
