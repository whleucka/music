<?php

namespace App;

use Echo\Framework\Http\Request;
use Echo\Interface\Console\Kernel as ConsoleKernel;
use Echo\Interface\Application as EchoApplication;
use Echo\Interface\Http\Kernel as HttpKernel;
use Dotenv;

class Application implements EchoApplication
{
    public function __construct(private ConsoleKernel|HttpKernel $kernel)
    {
        $dotenv = Dotenv\Dotenv::createImmutable(config("paths.root"));
        $dotenv->safeLoad();
    }

    public function run(): void
    {
        // Run the application (web or cli)
        if ($this->kernel instanceof HttpKernel) {
            // Handle a web request
            $request = container()->get(Request::class);
            $this->kernel->handle($request);
        } elseif ($this->kernel instanceof ConsoleKernel) {
            // Run a command in cli mode
            $this->kernel->handle();
        }
    }
}
