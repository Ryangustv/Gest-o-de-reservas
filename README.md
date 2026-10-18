# Gestão de Reservas

Aplicação desenvolvida como teste técnico para listar empreendimentos e suas unidades disponíveis, cadastrar reservas com nome e e-mail do cliente e cancelar reservas existentes.

## Funcionalidades

- Listagem de empreendimentos com nome, cidade e unidades disponíveis.
- Formulário de reserva com validação de nome e e-mail.
- Bloqueio da unidade durante a transação para impedir reservas simultâneas.
- Listagem de reservas com as mais recentes primeiro, ordenadas pelo ID decrescente.
- Cancelamento de reserva com liberação da unidade; IDs inexistentes retornam HTTP 404.
- Cache Redis de empreendimentos por 5 minutos, invalidado após criar ou cancelar uma reserva e ao clicar em **Atualizar lista**.
- Navegação entre telas com Inertia, documentação Swagger e exportação OpenAPI para importar no Postman.

O fluxo é público: não há autenticação para cadastrar ou cancelar reservas nesta versão.

## Tecnologias e organização

| Camada | Tecnologias |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 e Eloquent |
| Frontend | React 19 com Hooks, Inertia 3, Vite 8 e Tailwind CSS 4 |
| Persistência | PostgreSQL; também pode ser utilizado o Neon |
| Cache | Redis com a extensão PHP `phpredis` |
| Documentação | L5-Swagger e OpenAPI |
| Testes | PHPUnit, SQLite em memória e cache em memória |

```text
app/
  Http/Controllers/       Recebimento das requisições e respostas
  Http/Requests/          Validação do cadastro de reserva
  Models/                Modelos e relacionamentos
  Repositories/          Interfaces e acesso ao banco
  Services/              Regras de reserva e cache
  ViewModels/            Dados enviados ao frontend
  OpenAPI/               Documentação dos endpoints
resources/js/
  Components/            Formulário de reserva
  Pages/                 Telas de empreendimentos e reservas
resources/css/           Estilos da aplicação
database/                Migrations e dados de demonstração
tests/Unit/              Testes unitários
tests/Feature/           Testes de rotas, integração e cache
compose.yaml             PostgreSQL e Redis para desenvolvimento
```

## Pré-requisitos

- PHP 8.3 ou superior e Composer 2.
- Extensões PHP exigidas pelo Composer, além de `pdo_pgsql` e `redis`.
- Extensão `pdo_sqlite` para executar todos os testes de banco.
- Node.js 22.12+ ou 24 e npm.
- Docker com Docker Compose v2 para executar PostgreSQL e Redis localmente.

PHP e Node executam na máquina; o Compose fornece os dois serviços de infraestrutura. Também é possível usar PostgreSQL e Redis já instalados, ajustando o `.env`.

Confira as ferramentas e extensões:

```sh
php -v
composer -V
node -v
npm -v
docker compose version
php --ri pdo_pgsql
php --ri redis
php --ri pdo_sqlite
```

No Windows, habilite as extensões no `php.ini` informado por `php --ini`. A extensão Redis deve corresponder à versão do PHP, arquitetura e tipo TS/NTS. Depois de habilitá-la, reinicie o servidor PHP. A extensão é o cliente de conexão; o servidor Redis também precisa estar em execução.

Se o PowerShell bloquear `npm.ps1`, utilize `npm.cmd` no lugar de `npm` nos comandos abaixo.

## Instalação e execução local

Execute os comandos na raiz do repositório, após cloná-lo.

### 1. Configure o ambiente

Em uma instalação nova, copie o arquivo de exemplo:

```sh
cp .env.example .env
```

No PowerShell:

```powershell
Copy-Item .env.example .env
```

Se já existe um `.env` configurado, preserve-o e ajuste somente as variáveis necessárias. Não publique o `.env` nem credenciais de serviços externos.

O `.env.example` usa estes valores locais, compatíveis com o Compose:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gestao_reservas
DB_USERNAME=gestao
DB_PASSWORD=gestao_local
DB_SSLMODE=prefer

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

