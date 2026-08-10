<?php

declare(strict_types=1);

namespace App\Models;

use App\Validation\Validador;

final class Saida
{
    public function __construct(
        public readonly string $data,
        public readonly string $numeroControle,
        public readonly string $localDestino,
        public readonly array $itens,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        $itens = array_map(
            fn (array $item) => ItemSaida::fromArray($item),
            $dados['itens'] ?? []
        );

        return new self(
            data: (string) $dados['data'],
            numeroControle: trim((string) $dados['numero_controle']),
            localDestino: trim((string) $dados['local_destino']),
            itens: $itens,
        );
    }

    public function itensConsolidados(): array
    {
        $consolidado = [];

        foreach ($this->itens as $item) {
            $quantidadeAtual = $consolidado[$item->itemId]->quantidade ?? 0;

            $consolidado[$item->itemId] = new ItemSaida(
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
        Validador::campoObrigatorio($dados, 'numero_controle', 50, $erros);
        Validador::campoObrigatorio($dados, 'local_destino', 150, $erros);

        $itens = $dados['itens'] ?? null;

        if (!is_array($itens) || $itens === []) {
            $erros[] = 'itens é obrigatório e deve conter ao menos um item';
        } else {
            foreach ($itens as $indice => $item) {
                if (!is_array($item)) {
                    $erros[] = "itens[{$indice}] deve ser um objeto";
                    continue;
                }

                foreach (ItemSaida::validar($item) as $erroItem) {
                    $erros[] = "itens[{$indice}]: {$erroItem}";
                }
            }
        }

        return $erros;
    }
}
