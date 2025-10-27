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
            $user = user();
            db()->execute("INSERT INTO sessions (user_id, uri, ip) 
                VALUES (?,?,?)", [
                $user ? $user->id : null,
                $request->getUri(),
                ip2long($request->getClientIp())
            ]);
        } catch (\Exception|\Error|\PDOException $e) {
            error_log("-- Skipping session insert --");
        }

        return $next($request);
    }
}
