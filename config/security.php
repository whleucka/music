<?php

return [
    "authenticated_route" => "/admin/dashboard",
    "register_enabled" => env("AUTH_REGISTER_ENABLED", false),
    "whitelist" => [
    ],
    "blacklist" => [
    ],
    "max_requests" => 200,
    "decay_seconds" => 60,
];
