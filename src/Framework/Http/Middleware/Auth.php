<?php

namespace Echo\Framework\Http\Middleware;

use Closure;
use Echo\Framework\Http\Response as HttpResponse;
use Echo\Framework\Session\Flash;
use Echo\Interface\Http\{Request, Middleware, Response};

/**
 * Authentication (route)
 */
class Auth implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->getAttribute("route");
        $middleware = $route["middleware"];
        $user = user();

        if (in_array('auth', $middleware) && !$user) {
            Flash::add("warning", "Please sign in to view this page.");
            $res = new HttpResponse("<script>window.location.href = '/sign-in';</script>", 401);
            return $res;
        }

        return $next($request);
    }
}
