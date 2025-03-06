<?php

$root = __DIR__ . "/../";

return [
    "root" => $root,
    "controllers" => $root . "app/Http/Controllers",
    "templates" => $root . "templates/",
    "migrations" => $root . "migrations",
    "template_cache" => $root . "templates/.cache",
    "jobs" => $root . "jobs",
    "logs" => $root . "storage/logs/",
    "covers" => $root . "storage/tracks/covers/",
    "transcode" => $root . "storage/tracks/transcode/",
    "public_covers" => '/covers/',
    "public_transcode" => '/transcode/',
];
