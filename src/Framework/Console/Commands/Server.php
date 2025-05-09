<?php

namespace Echo\Framework\Console\Commands;

/**
 * Run local web server
 */
class Server extends \ConsoleKit\Command
{
    public function execute(array $args, array $options = []): void
    {
        $dev = config("dev");
        $this->writeln("Starting development server on {$dev['server']}:{$dev['port']}", \ConsoleKit\Colors::GREEN);
        `php -S {$dev['server']}:{$dev['port']} -t public/`;
    }
}
