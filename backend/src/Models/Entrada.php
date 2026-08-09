<?php

declare(strict_types=1);

namespace App\Models;

final class Entrada
{
    /**
     * @param ItemEntrada[] $itens
     */
    public function __construct(
        public readonly ?int $id,
        public readonly string $data,
        public readonly string $numeroNota,
        public readonly string $fornecedor,
        public readonly array $itens,
    ) {
    } 

    public static function fromArray(array $dados): self
    {
        $itens = array_map(
            static fn (array $item) => ItemEntrada::fromArray($item),
            $dados['itens'] ?? []
        );

        return new self(
            id: isset($dados['id']) ? (int) $dados['id'] : null,
            data: (string) $dados['data'],
            numeroNota: trim((string) $dados['numero_nota']),
            fornecedor: trim((string) $dados['fornecedor']),
            itens: $itens,
        );
    }

    /**
     * Agrupa itens repetidos somando a quantidade, já que o banco não aceita
     * o mesmo item_id duas vezes na mesma entrada (uq_itens_entrada_entrada_item).
     *
     * @return ItemEntrada[]
     */
    public function itensConsolidados(): array
    {
        $consolidado = [];

        foreach ($this->itens as $item) {
            $quantidadeAtual = $consolidado[$item->itemId]->quantidade ?? 0;

            $consolidado[$item->itemId] = new ItemEntrada(
                id: null,
                itemId: $item->itemId,
                quantidade: $quantidadeAtual + $item->quantidade,
            );
        }

        return array_values($consolidado);
    }

    /**
     * @return string[] lista de mensagens de erro; vazio quando os dados são válidos
     */
    public static function validar(array $dados): array
    {
        $erros = [];

        $data = (string) ($dados['data'] ?? '');
        if ($data === '' || !self::dataValida($data)) {
            $erros[] = 'data é obrigatória e deve estar no formato AAAA-MM-DD';
        }

        $numeroNota = trim((string) ($dados['numero_nota'] ?? ''));
        if ($numeroNota === '') {
            $erros[] = 'numero_nota é obrigatório';
        } elseif (mb_strlen($numeroNota) > 50) {
            $erros[] = 'numero_nota deve ter no máximo 50 caracteres';
        }

        $fornecedor = trim((string) ($dados['fornecedor'] ?? ''));
        if ($fornecedor === '') {
            $erros[] = 'fornecedor é obrigatório';
        } elseif (mb_strlen($fornecedor) > 150) {
            $erros[] = 'fornecedor deve ter no máximo 150 caracteres';
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

                foreach (ItemEntrada::validar($item) as $erroItem) {
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
