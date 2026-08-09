<?php

declare(strict_types=1);

namespace App\Models;

final class Item
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $subcategoriaId,
        public readonly string $descricao,
        public readonly string $cadastradoPor,
        public readonly int $estoque,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            id: isset($dados['id']) ? (int) $dados['id'] : null,
            subcategoriaId: (int) $dados['subcategoria_id'],
            descricao: trim((string) $dados['descricao']),
            cadastradoPor: trim((string) $dados['cadastrado_por']),
            estoque: (int) ($dados['estoque'] ?? 0),
        );
    }

    public static function validar(array $dados): array
    {
        $erros = [];

        if (empty($dados['subcategoria_id']) || !is_numeric($dados['subcategoria_id'])) {
            $erros[] = 'subcategoria_id é obrigatório e deve ser numérico';
        }

        $descricao = trim((string) ($dados['descricao'] ?? ''));
        if ($descricao === '') {
            $erros[] = 'descricao é obrigatório';
        } elseif (mb_strlen($descricao) > 255) {
            $erros[] = 'descricao deve ter no máximo 255 caracteres';
        }

        $cadastradoPor = trim((string) ($dados['cadastrado_por'] ?? ''));
        if ($cadastradoPor === '') {
            $erros[] = 'cadastrado_por é obrigatório';
        } elseif (mb_strlen($cadastradoPor) > 150) {
            $erros[] = 'cadastrado_por deve ter no máximo 150 caracteres';
        }

        if (isset($dados['estoque']) && (!is_numeric($dados['estoque']) || (int) $dados['estoque'] < 0)) {
            $erros[] = 'estoque deve ser um número inteiro maior ou igual a 0';
        }

        return $erros;
    }
}