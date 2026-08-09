<?php

declare(strict_types=1);

namespace App\Models;

final class ItemSaida
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $itemId,
        public readonly int $quantidade,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            id: isset($dados['id']) ? (int) $dados['id'] : null,
            itemId: (int) $dados['item_id'],
            quantidade: (int) $dados['quantidade'],
        );
    }

    public static function validar(array $dados): array
    {
        $erros = [];

        if (empty($dados['item_id']) || !is_numeric($dados['item_id'])) {
            $erros[] = 'item_id é obrigatório e deve ser numérico';
        }

        if (!isset($dados['quantidade']) || !is_numeric($dados['quantidade']) || (int) $dados['quantidade'] < 1) {
            $erros[] = 'quantidade é obrigatória e deve ser um número inteiro maior ou igual a 1';
        }

        return $erros;
    }
}
