<?php

declare(strict_types=1);

namespace App\Validation;

final class Validador
{
    public static function idObrigatorio(array $dados, string $campo, array &$erros): void
    {
        if (empty($dados[$campo]) || !is_numeric($dados[$campo])) {
            $erros[] = "{$campo} é obrigatório e deve ser numérico";
        }
    }

    public static function campoObrigatorio(array $dados, string $campo, int $tamanhoMaximo, array &$erros): string
    {
        $valor = trim((string) ($dados[$campo] ?? ''));

        if ($valor === '') {
            $erros[] = "{$campo} é obrigatório";
        } elseif (mb_strlen($valor) > $tamanhoMaximo) {
            $erros[] = "{$campo} deve ter no máximo {$tamanhoMaximo} caracteres";
        }

        return $valor;
    }

    public static function numeroOpcionalMinimo(array $dados, string $campo, int $minimo, array &$erros): void
    {
        if (isset($dados[$campo]) && (!is_numeric($dados[$campo]) || (int) $dados[$campo] < $minimo)) {
            $erros[] = "{$campo} deve ser um número inteiro maior ou igual a {$minimo}";
        }
    }

    public static function quantidadeMinima(array $dados, int $minimo, array &$erros): void
    {
        if (!isset($dados['quantidade']) || !is_numeric($dados['quantidade']) || (int) $dados['quantidade'] < $minimo) {
            $erros[] = "quantidade é obrigatória e deve ser um número inteiro maior ou igual a {$minimo}";
        }
    }

    public static function data(array $dados, string $campo, array &$erros): string
    {
        $data = (string) ($dados[$campo] ?? '');

        if ($data === '' || !self::dataValida($data)) {
            $erros[] = "{$campo} é obrigatória e deve estar no formato AAAA-MM-DD";
        }

        return $data;
    }

    public static function itemMovimento(array $dados, array &$erros): void
    {
        self::idObrigatorio($dados, 'item_id', $erros);
        self::quantidadeMinima($dados, 1, $erros);
    }

    private static function dataValida(string $data): bool
    {
        $convertida = \DateTimeImmutable::createFromFormat('Y-m-d', $data);

        return $convertida !== false && $convertida->format('Y-m-d') === $data;
    }
}