As sessões ficam em arquivos; não é necessária uma tabela `sessions`. Os valores de banco acima são destinados ao ambiente local.

### 2. Instale as dependências

```sh
composer install
npm ci
php artisan key:generate
php artisan config:clear
```

### 3. Inicie PostgreSQL e Redis

Com o Docker em execução:

```sh
docker compose up -d --wait
docker compose ps
```

Os serviços ficam disponíveis apenas no computador local, nas portas `5432` e `6379`. O volume do PostgreSQL preserva os dados entre reinicializações. O Redis deve permanecer disponível enquanto a aplicação estiver sendo usada.

Se uma porta já estiver ocupada, altere `DB_PORT` ou `REDIS_PORT` no `.env` antes de iniciar o Compose. Se já possui PostgreSQL ou Redis, reutilize o serviço existente ou inicie somente o serviço necessário, por exemplo:

```sh
docker compose up -d --wait postgres
```

### 4. Crie as tabelas e os dados de demonstração

Confira se o `.env` aponta para o banco que deseja preparar e execute:

```sh
php artisan migrate
php artisan db:seed
php artisan l5-swagger:generate
```

O seeder cria **2 empreendimentos e 6 unidades disponíveis** em um banco novo. Pode ser executado novamente sem duplicar esses registros nem alterar o status de unidades existentes. Reservas são criadas pela interface. Caso já tenha aberto a listagem antes de executar o seeder, clique em **Atualizar lista** para renovar o cache.

### 5. Compile o frontend e inicie a aplicação

```sh
npm run build
php artisan serve
```

Acesse:

