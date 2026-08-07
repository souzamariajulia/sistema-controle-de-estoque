# Sistema Controle de Estoque

Cadastro de itens (papel, cadeira, computador...) com controle de entrada e
saída de estoque. Frontend, backend e camada de dados no mesmo repositório.

**Stack:** PHP · MySQL 9.7 (LTS) · HTML/CSS/JavaScript · Docker

O projeto é construído por camada. Estado atual:

- [x] **Camada de dados** — schema, relacionamentos, views e seed
- [ ] Camada de backend — conexão, repositórios e regras de negócio
- [ ] Camada de aplicação/frontend — telas de cadastro, listagem, movimentos e relatório

## Estrutura

```
database/
  init/
    01_schema.sql   tabelas, chaves, índices e constraints
    02_views.sql    views de listagem e relatório
    03_seed.sql     dados de exemplo
  README.md         modelo de dados e onde cada regra de negócio é garantida
docker-compose.yml  MySQL + Adminer
.env.example        variáveis de ambiente
```

## Como subir o banco

Requer [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
cp .env.example .env      # no PowerShell: Copy-Item .env.example .env
docker compose up -d
```

Na primeira subida os scripts de `database/init/` são executados
automaticamente, em ordem alfabética, criando o schema e os dados de exemplo.

| Serviço | Endereço | Credenciais |
| --- | --- | --- |
| MySQL | `127.0.0.1:3306` | usuário `estoque` / senha `estoque` / base `estoque` |
| Adminer | http://localhost:8080 | mesmas do MySQL (servidor `db`) |

Se a porta 3306 já estiver ocupada por um MySQL local, ajuste `DB_PORT` no `.env`.

### Verificar

```bash
docker compose exec db mysql -u estoque -pestoque estoque -e "SHOW TABLES; SELECT * FROM vw_relatorio_estoque;"
```

A coluna `divergencia` do relatório deve vir `0` em todas as linhas.

### Recriar o banco

Os scripts de init só rodam com o volume vazio. Para reaplicá-los depois de
alterar um `.sql`:

```bash
docker compose down -v && docker compose up -d
```

## Modelo de dados

Diagrama e detalhamento das tabelas, constraints e regras de negócio:
[database/README.md](database/README.md).

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
