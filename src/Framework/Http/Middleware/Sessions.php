<?php

namespace Echo\Framework\Http\Middleware;

use Closure;
use Echo\Interface\Http\{Request, Middleware, Response};

/**
 * Sessions
 */
class Sessions implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            db()->execute("INSERT INTO sessions (uri, ip) 
                VALUES (?,?)", [
                $request->getUri(),
                ip2long($request->getClientIp())
            ]);
        } catch (\Exception|\Error|\PDOException $e) {
            error_log("-- Skipping session insert --");
        }

        return $next($request);
    }
}
