<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;

final class RelatorioRepository
{
    public function gerarEstoque(?int $itemId, ?string $dataInicio, ?string $dataFim): array
    {
        $condicoesEntrada = ['1=1'];
        $condicoesSaida = ['1=1'];
        $parametros = [];

        if ($dataInicio !== null) {
            $condicoesEntrada[] = 'e.data >= :data_inicio_ent';
            $condicoesSaida[] = 's.data >= :data_inicio_sai';
            $parametros['data_inicio_ent'] = $dataInicio;
            $parametros['data_inicio_sai'] = $dataInicio;
        }

        if ($dataFim !== null) {
            $condicoesEntrada[] = 'e.data <= :data_fim_ent';
            $condicoesSaida[] = 's.data <= :data_fim_sai';
            $parametros['data_fim_ent'] = $dataFim;
            $parametros['data_fim_sai'] = $dataFim;
        }

        $condicaoItem = '';
        if ($itemId !== null) {
            $condicaoItem = 'WHERE d.item_id = :item_id';
            $parametros['item_id'] = $itemId;
        }

        $sql = '
            SELECT
                d.item_id,
                d.descricao,
                d.categoria,
                d.subcategoria,
                d.cadastrado_por,
                d.estoque,
                CAST(COALESCE(ent.total, 0) AS SIGNED) AS total_entradas,
                CAST(COALESCE(sai.total, 0) AS SIGNED) AS total_saidas,
                CAST(COALESCE(ent.total, 0) AS SIGNED)
                    - CAST(COALESCE(sai.total, 0) AS SIGNED) AS saldo_movimentado
            FROM vw_itens_detalhados d
            LEFT JOIN (
                SELECT ie.item_id, SUM(ie.quantidade) AS total
                FROM itens_entrada ie
                INNER JOIN entradas e ON e.id = ie.entrada_id
                WHERE ' . implode(' AND ', $condicoesEntrada) . '
                GROUP BY ie.item_id
            ) ent ON ent.item_id = d.item_id
            LEFT JOIN (
                SELECT isa.item_id, SUM(isa.quantidade) AS total
                FROM itens_saida isa
                INNER JOIN saidas s ON s.id = isa.saida_id
                WHERE ' . implode(' AND ', $condicoesSaida) . "
                GROUP BY isa.item_id
            ) sai ON sai.item_id = d.item_id
            {$condicaoItem}
            ORDER BY d.descricao
        ";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }
}
