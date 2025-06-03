<?php

namespace Echo\Framework\Http\Middleware;

use App\Models\User;
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
        $uuid = session()->get("user_uuid");
        $user = $uuid ? User::where("uuid", $uuid) : false;

        if (in_array('auth', $middleware) && !$user) {
            $res = new HttpResponse('<script>window.location.href="/sign-in";</script>', 200);
            $res->setHeader("HX-Retarget", "body");
            if (preg_match("/index/", $route->name)) {
                session()->set("auth_redirect", $_SERVER["REQUEST_URI"]);
            }
            Flash::add("warning", "Please sign in to view this page.");
            return $res;
        }

        return $next($request);
    }
}
