<?php

declare(strict_types=1);

namespace App\Models;

final class Saida
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $data,
        public readonly string $numeroControle,
        public readonly string $localDestino,
        public readonly array $itens,
    ) {
    }

    public static function fromArray(array $dados): self
    {
        $itens = array_map(
            static fn (array $item) => ItemSaida::fromArray($item),
            $dados['itens'] ?? []
        );

        return new self(
            id: isset($dados['id']) ? (int) $dados['id'] : null,
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
                id: null,
                itemId: $item->itemId,
                quantidade: $quantidadeAtual + $item->quantidade,
            );
        }

        return array_values($consolidado);
    }

    public static function validar(array $dados): array
    {
        $erros = [];

        $data = (string) ($dados['data'] ?? '');
        if ($data === '' || !self::dataValida($data)) {
            $erros[] = 'data é obrigatória e deve estar no formato AAAA-MM-DD';
        }

        $numeroControle = trim((string) ($dados['numero_controle'] ?? ''));
        if ($numeroControle === '') {
            $erros[] = 'numero_controle é obrigatório';
        } elseif (mb_strlen($numeroControle) > 50) {
            $erros[] = 'numero_controle deve ter no máximo 50 caracteres';
        }

        $localDestino = trim((string) ($dados['local_destino'] ?? ''));
        if ($localDestino === '') {
            $erros[] = 'local_destino é obrigatório';
        } elseif (mb_strlen($localDestino) > 150) {
            $erros[] = 'local_destino deve ter no máximo 150 caracteres';
        }

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

    private static function dataValida(string $data): bool
    {
        $convertida = \DateTimeImmutable::createFromFormat('Y-m-d', $data);

        return $convertida !== false && $convertida->format('Y-m-d') === $data;
    }
}
