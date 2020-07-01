<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request, plus specify which methods are allowed
     * By default GET,POST,PUT,DELETE,OPTIONS,PATCH are allowed
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next, ...$allowedMethods)
    {
        $response = $next($request);

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => implode(",", $allowedMethods),
            'Access-Control-Allow-Headers' => 'Authorization,Content-Type,Content-Length,X-Content-Filesize,X-Content-Type,X-Content-Filename',
            'Access-Control-Expose-Headers' => 'Content-Disposition,Content-Type,Content-Length,X-Content-Filesize,X-Content-Type,X-Suggested-Filename'
        ];

        if ($response instanceof Response) {
            $response->headers->add($headers);
            // return $response;
        } 

        return $response;
    }
}
