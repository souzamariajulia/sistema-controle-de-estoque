# Sistema Controle de Estoque

Sistema para cadastro de itens (papel, cadeira, computador, etc) com controle de
entrada e saída de estoque. Permite cadastrar itens, registrar movimentações,
consultar o histórico de cada item e gerar relatórios de estoque. O projeto
reúne frontend, backend e camada de dados no mesmo repositório.

**Stack:** PHP | MySQL 9.7 (LTS) | HTML/CSS/JavaScript | Docker

## Como rodar o projeto

Os três serviços precisam ser levantados nesta ordem: banco de dados,
backend e, por fim, frontend — cada um depende do anterior estar no ar.

### 1. Pré-requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- PHP >= 8.4, com as extensões `pdo_mysql`, `zip`, `gd` e `fileinfo`
  habilitadas no `php.ini` (as duas últimas são exigidas pelo
  `phpoffice/phpspreadsheet`, usado na exportação de relatórios)
- [Composer](https://getcomposer.org/)

### 2. Clonar o repositório e configurar as variáveis de ambiente

```bash
git clone <url-do-repositorio>
cd sistema-controle-de-estoque
cp .env.example .env      # no PowerShell: Copy-Item .env.example .env
```

O `.env` já vem com valores padrão suficientes para rodar localmente; ajuste
apenas se precisar (por exemplo, se a porta 3306 já estiver ocupada por um
MySQL local, troque `DB_PORT`).

### 3. Subir o banco de dados

```bash
docker compose up -d
```

Na primeira subida, os scripts de `database/init/` são executados
automaticamente, em ordem alfabética, criando o schema e os dados de exemplo.

| Serviço | Endereço | Credenciais |
| --- | --- | --- |
| MySQL | `127.0.0.1:3306` | usuário `estoque` / senha `estoque` / base `estoque` |
| Adminer | http://localhost:8080 | mesmas do MySQL (servidor `db`) |

Se precisar reaplicar os scripts depois de alterar algum `.sql` (eles só
rodam com o volume vazio):

```bash
docker compose down -v && docker compose up -d
```

### 4. Subir o backend

Com o banco no ar, instale as dependências PHP e inicie o servidor embutido:

```bash
composer install
php -S 127.0.0.1:8000 -t backend/public
```

O backend lê a conexão do `.env` na raiz do projeto (`DB_HOST=127.0.0.1`
quando o PHP roda fora do Docker). A API fica disponível em
`http://127.0.0.1:8000/api`.

### 5. Subir o frontend

Com o backend já rodando na porta 8000, em outro terminal:

```bash
php -S 127.0.0.1:8001 -t frontend/public
```

Acesse http://127.0.0.1:8001/itens.html (tela inicial). O backend libera
CORS para a origem definida em `FRONTEND_URL` no `.env` (padrão
`http://127.0.0.1:8001`) — se você mudar a porta do frontend, ajuste essa
variável também e reinicie o backend.
