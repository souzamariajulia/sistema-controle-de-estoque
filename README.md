# Sistema Controle de Estoque

Cadastro de itens (papel, cadeira, computador...) com controle de entrada e
saída de estoque. Frontend, backend e camada de dados no mesmo repositório.

**Stack:** PHP · MySQL 9.7 (LTS) · HTML/CSS/JavaScript · Docker

O projeto é construído por camada. Estado atual:

- [x] **Camada de dados** — schema, relacionamentos, views e seed
- [x] **Camada de backend** — conexão, roteamento e repositórios (base)
- [ ] Regras de negócio (entradas/saídas, transações) e camada de aplicação/frontend

## Estrutura

```
database/
  init/
    01_schema.sql   tabelas, chaves, índices e constraints
    02_views.sql    views de listagem e relatório
    03_seed.sql     dados de exemplo
  README.md         modelo de dados e onde cada regra de negócio é garantida
backend/
  public/index.php  front controller (roteamento + endpoints da API)
  src/
    Config/Database.php    conexão PDO (singleton)
    Repositories/           uma classe por tabela/agregado
    Router.php              roteador minimalista (sem framework)
docker-compose.yml  MySQL + Adminer
composer.json       dependências PHP (vlucas/phpdotenv)
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

## Como subir o backend

Requer PHP >= 8.4 e [Composer](https://getcomposer.org/), com as extensões
`pdo_mysql` e `zip` habilitadas no `php.ini`.

```bash
composer install
php -S 127.0.0.1:8000 -t backend/public
```

| Rota | Descrição |
| --- | --- |
| `GET /api/health` | Verifica se a API está no ar |
| `GET /api/itens` | Lista os itens (via `vw_itens_detalhados`) |

O backend lê a conexão do `.env` na raiz do projeto (`DB_HOST=127.0.0.1`
quando o PHP roda fora do Docker). O banco (MySQL + Adminer) precisa estar
no ar — veja "Como subir o banco" acima.

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
