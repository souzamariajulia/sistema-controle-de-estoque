<?php

declare(strict_types=1);

namespace App\Models;

use App\Validation\Validador;

final class ItemSaida
{
    public function __construct(
        public readonly int $itemId,
        public readonly int $quantidade,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            itemId: (int) $dados['item_id'],
            quantidade: (int) $dados['quantidade'],
        );
    }

    public static function validar(array $dados): array
    {
        $erros = [];
        Validador::itemMovimento($dados, $erros);

        return $erros;
    }
}
