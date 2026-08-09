<?php

declare(strict_types=1);

use App\Models\Entrada;
use App\Models\Item;
use App\Repositories\EntradaRepository;
use App\Repositories\ItemRepository;
use App\Repositories\SubcategoriaRepository;
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
    $repositorio = new ItemRepository();
    $termo = $_GET['busca'] ?? null;

    $itens = $termo !== null
        ? $repositorio->search((string) $termo)
        : $repositorio->findAll();

    echo json_encode($itens);
});

$router->get('/api/itens/{id}', function (array $params): void {
    $item = (new ItemRepository())->findById((int) $params['id']);

    if ($item === null) {
        http_response_code(404);
        echo json_encode(['erro' => 'Item não encontrado']);
        return;
    }

    echo json_encode($item);
});

$router->post('/api/itens', function (): void {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $erros = Item::validar($dados);

    if ($erros === [] && !(new SubcategoriaRepository())->existe((int) $dados['subcategoria_id'])) {
        $erros[] = 'subcategoria_id informado não existe';
    }

    if ($erros !== []) {
        http_response_code(400);
        echo json_encode(['erros' => $erros]);
        return;
    }

    $repositorio = new ItemRepository();
    $id = $repositorio->create(Item::fromArray($dados));

    http_response_code(201);
    echo json_encode($repositorio->findById($id));
});

$router->get('/api/itens/{id}/entradas', function (array $params): void {
    $entradas = (new EntradaRepository())->findByItem((int) $params['id']);
    echo json_encode($entradas);
});

$router->post('/api/entradas', function (): void {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $erros = Entrada::validar($dados);

    if ($erros !== []) {
        http_response_code(400);
        echo json_encode(['erros' => $erros]);
        return;
    }

    $repositorio = new EntradaRepository();

    try {
        $id = $repositorio->create(Entrada::fromArray($dados));
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['erros' => [$e->getMessage()]]);
        return;
    }

    http_response_code(201);
    echo json_encode($repositorio->findById($id));
});

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
