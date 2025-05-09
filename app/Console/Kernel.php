<?php

namespace App\Console;

use Echo\Framework\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // see: https://github.com/maximebf/ConsoleKit
    protected array $commands = [
        'version' => \Echo\Framework\Console\Commands\Version::class,
        'migrate' => \Echo\Framework\Console\Commands\Migrate::class,
        'tracks' => \App\Console\Commands\Tracks::class,
        'server' => \Echo\Framework\Console\Commands\Server::class,
    ];
}
