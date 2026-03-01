<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($path);

        $handler = $this->routes[$method][$path] ?? null;
        if (!is_callable($handler)) {
            http_response_code(404);
            echo View::render('errors/not-found', [
                'title' => '404',
                'basePath' => View::detectBasePath(),
            ]);
            return;
        }

        $handler();
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}

