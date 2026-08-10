<?php

declare(strict_types=1);

namespace App\Models;

use App\Validation\Validador;

final class Entrada
{
    public function __construct(
        public readonly string $data,
        public readonly string $numeroNota,
        public readonly string $fornecedor,
        public readonly array $itens,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        $itens = array_map(
            fn (array $item) => ItemEntrada::fromArray($item),
            $dados['itens'] ?? []
        );

        return new self(
            data: (string) $dados['data'],
            numeroNota: trim((string) $dados['numero_nota']),
            fornecedor: trim((string) $dados['fornecedor']),
            itens: $itens,
        );
    }

    public function itensConsolidados(): array
    {
        $consolidado = [];

        foreach ($this->itens as $item) {
            $quantidadeAtual = $consolidado[$item->itemId]->quantidade ?? 0;

            $consolidado[$item->itemId] = new ItemEntrada(
                itemId: $item->itemId,
                quantidade: $quantidadeAtual + $item->quantidade,
            );
        }

        return array_values($consolidado);
    }

    public static function validar(array $dados): array
    {
        $erros = [];

        Validador::data($dados, 'data', $erros);
        Validador::campoObrigatorio($dados, 'numero_nota', 50, $erros);
        Validador::campoObrigatorio($dados, 'fornecedor', 150, $erros);

        $itens = $dados['itens'] ?? null;

        if (!is_array($itens) || $itens === []) {
            $erros[] = 'itens é obrigatório e deve conter ao menos um item';
        } else {
            foreach ($itens as $indice => $item) {
                if (!is_array($item)) {
                    $erros[] = "itens[{$indice}] deve ser um objeto";
                    continue;
                }

                foreach (ItemEntrada::validar($item) as $erroItem) {
                    $erros[] = "itens[{$indice}]: {$erroItem}";
                }
            }
        }

        return $erros;
    }
}
