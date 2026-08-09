<?php

declare(strict_types=1);

namespace App;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    // PUT, DELETE

    private function add(string $method, string $path, callable $handler): void
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method || preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }
            //TODO: entender essa parte melhor
            $params = array_filter($matches, fn ($chave) => is_string($chave), ARRAY_FILTER_USE_KEY);

            try {
                ($route['handler'])($params);
            } catch (\Throwable $e) {
                http_response_code(500);
                echo json_encode(['erro' => $e->getMessage()]);
            }

            return;
        }

        http_response_code(404);
        echo json_encode(['erro' => 'Rota não encontrada']);
    }
}
