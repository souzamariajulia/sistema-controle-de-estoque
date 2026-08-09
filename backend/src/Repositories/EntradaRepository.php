<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Entrada;
use InvalidArgumentException;
use Throwable;

final class EntradaRepository
{
    /**
     * Grava o cabeçalho e as linhas da entrada na mesma transação e soma a
     * quantidade ao saldo de cada item. Se qualquer passo falhar, nada é
     * persistido (ROLLBACK).
     */
    public function create(Entrada $entrada): int
    {
        $itens = $entrada->itensConsolidados();

        if ($itens === []) {
            throw new InvalidArgumentException('Entrada deve conter ao menos um item');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmtCabecalho = $pdo->prepare(
                'INSERT INTO entradas (data, numero_nota, fornecedor) VALUES (:data, :numero_nota, :fornecedor)'
            );
            $stmtCabecalho->execute([
                'data' => $entrada->data,
                'numero_nota' => $entrada->numeroNota,
                'fornecedor' => $entrada->fornecedor,
            ]);

            $entradaId = (int) $pdo->lastInsertId();

            $stmtTravaItem = $pdo->prepare('SELECT id FROM itens WHERE id = :id FOR UPDATE');
            $stmtLinha = $pdo->prepare(
                'INSERT INTO itens_entrada (entrada_id, item_id, quantidade) VALUES (:entrada_id, :item_id, :quantidade)'
            );
            $stmtSaldo = $pdo->prepare('UPDATE itens SET estoque = estoque + :quantidade WHERE id = :id');

            foreach ($itens as $item) {
                $stmtTravaItem->execute(['id' => $item->itemId]);

                if ($stmtTravaItem->fetch() === false) {
                    throw new InvalidArgumentException("item_id {$item->itemId} não existe");
                }

                $stmtLinha->execute([
                    'entrada_id' => $entradaId,
                    'item_id' => $item->itemId,
                    'quantidade' => $item->quantidade,
                ]);

                $stmtSaldo->execute([
                    'quantidade' => $item->quantidade,
                    'id' => $item->itemId,
                ]);
            }

            $pdo->commit();

            return $entradaId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM entradas WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $entrada = $stmt->fetch();

        if ($entrada === false) {
            return null;
        }

        $stmtItens = Database::connection()->prepare(
            'SELECT item_id, quantidade FROM itens_entrada WHERE entrada_id = :entrada_id'
        );
        $stmtItens->execute(['entrada_id' => $id]);

        $entrada['itens'] = $stmtItens->fetchAll();

        return $entrada;
    }

    public function findByItem(int $itemId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM vw_movimentacoes WHERE tipo = 'ENTRADA' AND item_id = :item_id ORDER BY data DESC, created_at DESC"
        );
        $stmt->execute(['item_id' => $itemId]);

        return $stmt->fetchAll();
    }
}
