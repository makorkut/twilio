<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Request;
use App\Http\Response;

class Router
{
    protected array $routes = [];
    protected ?Container $container = null;

    public function __construct(?Container $container = null)
    {
        $this->container = $container;
    }

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    protected function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // Try exact match first (faster for static routes)
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];

            if (is_callable($handler)) {
                $result = $this->executeHandler($handler, $request);
                return $result instanceof Response ? $result : Response::json($result);
            }
        }

        // Try pattern matching for dynamic routes
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $handler) {
                // Convert route pattern to regex
                // Example: /admin/products/edit/{id} => /admin/products/edit/([^/]+)
                $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
                $regex = '#^' . $regex . '$#';

                if (preg_match($regex, $path, $matches)) {
                    array_shift($matches); // Remove full match

                    if (is_callable($handler)) {
                        $result = $this->executeHandler($handler, $request, $matches);
                        return $result instanceof Response ? $result : Response::json($result);
                    }
                }
            }
        }

        return Response::notFound();
    }

    protected function executeHandler($handler, Request $request, array $params = [])
    {
        if (is_array($handler)) {
            // Controller@method format
            [$controller, $method] = $handler;

            // Use container for dependency injection if available
            if ($this->container) {
                $instance = $this->container->get($controller);
            } else {
                $instance = new $controller();
            }

            return $instance->$method($request, ...$params);
        }

        // Closure/function format
        // Check if handler expects $request as first parameter
        if ($handler instanceof \Closure) {
            $reflection = new \ReflectionFunction($handler);
            $parameters = $reflection->getParameters();

            // If first parameter is typed as Request or named 'request', pass it
            if (!empty($parameters)) {
                $firstParam = $parameters[0];
                $paramType = $firstParam->getType();
                if (($paramType && $paramType->getName() === 'App\Http\Request') ||
                    $firstParam->getName() === 'request') {
                    return $handler($request, ...$params);
                }
            }

            // Otherwise, just pass route parameters
            return $handler(...$params);
        }

        return $handler($request, ...$params);
    }
}
