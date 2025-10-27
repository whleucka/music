<?php

namespace Echo\Framework\Routing;

use Echo\Framework\Routing\Route;
use ReflectionClass;

class Collector
{
    private array $routes = [];

    /**
     * Register controller routes
     * This method is madness. It will attempt to deal with 
     * route attribute inheritance
     */
    public function register(string $controller): void
    {
        $reflection = new \ReflectionClass($controller);

        // We can skip abstract classes (like AdminController)
        if ($reflection->isAbstract()) {
            return;
        }

        // Step 1: Walk class chain and collect [class => group]
        $groupStack = [];
        $current = $reflection;
        while ($current) {
            $groupAttr = $current->getAttributes(\Echo\Framework\Routing\Group::class);
            $group = $groupAttr ? $groupAttr[0]->newInstance() : null;
            $groupStack[$current->getName()] = $group;
            $current = $current->getParentClass();
        }

        // Step 2: Go through all methods (inherited too)
        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                if (!is_subclass_of($instance, Route::class)) {
                    continue;
                }

                $httpMethod = strtolower((new \ReflectionClass($instance))->getShortName());

                // Step 3: Merge group settings from declaring class up to root
                $declaringClass = $method->getDeclaringClass()->getName();

                $pathPrefix = '';
                $namePrefix = '';
                $groupMiddleware = [];

                foreach (array_reverse($groupStack) as $group) {
                    if (!$group) continue;

                    if ($group->path_prefix) {
                        $pathPrefix .= '/' . trim($group->path_prefix, '/');
                    }
                    if ($group->name_prefix) {
                        $namePrefix .= ($namePrefix ? '.' : '') . trim($group->name_prefix, '.');
                    }
                    $groupMiddleware = array_merge($groupMiddleware, $group->middleware);
                }


                // Build full path and name
                $fullPath = rtrim($pathPrefix . '/' . ltrim($instance->path, '/'), '/');
                $fullPath = $fullPath === '' ? '/' : $fullPath;

                $fullName = $namePrefix;
                if ($instance->name) {
                    $fullName .= ($fullName ? '.' : '') . $instance->name;
                }

                // Check for duplicates
                foreach ($this->routes as $routesByMethod) {
                    foreach ($routesByMethod as $route) {
                        if ($route['name'] === $fullName) {
                            throw new \Exception("Duplicate route name detected: '{$fullName}'");
                        }
                    }
                }

                if (isset($this->routes[$fullPath][$httpMethod])) {
                    throw new \Exception("Duplicate route detected: [$httpMethod] path: $fullPath");
                }

                $mergedMiddleware = array_merge($groupMiddleware, $instance->middleware);

                $this->routes[$fullPath][$httpMethod] = [
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
