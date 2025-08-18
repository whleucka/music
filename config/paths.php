<?php

$root = __DIR__ . "/../";

return [
    "root" => $root,
    "controllers" => $root . "app/Http/Controllers",
    "templates" => $root . "templates/",
    "migrations" => $root . "migrations",
    "template_cache" => $root . "templates/.cache",
    "jobs" => $root . "jobs",
    "public" => $root . "public",
    "js" => $root . "public/js",
    "logs" => $root . "storage/logs/",
    "session" => "/tmp",
    "uploads" => $root . "storage/uploads",
];
