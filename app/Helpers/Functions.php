<?php

use App\Application;
use App\Models\User;
use App\Http\Kernel as HttpKernel;
use App\Console\Kernel as ConsoleKernel;
use App\State\Brain;
use Echo\Framework\Container\Container;
use Echo\Framework\Database\Connection;
use Echo\Framework\Database\Drivers\MariaDB;
use Echo\Framework\Database\Drivers\MySQL;
use Echo\Framework\Database\QueryBuilder;
use Echo\Framework\Http\Request;
use Echo\Framework\Routing\Router;
use Echo\Framework\Session\Session;
use Echo\Interface\Http\Request as HttpRequest;
use Echo\Interface\Routing\Router as RoutingRouter;

function brain()
{
    return container()->get(Brain::class);
}

function recursiveFiles(string $directory)
{

    return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
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

function user()
{
    $uuid = session()->get("user_uuid");
    return $uuid 
        ? User::where("uuid", $uuid)->get()
        : false;
}

/**
 * Get application container
 */
function container()
{
    return Container::getInstance();
}

function qb()
{
    return new QueryBuilder;
}

function twig()
{
    $twig = container()->get(\Twig\Environment::class);
    return $twig;
}

/**
 * Get PDO DB
 */
function db()
{
    $root_dir = config("paths.root");
    $driver = config("db.driver");
    $driver_class = match($driver) {
        'mysql' => MySQL::class,
        'mariadb' => MariaDB::class,
    };
    $exists = file_exists($root_dir . '.env');
    if ($exists) {
        $db_driver = container()->get($driver_class);
        return Connection::getInstance($db_driver);
    }
    return null;
}

/**
 * Get app session
 */
function session()
{
    return Session::getInstance();
}

/**
 * Get web router
 */
function router(): RoutingRouter
{
    return container()->get(Router::class);
}

/**
 * Get http request
 */
function request(): HttpRequest
{
    return container()->get(Request::class);
}

/**
 * Get env value
 */
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
 * Get route uri
 */
function uri(string $name, ...$params): ?string
{
    return router()->searchUri($name, ...$params);
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
