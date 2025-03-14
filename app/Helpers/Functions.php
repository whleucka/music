<?php

use App\Application;
use App\Http\Kernel as HttpKernel;
use App\Console\Kernel as ConsoleKernel;
use Echo\Framework\Container\Container;
use Echo\Framework\Database\Connection;
use Echo\Framework\Database\Drivers\MySQL;
use Echo\Framework\Http\Request;
use Echo\Framework\Routing\Router;
use Echo\Framework\Session\Session;
use Echo\Interface\Http\Request as HttpRequest;
use Echo\Interface\Routing\Router as RoutingRouter;

function redirect(string $path): void {
    if (request()->headers->has('Hx-Request')) {
        $header = sprintf("HX-Redirect:%s", $path);
        header($header);
        exit();
    } else {
        $header = sprintf("Location:%s", $path);
        header($header);
        exit();
    }
}

function location(
    string $path, 
    ?string $source = null, 
    ?string $event = null, 
    ?string $handler = null, 
    ?string $target = null, 
    ?string $swap = null,
    ?string $values = null,
    ?string $headers = null,
    ?string $select = null
) {
    if (request()->headers->has('Hx-Request')) {
        $options = [];
        $options['path'] = $path;
        foreach (compact('source', 'event', 'handler', 'target', 'swap', 'values', 'headers', 'select') as $key => $value) {
            if ($value !== null) {
                $options[$key] = $value;
            }
        }
        $opts = json_encode($options);
        header("HX-Location: $opts");
        exit();
    } else {
        $header = sprintf("Location:%s", $path);
        header($header);
        exit();
    }
}

function trigger(string $event)
{
    header("HX-Trigger: $event");
}

function uri(string $name, ...$params): ?string
{
    return router()->searchUri($name, ...$params);
}

function recursiveFiles(string $directory)
{

    return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
}

function getClasses(string $directory): array
{
    // Get existing classes before loading new ones
    $before = get_declared_classes();

    // Recursively find all PHP files
    $files = recursiveFiles($directory);
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            require_once $file->getPathname();
        }
    }

    // Get all declared classes after loading
    $after = get_declared_classes();

    // Return only the new classes
    return array_diff($after, $before);
}

function router(): RoutingRouter
{
    return container()->get(Router::class);
}

function request(): HttpRequest
{
    return container()->get(Request::class);
}

/**
 * Get application container
 */
function container()
{
    return Container::getInstance();
}

/**
 * Get PDO DB
 */
function db()
{
    $mysql = container()->get(MySQL::class);
    return Connection::getInstance($mysql);
}

/**
 * Get app session
 */
function session()
{
    return Session::getInstance();
}

function env(string $name, mixed $default = null)
{
    // Load environment
    $dotenv = Dotenv\Dotenv::createImmutable(config("paths.root"));
    $dotenv->safeLoad();

    if (!isset($_ENV[$name])) {
        $_ENV[$name] = $default;
    }
    return $_ENV[$name];
}

/**
 * Dump
 */
function dump(mixed $payload): void
{
    printf("<pre>%s</pre>", print_r($payload, true));
}

/**
 * Dump & die
 */
function dd(mixed $payload): void
{
    dump($payload);
    die;
}

/**
 * Web application
 */
function app(): Application
{
    $kernel = new HttpKernel();
    return new Application($kernel);
}

/**
 * Console application
 */
function console(): Application
{
    $kernel = new ConsoleKernel();
    return new Application($kernel);
}

/**
 * Get application config
 */
function config(string $name): mixed
{
    $name_split = explode(".", $name);
    $config_target = __DIR__ . "/../../config/" . strtolower($name_split[0]) . ".php";

    if (is_file($config_target)) {
        $config = require $config_target;

        // Traverse nested keys dynamically
        $value = $config;
        for ($i = 1; $i < count($name_split); $i++) {
            if (!isset($value[$name_split[$i]])) {
                return null;
            }
            $value = $value[$name_split[$i]];
        }
        if ($value === "true") {
            return true;
        }
        if ($value === "false") {
            return false;
        }
        return $value;
    }

    return null;
}
