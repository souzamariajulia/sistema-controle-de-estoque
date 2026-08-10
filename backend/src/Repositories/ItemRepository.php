<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Item;

final class ItemRepository
{
    public function findAll(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM vw_itens_detalhados ORDER BY descricao');

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM vw_itens_detalhados WHERE item_id = :id');
        $stmt->execute(['id' => $id]);

        $item = $stmt->fetch();

        return $item === false ? null : $item;
    }

    public function search(string $termo): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM vw_itens_detalhados WHERE descricao LIKE :termo ORDER BY descricao'
        );
        $stmt->execute(['termo' => '%' . $termo . '%']);

        return $stmt->fetchAll();
    }

    public function create(Item $item): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO itens (subcategoria_id, descricao, cadastrado_por, estoque)
             VALUES (:subcategoria_id, :descricao, :cadastrado_por, :estoque)'
        );

        $stmt->execute([
            'subcategoria_id' => $item->subcategoriaId,
            'descricao' => $item->descricao,
            'cadastrado_por' => $item->cadastradoPor,
            'estoque' => $item->estoque,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, Item $item): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE itens
             SET subcategoria_id = :subcategoria_id, descricao = :descricao,
                 cadastrado_por = :cadastrado_por, estoque = :estoque
             WHERE id = :id'
        );

        $stmt->execute([
            'subcategoria_id' => $item->subcategoriaId,
            'descricao' => $item->descricao,
            'cadastrado_por' => $item->cadastradoPor,
            'estoque' => $item->estoque,
            'id' => $id,
        ]);
    }

    public function possuiMovimentacoes(int $id): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM itens_entrada WHERE item_id = :id_entrada
             UNION
             SELECT 1 FROM itens_saida WHERE item_id = :id_saida
             LIMIT 1'
        );
        $stmt->execute(['id_entrada' => $id, 'id_saida' => $id]);

        return $stmt->fetch() !== false;
    }

    public function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM itens WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
