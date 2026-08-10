<?php

declare(strict_types=1);

use App\Http\RespostaErro;
use App\Models\Entrada;
use App\Models\Item;
use App\Models\Saida;
use App\Repositories\EntradaRepository;
use App\Repositories\ItemRepository;
use App\Repositories\RelatorioRepository;
use App\Repositories\SaidaRepository;
use App\Repositories\SubcategoriaRepository;
use App\Router;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . ($_ENV['FRONTEND_URL'] ?? 'http://127.0.0.1:8001'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

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

$router->get('/api/subcategorias', function (): void {
    echo json_encode((new SubcategoriaRepository())->findAll());
});

$router->get('/api/itens/{id}', function (array $params): void {
    $item = (new ItemRepository())->findById((int) $params['id']);

    if ($item === null) {
        RespostaErro::enviar(404, 'Item não encontrado');
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
        RespostaErro::enviar(400, $erros);
    }

    $repositorio = new ItemRepository();
    $id = $repositorio->create(Item::fromArray($dados));

    http_response_code(201);
    echo json_encode($repositorio->findById($id));
});

$router->put('/api/itens/{id}', function (array $params): void {
    $id = (int) $params['id'];
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $repositorio = new ItemRepository();

    if ($repositorio->findById($id) === null) {
        RespostaErro::enviar(404, 'Item não encontrado');
    }

    $erros = Item::validar($dados);

    if ($erros === [] && !(new SubcategoriaRepository())->existe((int) $dados['subcategoria_id'])) {
        $erros[] = 'subcategoria_id informado não existe';
    }

    if ($erros !== []) {
        RespostaErro::enviar(400, $erros);
    }

    $repositorio->update($id, Item::fromArray($dados));

    echo json_encode($repositorio->findById($id));
});

$router->delete('/api/itens/{id}', function (array $params): void {
    $id = (int) $params['id'];
    $repositorio = new ItemRepository();

    if ($repositorio->findById($id) === null) {
        RespostaErro::enviar(404, 'Item não encontrado');
    }

    if ($repositorio->possuiMovimentacoes($id)) {
        RespostaErro::enviar(400, 'Item possui entradas ou saídas registradas e não pode ser excluído.');
    }

    $repositorio->delete($id);
    http_response_code(204);
});

$router->get('/api/itens/{id}/entradas', function (array $params): void {
    $id = (int) $params['id'];

    if ((new ItemRepository())->findById($id) === null) {
        RespostaErro::enviar(404, 'Item não encontrado');
    }

    echo json_encode((new EntradaRepository())->findByItem($id));
});

$router->post('/api/entradas', function (): void {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $erros = Entrada::validar($dados);

    if ($erros !== []) {
        RespostaErro::enviar(400, $erros);
    }

    $repositorio = new EntradaRepository();

    try {
        $id = $repositorio->create(Entrada::fromArray($dados));
    } catch (InvalidArgumentException $e) {
        RespostaErro::enviar(400, $e->getMessage());
    }

    http_response_code(201);
    echo json_encode($repositorio->findById($id));
});

$router->get('/api/itens/{id}/saidas', function (array $params): void {
    $id = (int) $params['id'];

    if ((new ItemRepository())->findById($id) === null) {
        RespostaErro::enviar(404, 'Item não encontrado');
    }

    echo json_encode((new SaidaRepository())->findByItem($id));
});

$router->post('/api/saidas', function (): void {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $erros = Saida::validar($dados);
    $repositorio = new SaidaRepository();

    if ($erros === [] && $repositorio->numeroControleExiste(trim((string) $dados['numero_controle']))) {
        $erros[] = 'numero_controle informado já está em uso';
    }

    if ($erros !== []) {
        RespostaErro::enviar(400, $erros);
    }

    try {
        $id = $repositorio->create(Saida::fromArray($dados));
    } catch (InvalidArgumentException $e) {
        RespostaErro::enviar(400, $e->getMessage());
    }

    http_response_code(201);
    echo json_encode($repositorio->findById($id));
});

$router->get('/api/relatorios/estoque', function (): void {
    $itemId = isset($_GET['item_id']) ? (int) $_GET['item_id'] : null;
    $dataInicio = $_GET['data_inicio'] ?? null;
    $dataFim = $_GET['data_fim'] ?? null;

    $linhas = (new RelatorioRepository())->gerarEstoque($itemId, $dataInicio, $dataFim);

    if (($_GET['formato'] ?? null) === 'xlsx') {
        $planilha = new Spreadsheet();
        $aba = $planilha->getActiveSheet();

        $aba->fromArray(
            ['Item', 'Categoria', 'Subcategoria', 'Cadastrado por', 'Estoque atual', 'Total entradas', 'Total saidas', 'Saldo movimentado'],
            null,
            'A1'
        );

        $numeroLinha = 2;
        foreach ($linhas as $item) {
            $aba->fromArray([
                $item['descricao'],
                $item['categoria'],
                $item['subcategoria'],
                $item['cadastrado_por'],
                $item['estoque'],
                $item['total_entradas'],
                $item['total_saidas'],
                $item['saldo_movimentado'],
            ], null, "A{$numeroLinha}");
            $numeroLinha++;
        }

        foreach (range('A', 'H') as $coluna) {
            $aba->getColumnDimension($coluna)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="relatorio-estoque.xlsx"');

        (new Xlsx($planilha))->save('php://output');
        return;
    }

    echo json_encode($linhas);
});

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
