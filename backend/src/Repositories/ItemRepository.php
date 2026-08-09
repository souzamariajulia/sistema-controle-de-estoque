<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

final class ItemRepository
{
    public function findAll(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM vw_itens_detalhados');

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM vw_itens_detalhados WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $item = $stmt->fetch();

        return $item === false ? null : $item;
    }
}
