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
        $groupPathPrefix = '';
        $groupNamePrefix = '';
        $groupMiddleware = [];

        foreach ($reflection->getAttributes(Group::class) as $groupAttr) {
            $group = $groupAttr->newInstance();
            $groupPathPrefix = rtrim($group->path_prefix, '/');
            $groupNamePrefix = $group->name_prefix;
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
                $fullPath = rtrim($groupPathPrefix . '/' . ltrim($instance->path, '/'), '/');
                $fullPath = $fullPath === '' ? '/' : $fullPath;

                // Prefix the route name
                $fullName = $groupNamePrefix ? $groupNamePrefix . '.' . $instance->name : $instance->name;

                // Check for duplicate route name
                foreach ($this->routes as $routesByMethod) {
                    foreach ($routesByMethod as $route) {
                        if ($route['name'] === $fullName) {
                            throw new \Exception("Duplicate route name detected: '{$fullName}'");
                        }
                    }
                }

                // Merge middleware from group and method
                $mergedMiddleware = array_merge($groupMiddleware, $instance->middleware);

                // Register the route
                $this->routes[$fullPath][$http_method] = [
                    'controller' => $controller,
                    'method' => $method->getName(),
                    'middleware' => $mergedMiddleware,
                    'name' => $fullName
                ];
            }
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
