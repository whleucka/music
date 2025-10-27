<?php

return [
    "authenticated_route" => "/playlist",
    "register_enabled" => env("AUTH_REGISTER_ENABLED", false),
    "whitelist" => [
    ],
    "blacklist" => [
    ],
    "max_requests" => 1000,
    "decay_seconds" => 60,
];
