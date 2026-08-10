<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Saida;
use InvalidArgumentException;
use Throwable;

final class SaidaRepository
{
    public function numeroControleExiste(string $numeroControle): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM saidas WHERE numero_controle = :numero_controle');
        $stmt->execute(['numero_controle' => $numeroControle]);

        return $stmt->fetch() !== false;
    }

    public function create(Saida $saida): int
    {
        $itens = $saida->itensConsolidados();

        if ($itens === []) {
            throw new InvalidArgumentException('Saída deve conter ao menos um item');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmtCabecalho = $pdo->prepare(
                'INSERT INTO saidas (data, numero_controle, local_destino) VALUES (:data, :numero_controle, :local_destino)'
            );
            $stmtCabecalho->execute([
                'data' => $saida->data,
                'numero_controle' => $saida->numeroControle,
                'local_destino' => $saida->localDestino,
            ]);

            $saidaId = (int) $pdo->lastInsertId();

            $stmtTravaItem = $pdo->prepare('SELECT estoque FROM itens WHERE id = :id FOR UPDATE');
            $stmtLinha = $pdo->prepare(
                'INSERT INTO itens_saida (saida_id, item_id, quantidade) VALUES (:saida_id, :item_id, :quantidade)'
            );
            $stmtSaldo = $pdo->prepare('UPDATE itens SET estoque = estoque - :quantidade WHERE id = :id');

            foreach ($itens as $item) {
                $stmtTravaItem->execute(['id' => $item->itemId]);
                $itemAtual = $stmtTravaItem->fetch();

                if ($itemAtual === false) {
                    throw new InvalidArgumentException("item_id {$item->itemId} não existe");
                }

                if ((int) $itemAtual['estoque'] < $item->quantidade) {
                    throw new InvalidArgumentException(
                        "saldo insuficiente para o item_id {$item->itemId} "
                        . "(disponível: {$itemAtual['estoque']}, solicitado: {$item->quantidade})"
                    );
                }

                $stmtLinha->execute([
                    'saida_id' => $saidaId,
                    'item_id' => $item->itemId,
                    'quantidade' => $item->quantidade,
                ]);

                $stmtSaldo->execute([
                    'quantidade' => $item->quantidade,
                    'id' => $item->itemId,
                ]);
            }

            $pdo->commit();

            return $saidaId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM saidas WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $saida = $stmt->fetch();

        if ($saida === false) {
            return null;
        }

        $stmtItens = Database::connection()->prepare(
            'SELECT item_id, quantidade FROM itens_saida WHERE saida_id = :saida_id'
        );
        $stmtItens->execute(['saida_id' => $id]);

        $saida['itens'] = $stmtItens->fetchAll();

        return $saida;
    }

    public function findByItem(int $itemId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM vw_movimentacoes WHERE tipo = 'SAIDA' AND item_id = :item_id ORDER BY data DESC, created_at DESC"
        );
        $stmt->execute(['item_id' => $itemId]);

        return $stmt->fetchAll();
    }

    public function findAll(): array
    {
        $stmt = Database::connection()->query(
            "SELECT * FROM vw_movimentacoes WHERE tipo = 'SAIDA' ORDER BY data DESC, created_at DESC"
        );

        return $stmt->fetchAll();
    }
}
