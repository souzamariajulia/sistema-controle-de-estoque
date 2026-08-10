<?php

declare(strict_types=1);

namespace App\Models;

use App\Validation\Validador;

final class Item
{
    public function __construct(
        public readonly int $subcategoriaId,
        public readonly string $descricao,
        public readonly string $cadastradoPor,
        public readonly int $estoque,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            subcategoriaId: (int) $dados['subcategoria_id'],
            descricao: trim((string) $dados['descricao']),
            cadastradoPor: trim((string) $dados['cadastrado_por']),
            estoque: (int) ($dados['estoque'] ?? 0),
        );
    }

    public static function validar(array $dados): array
    {
        $erros = [];

        Validador::idObrigatorio($dados, 'subcategoria_id', $erros);
        Validador::campoObrigatorio($dados, 'descricao', 255, $erros);
        Validador::campoObrigatorio($dados, 'cadastrado_por', 150, $erros);
        Validador::numeroOpcionalMinimo($dados, 'estoque', 0, $erros);

        return $erros;
    }
}
