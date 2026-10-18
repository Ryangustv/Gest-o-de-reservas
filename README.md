# Gestão de Reservas

Teste técnico desenvolvido com Laravel e React para listar empreendimentos, reservar unidades e consultar ou cancelar reservas.

## Pré-requisitos

- PHP 8.3+ com `pdo_pgsql` e `redis` (`pdo_sqlite` para os testes).
- Composer 2, Node.js 22.12+ ou 24 e npm.
- Docker com Compose v2 instalado e em execução.

## Executar o projeto

O banco Neon já está preparado, e a conexão está preenchida no `.env.example`. Não é necessário criar ou configurar outro banco.

Após clonar o repositório, execute na raiz:

```sh
composer setup
```

O comando executa a instalação das dependências, prepara o ambiente, inicia o Redis, compila o frontend e abre o servidor da aplicação.

Mantenha o terminal e o Docker em execução durante a avaliação.

## Acessar

- Aplicação: [http://localhost:8000](http://localhost:8000)
- Reservas: [http://localhost:8000/reservas](http://localhost:8000/reservas)
- Swagger: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

Para importar os endpoints no Postman, use o botão **Exportar para Postman** no Swagger e importe o arquivo baixado.

## Testes

```sh
php artisan test
```
