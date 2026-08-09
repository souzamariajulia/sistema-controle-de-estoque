<?php

declare(strict_types=1);

use App\Repositories\ItemRepository;
use App\Router;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

header('Content-Type: application/json; charset=utf-8');

$router = new Router();

$router->get('/api/health', function (): void {
    echo json_encode(['status' => 'ok']);
});

$router->get('/api/itens', function (): void {
    $itens = (new ItemRepository())->findAll();
    echo json_encode($itens);
});

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
