<?php

namespace Echo\Framework\Routing;

use Echo\Framework\Routing\Route;
use ReflectionClass;

class Collector
{
    private array $routes = [];

    public function register(string $controller): void
    {
        $reflection = new \ReflectionClass($controller);

        // Check for group attribute
        $groupPrefix = '';
        $groupMiddleware = [];

        foreach ($reflection->getAttributes(Group::class) as $groupAttr) {
            $group = $groupAttr->newInstance();
            $groupPrefix = rtrim($group->prefix, '/');
            $groupMiddleware = $group->middleware;
        }

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                if (!is_subclass_of($instance, Route::class)) {
                    continue;
                }

                $http_method = strtolower((new \ReflectionClass($instance))->getShortName());

                // Combine group prefix and route path
                $fullPath = rtrim($groupPrefix . '/' . ltrim($instance->path, '/'), '/');
                $fullPath = $fullPath === '' ? '/' : $fullPath;

                // Check for duplicate route name
                foreach ($this->routes as $routesByMethod) {
                    foreach ($routesByMethod as $route) {
                        if ($route['name'] === $instance->name) {
                            //throw new \Exception("Duplicate route name detected: '{$instance->name}'");
                        }
                    }
                }

                // Check for duplicate path & method
                if (isset($this->routes[$fullPath][$http_method])) {
                    //throw new \Exception("Duplicate route detected: [$http_method] path: $fullPath");
                }

                // Merge middleware from group and method
                $mergedMiddleware = array_merge($groupMiddleware, $instance->middleware);

                // Register the route
                $this->routes[$fullPath][$http_method] = [
                    'controller' => $controller,
                    'method' => $method->getName(),
                    'middleware' => $mergedMiddleware,
                    'name' => $instance->name
                ];
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
