<?php

use Echo\Framework\Database\Drivers\{ MariaDB, MySQL };
use Echo\Framework\Http\Request;
use Echo\Framework\Routing\Collector;
use Echo\Framework\Routing\Router;
use Echo\Interface\Admin\Module;

/**
 * Helpers
 */
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

return [
    Module::class => function(\Psr\Container\ContainerInterface $c) {
        $params = request()->getAttribute('route')['params'];
        if (empty($params)) throw new Error("Param does not exist");
        try {
            $class = $params[0];
            return $c->get("App\Http\Controllers\Admin\Modules\\$class");
        } catch (Throwable $ex) {}
    },
    Request::class => DI\create()->constructor($_GET, $_POST, $_REQUEST, $_FILES, $_COOKIE, function_exists("getallheaders") ? getallheaders() : []),
    Collector::class => function() {
        // Get web controllers
        $controller_path = config("paths.controllers");
        $controllers = getClasses($controller_path);

        // Register application routes
        $collector = new Collector();
        foreach ($controllers as $controller) {
            $collector->register($controller);
        }
        return $collector;
    },
    Router::class => DI\create()->constructor(DI\Get(Collector::class)),
    MySQL::class => DI\create()->constructor(
        name: config("db.name"),
        username: config("db.username"),
        password: config("db.password"),
        host: config("db.host"),
        port: (int) config("db.port"),
        charset: config("db.charset"),
        options: config("db.options"),
    ),
    MariaDB::class => DI\create()->constructor(
        name: config("db.name"),
        username: config("db.username"),
        password: config("db.password"),
        host: config("db.host"),
        port: (int) config("db.port"),
        charset: config("db.charset"),
        options: config("db.options"),
    ),
    \Twig\Loader\FilesystemLoader::class => DI\create()->constructor(config("paths.templates")),
    \Twig\Environment::class => DI\create()->constructor(DI\Get(\Twig\Loader\FilesystemLoader::class), [
        "cache" => config("paths.template_cache"),
        "auto_reload" => config("app.debug"),
        "debug" => config("app.debug"),
    ]),
];
