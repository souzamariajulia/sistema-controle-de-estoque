<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo json_encode(['erro' => 'Rota não encontrada']);
            return;
        }

        try {
            $handler();
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }
}
