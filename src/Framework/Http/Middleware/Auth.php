<?php

namespace Echo\Framework\Http\Middleware;

use App\Models\User;
use Closure;
use Echo\Framework\Http\Response as HttpResponse;
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
        $uuid = session()->get("user_uuid");
        $user = $uuid ? User::where("uuid", $uuid) : false;

        if (in_array('auth', $middleware) && !$user) {
            $res = new HttpResponse(null, 302);
            $res->setHeader("Location", uri("auth.sign-in.index"));
            session()->set("auth_redirect", $_SERVER["REQUEST_URI"]);
            return $res;
        }

        return $next($request);
    }
}