- Aplicação: [http://localhost:8000](http://localhost:8000).
- Empreendimentos: [http://localhost:8000/empreendimentos](http://localhost:8000/empreendimentos).
- Reservas: [http://localhost:8000/reservas](http://localhost:8000/reservas).
- Swagger: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation).

Para desenvolver com atualização automática do frontend, mantenha dois terminais abertos:

```sh
# Terminal 1
php artisan serve
```

```sh
# Terminal 2
npm run dev
```

O servidor Vite fornece os arquivos do frontend; as páginas devem ser abertas pela URL do Laravel, na porta `8000`.

## Uso com Neon

O PostgreSQL do Compose é suficiente para avaliar o projeto sem depender de uma conta externa. Para utilizar um banco Neon, substitua as variáveis de banco no `.env` pelas credenciais do seu projeto:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=SEU_HOST_FORNECIDO_PELO_NEON
DB_PORT=5432
DB_DATABASE=SEU_BANCO
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA
DB_SSLMODE=require
```

`DB_HOST` recebe somente o hostname, sem `postgresql://`, nome do banco ou parâmetros de URL. Após modificar o `.env`, execute `php artisan config:clear` e reinicie `php artisan serve`. Nesse cenário, pode iniciar somente o Redis local:

```sh
docker compose up -d --wait redis
```

Execute as migrations no banco Neon escolhido. A latência da conexão remota pode influenciar o tempo de carregamento das reservas.

## Endpoints

| Método | Rota | Comportamento |
| --- | --- | --- |
| GET | `/` | Redireciona para empreendimentos |
| GET | `/empreendimentos` | Tela Inertia ou JSON com `Accept: application/json` |
| GET | `/empreendimentos?atualizar=1` | Invalida o cache e consulta os dados atuais |
| GET | `/reservas` | Tela Inertia com reservas, cliente e unidade |
| POST | `/reservas` | Cadastra reserva e retorna HTTP 201 |
| DELETE | `/reservas/{id}` | Cancela reserva; retorna HTTP 200 ou 404 se inexistente |

Exemplo do corpo JSON para cadastrar:

```json
{
  "unidade_id": 1,
  "cliente_nome": "Ana Silva",
  "cliente_email": "ana@example.com"
}
```

A data da reserva é preenchida pelo backend. Nome e e-mail aceitam até 100 caracteres. Dados inválidos ou unidade indisponível retornam HTTP 422.

As rotas estão em `web.php`: operações POST e DELETE exigem o cookie de sessão e o token CSRF correspondente. O frontend e a interface Swagger configuram o token. No Postman, mantenha a sessão e envie o token em `X-CSRF-TOKEN`; um token ausente ou expirado resulta em HTTP 419.

O header `Accept: application/json` solicita respostas e erros em JSON. A listagem `/reservas` entrega uma página Inertia, não uma API JSON independente.

## Swagger e Postman

As definições estão em `app/OpenAPI`. Depois de editar a documentação, execute:

```sh
php artisan l5-swagger:generate
```

Na página do Swagger, o botão **Exportar para Postman** baixa `gestao-reservas.openapi.json`. No Postman, use **Import**, selecione o arquivo e importe como **Postman Collection**. O arquivo gerado no projeto também pode ser importado diretamente de `storage/api-docs/api-docs.json`.

Ajuste a URL base da coleção para `http://localhost:8000`, conforme necessário. A importação dos endpoints não substitui a configuração de sessão e CSRF para operações de escrita.

## Testes

```sh
# Suíte completa
php artisan test

# Somente unitários
php artisan test --testsuite=Unit

# Somente testes de funcionalidade
php artisan test --testsuite=Feature
```

Os testes usam PHPUnit e possuem métodos com nomes em português. O `phpunit.xml` configura SQLite em memória, sessões em memória e filas síncronas; os testes de cache substituem Redis por memória. Não é necessário acessar Neon, PostgreSQL local ou Redis para executá-los.

A cobertura inclui dados dos ViewModels, listagens, cadastro e cancelamento, validação, unidade indisponível, reserva inexistente, ordenação e comportamento do cache. Sem `pdo_sqlite`, os testes que dependem do banco são ignorados; habilite a extensão para executar a suíte completa.

Se a extensão SQLite está instalada, mas ainda não está habilitada no `php.ini`, pode carregá-la somente durante a execução dos testes:

```sh
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit
```

## Validação manual

1. Abra empreendimentos e confira as unidades disponíveis.
2. Clique em **Reservar**, informe nome e e-mail e confirme.
3. Confira que a unidade deixa a lista de disponíveis.
4. Abra **Visualizar reservas** e confira o cliente, a unidade e a ordenação.
5. Cancele a reserva e volte aos empreendimentos: a unidade deve estar disponível novamente.
6. Clique em **Atualizar lista** para forçar a leitura do banco e renovar o cache.

## Comandos úteis e diagnóstico

```sh
# Conferir as rotas
php artisan route:list --except-vendor

# Conferir conexão e resposta do Redis
php --ri redis
docker compose exec redis redis-cli ping

# Ver logs da infraestrutura
docker compose logs --tail=50

# Parar a infraestrutura, preservando o volume do banco
docker compose down
```

| Problema | Verificação |
| --- | --- |
| `Class "Redis" not found` | Habilite a extensão no PHP utilizado por `php artisan serve` |
| Conexão Redis recusada | Confira `REDIS_HOST`, `REDIS_PORT` e o serviço em `docker compose ps` |
| Tabela `sessions` inexistente | Utilize `SESSION_DRIVER=file` e limpe o cache de configuração |
| Tabelas do domínio inexistentes | Confira o banco configurado e execute `php artisan migrate` |
| Manifesto Vite inexistente | Execute `npm ci` e `npm run build` |
| Erro 419 ao reservar ou cancelar | Recarregue a página para renovar a sessão e o token CSRF |

## Referências

- [Laravel: migrations](https://laravel.com/docs/13.x/migrations)
- [Laravel: seeders](https://laravel.com/docs/13.x/seeding)
- [Docker Compose: iniciar serviços](https://docs.docker.com/reference/cli/docker/compose/up/)
- [Neon: conexão com Laravel](https://neon.com/docs/guides/laravel)
