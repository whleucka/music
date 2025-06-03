<?php

namespace Echo\Framework\Http\Middleware;

use App\Providers\Setup\SetupService;
use Closure;
use Echo\Framework\Http\Response as HttpResponse;
use Echo\Framework\Session\Flash;
use Echo\Interface\Http\{Request, Middleware, Response};

/**
 * Setup
 */
class Setup implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->getAttribute("route");
        $middleware = $route["middleware"];
        $setup_service = container()->get(SetupService::class);

        if (!in_array("setup", $middleware) && !$setup_service->isComplete()) {
            // Setup must be completed first
            Flash::add("warning", "Please complete the setup in order to continue.");
            $res = new HttpResponse(null, 302);
            $res->setHeader("Location", uri("setup.index"));
            return $res;
        } else if (in_array("setup", $middleware) && $setup_service->isComplete()) {
            // Setup is complete, show sign-in
            Flash::add("warning", "Setup complete. For security purposes, the setup page has been disabled.");
            $res = new HttpResponse(null, 302);
            $res->setHeader("Location", uri("auth.sign-in.index"));
            return $res;
        }

        return $next($request);
    }
}
