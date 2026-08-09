SET NAMES utf8mb4;

DROP VIEW IF EXISTS vw_itens_detalhados;
DROP VIEW IF EXISTS vw_movimentacoes;
DROP VIEW IF EXISTS vw_relatorio_estoque;

CREATE VIEW vw_itens_detalhados AS
SELECT
    i.id                AS item_id,
    i.descricao,
    i.cadastrado_por,
    i.estoque,
    sc.id               AS subcategoria_id,
    sc.nome             AS subcategoria,
    c.id                AS categoria_id,
    c.nome              AS categoria,
    i.created_at,
    i.updated_at
FROM itens i
INNER JOIN subcategorias sc ON sc.id = i.subcategoria_id
INNER JOIN categorias    c  ON c.id  = sc.categoria_id;

CREATE VIEW vw_movimentacoes AS
SELECT
    'ENTRADA' COLLATE utf8mb4_unicode_ci AS tipo,
    ie.id                    AS movimento_item_id,
    e.id                     AS movimento_id,
    ie.item_id,
    ie.quantidade,
    e.data,
    e.numero_nota            AS documento,
    e.fornecedor             AS origem_destino,
    ie.created_at
FROM itens_entrada ie
INNER JOIN entradas e ON e.id = ie.entrada_id

UNION ALL

SELECT
    'SAIDA' COLLATE utf8mb4_unicode_ci AS tipo,
    isa.id                   AS movimento_item_id,
    s.id                     AS movimento_id,
    isa.item_id,
    isa.quantidade,
    s.data,
    s.numero_controle        AS documento,
    s.local_destino          AS origem_destino,
    isa.created_at
FROM itens_saida isa
INNER JOIN saidas s ON s.id = isa.saida_id;

CREATE VIEW vw_relatorio_estoque AS
SELECT
    d.item_id,
    d.descricao,
    d.categoria,
    d.subcategoria,
    d.cadastrado_por,
    d.estoque,
    
    CAST(COALESCE(ent.total, 0) AS SIGNED)       AS total_entradas,
    CAST(COALESCE(sai.total, 0) AS SIGNED)       AS total_saidas,
    CAST(COALESCE(ent.total, 0) AS SIGNED)
        - CAST(COALESCE(sai.total, 0) AS SIGNED) AS saldo_calculado,
    CAST(d.estoque AS SIGNED)
        - (CAST(COALESCE(ent.total, 0) AS SIGNED)
           - CAST(COALESCE(sai.total, 0) AS SIGNED)) AS divergencia
FROM vw_itens_detalhados d
LEFT JOIN (
    SELECT item_id, SUM(quantidade) AS total
    FROM itens_entrada
    GROUP BY item_id
) ent ON ent.item_id = d.item_id
LEFT JOIN (
    SELECT item_id, SUM(quantidade) AS total
    FROM itens_saida
    GROUP BY item_id
) sai ON sai.item_id = d.item_id;
