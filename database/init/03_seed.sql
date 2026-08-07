SET NAMES utf8mb4;

INSERT INTO categorias (id, nome) VALUES
    (1, 'Papelaria'),
    (2, 'Mobiliário'),
    (3, 'Informática'),
    (4, 'Limpeza');

INSERT INTO subcategorias (id, categoria_id, nome) VALUES
    (1, 1, 'Papel'),
    (2, 1, 'Escrita'),
    (3, 2, 'Cadeiras'),
    (4, 2, 'Mesas'),
    (5, 3, 'Computadores'),
    (6, 3, 'Periféricos'),
    (7, 4, 'Descartáveis');

INSERT INTO itens (id, subcategoria_id, descricao, cadastrado_por) VALUES
    (1, 1, 'Papel A4 75g - resma 500 folhas', 'Maria Júlia'),
    (2, 2, 'Caneta esferográfica azul',        'Maria Júlia'),
    (3, 3, 'Cadeira giratória com braços',     'Carlos Souza'),
    (4, 4, 'Mesa em L 1,40m',                  'Carlos Souza'),
    (5, 5, 'Computador desktop i5 8GB',        'Ana Lima'),
    (6, 6, 'Teclado USB ABNT2',                'Ana Lima'),
    (7, 7, 'Copo descartável 200ml - pacote',  'João Pedro');

INSERT INTO entradas (id, data, numero_nota, fornecedor) VALUES
    (1, '2026-07-06', '000123', 'Distribuidora Alfa LTDA'),
    (2, '2026-07-14', '000987', 'Móveis Beta ME'),
    (3, '2026-07-28', 'NF-4521', 'Tech Supply S/A');

INSERT INTO itens_entrada (entrada_id, item_id, quantidade) VALUES
    (1, 1, 100),
    (1, 2, 250),
    (1, 7,  40),
    (2, 3,  30),
    (2, 4,  12),
    (3, 5,  15),
    (3, 6,  25);

INSERT INTO saidas (id, data, numero_controle, local_destino) VALUES
    (1, '2026-07-20', 'SAI-2026-0001', 'Loja Centro'),
    (2, '2026-07-30', 'SAI-2026-0002', 'Departamento Financeiro'),
    (3, '2026-08-04', 'SAI-2026-0003', 'Gabinete da Diretoria');

INSERT INTO itens_saida (saida_id, item_id, quantidade) VALUES
    (1, 1, 20),
    (1, 2, 50),
    (2, 1, 15),
    (2, 7, 10),
    (3, 3,  4),
    (3, 5,  2),
    (3, 6,  3);

UPDATE itens i
SET i.estoque = (
        SELECT COALESCE(SUM(ie.quantidade), 0)
        FROM itens_entrada ie WHERE ie.item_id = i.id
    ) - (
        SELECT COALESCE(SUM(isa.quantidade), 0)
        FROM itens_saida isa WHERE isa.item_id = i.id
    );
