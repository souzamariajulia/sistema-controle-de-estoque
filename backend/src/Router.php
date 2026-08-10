<?php

declare(strict_types=1);

namespace App;

use App\Http\RespostaErro;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

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

            $params = array_filter($matches, fn ($chave) => is_string($chave), ARRAY_FILTER_USE_KEY);

            try {
                ($route['handler'])($params);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                RespostaErro::enviar(500, 'Erro interno do servidor.');
            }

            return;
        }

        RespostaErro::enviar(404, 'Rota não encontrada');
    }
}
