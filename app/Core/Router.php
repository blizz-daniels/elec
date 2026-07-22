<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Request;
use App\Support\Response;
use RuntimeException;

final class Router
{
    private array $routes = [];

    public function get(string $uri, array $handler): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $handler;
    }

    public function post(string $uri, array $handler): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $handler;
    }

    public function dispatch(): void
    {
        $request = Request::capture();
        $path = $this->normalize($request->path());
        $method = $request->method();

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            Response::notFound('Page not found');
            return;
        }

        [$class, $action] = $handler;
        if (!class_exists($class)) {
            throw new RuntimeException("Controller not found: {$class}");
        }

        $controller = new $class();
        $controller->{$action}($request);
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? $uri : rtrim($uri, '/');
    }
}
