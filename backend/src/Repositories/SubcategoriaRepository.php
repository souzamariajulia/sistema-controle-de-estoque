<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;

final class SubcategoriaRepository
{
    public function existe(int $id): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM subcategorias WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() !== false;
    }
}
