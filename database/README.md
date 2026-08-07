# Camada de dados

MySQL 9.7 (LTS) / InnoDB / `utf8mb4_unicode_ci`.

## Arquivos

| Arquivo | Conteúdo |
| --- | --- |
| [init/01_schema.sql](init/01_schema.sql) | Tabelas, chaves, índices e constraints |
| [init/02_views.sql](init/02_views.sql) | Views de listagem e relatório |
| [init/03_seed.sql](init/03_seed.sql) | Dados de exemplo |

Os três rodam automaticamente, em ordem alfabética, na **primeira** subida do
container (`/docker-entrypoint-initdb.d`). Para reprocessar, é preciso destruir
o volume — veja "Recriar o banco" no [README principal](../README.md).

## Modelo

```
categorias 1──N subcategorias 1──N itens
                                   │
                     ┌─────────────┴─────────────┐
                     N                           N
              itens_entrada               itens_saida
                     N                           N
                     │                           │
                 entradas                     saidas
```

| Tabela | Papel |
| --- | --- |
| `categorias` | Categoria do item (`nome` único) |
| `subcategorias` | Subcategoria dentro de uma categoria (`nome` único por categoria) |
| `itens` | Produto: `descricao`, `cadastrado_por`, `estoque`, `subcategoria_id` |
| `entradas` | Cabeçalho da compra: `data`, `numero_nota`, `fornecedor` |
| `itens_entrada` | Linhas da entrada: `item_id`, `quantidade` |
| `saidas` | Cabeçalho do uso/venda: `data`, `numero_controle` (único), `local_destino` |
| `itens_saida` | Linhas da saída: `item_id`, `quantidade` |

O cabeçalho (`entradas`/`saidas`) separado das linhas (`itens_entrada`/`itens_saida`)
é o que atende a regra "deve ser possível adicionar mais um item na entrada e/ou
saída": um movimento tem N itens.

## Regras de negócio no banco

| Regra | Como é garantida |
| --- | --- |
| Número de controle da saída é único | `uq_saidas_numero_controle` |
| Quantidade mínima de entrada/saída é 1 | `CHECK (quantidade >= 1)` nas duas tabelas de linha |
| Saldo não pode ficar negativo | `itens.estoque` é `INT UNSIGNED` e o servidor roda em `STRICT_ALL_TABLES` — um `UPDATE` que levaria o saldo abaixo de zero é recusado com erro, não truncado |
| Item com movimento não é apagado | `ON DELETE RESTRICT` nas FKs de `item_id` |
| Apagar um movimento apaga suas linhas | `ON DELETE CASCADE` nas FKs de `entrada_id`/`saida_id` |
| Item não se repete na mesma entrada/saída | `uq_itens_entrada_entrada_item` / `uq_itens_saida_saida_item` (quantidades devem ser somadas na mesma linha) |

**Fica para a camada de serviço** (não é expressável em constraint declarativa):

- **Não gerar entrada/saída sem itens** — o cabeçalho é inserido antes das
  linhas, então dentro da transação: insere cabeçalho, insere as linhas, e só
  faz `COMMIT` se houver ao menos uma linha; caso contrário `ROLLBACK`.
- **Atualização de `itens.estoque`** — cada movimento roda em transação com
  `SELECT ... FOR UPDATE` no item, para serializar saídas concorrentes sobre o
  mesmo saldo. O banco é a rede de segurança (`UNSIGNED`), não a primeira
  checagem: a validação amigável ("saldo insuficiente") vem do serviço.

A view `vw_relatorio_estoque` expõe a coluna `divergencia`
(`estoque - (entradas - saidas)`), que deve ser sempre `0` — útil como
conferência do trabalho da camada de serviço.