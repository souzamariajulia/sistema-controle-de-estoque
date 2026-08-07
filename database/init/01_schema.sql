SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS itens_saida;
DROP TABLE IF EXISTS itens_entrada;
DROP TABLE IF EXISTS saidas;
DROP TABLE IF EXISTS entradas;
DROP TABLE IF EXISTS itens;
DROP TABLE IF EXISTS subcategorias;
DROP TABLE IF EXISTS categorias;

SET FOREIGN_KEY_CHECKS = 1;

-- categorias
CREATE TABLE categorias (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT uq_categorias_nome UNIQUE (nome)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- subcategorias 
CREATE TABLE subcategorias (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id  INT UNSIGNED NOT NULL,
    nome          VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT uq_subcategorias_categoria_nome UNIQUE (categoria_id, nome),
    CONSTRAINT fk_subcategorias_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- itens
CREATE TABLE itens (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    subcategoria_id  INT UNSIGNED NOT NULL,
    descricao        VARCHAR(255) NOT NULL,
    cadastrado_por   VARCHAR(150) NOT NULL,
    estoque          INT UNSIGNED NOT NULL DEFAULT 0,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_itens_subcategoria
        FOREIGN KEY (subcategoria_id) REFERENCES subcategorias (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX ix_itens_subcategoria (subcategoria_id),
    INDEX ix_itens_descricao (descricao)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- entradas 
CREATE TABLE entradas (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    data         DATE         NOT NULL,
    numero_nota  VARCHAR(50)  NOT NULL,
    fornecedor   VARCHAR(150) NOT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX ix_entradas_data (data),
    INDEX ix_entradas_numero_nota (numero_nota),
    INDEX ix_entradas_fornecedor (fornecedor)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- saidas  
CREATE TABLE saidas (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    data             DATE         NOT NULL,
    numero_controle  VARCHAR(50)  NOT NULL,
    local_destino    VARCHAR(150) NOT NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT uq_saidas_numero_controle UNIQUE (numero_controle),
    INDEX ix_saidas_data (data),
    INDEX ix_saidas_local_destino (local_destino)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- itens_entrada 
CREATE TABLE itens_entrada (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entrada_id  INT UNSIGNED NOT NULL,
    item_id     INT UNSIGNED NOT NULL,
    quantidade  INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT uq_itens_entrada_entrada_item UNIQUE (entrada_id, item_id),
    CONSTRAINT ck_itens_entrada_quantidade CHECK (quantidade >= 1),
    CONSTRAINT fk_itens_entrada_entrada
        FOREIGN KEY (entrada_id) REFERENCES entradas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_entrada_item
        FOREIGN KEY (item_id) REFERENCES itens (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX ix_itens_entrada_item (item_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- itens_saida 
CREATE TABLE itens_saida (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    saida_id    INT UNSIGNED NOT NULL,
    item_id     INT UNSIGNED NOT NULL,
    quantidade  INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT uq_itens_saida_saida_item UNIQUE (saida_id, item_id),
    CONSTRAINT ck_itens_saida_quantidade CHECK (quantidade >= 1),
    CONSTRAINT fk_itens_saida_saida
        FOREIGN KEY (saida_id) REFERENCES saidas (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_itens_saida_item
        FOREIGN KEY (item_id) REFERENCES itens (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX ix_itens_saida_item (item_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
