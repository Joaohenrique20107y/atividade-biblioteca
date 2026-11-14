# Sistema de Biblioteca em Camadas

Projeto desenvolvido para a atividade **Sistema de Biblioteca em camadas**. A aplicação usa PHP, MySQL, HTML, CSS e JavaScript (`fetch`).

## 1. Requisitos

- PHP 8.1 ou superior, com as extensões `PDO` e `pdo_mysql` habilitadas.
- MySQL 8 ou compatível (MariaDB recente também pode funcionar).
- Navegador atualizado.

## 2. Estrutura

```text
atividade-biblioteca/
├── database/
│   └── schema.sql
├── public/
│   ├── index.php
│   ├── index.html
│   ├── livros.html
│   ├── leitores.html
│   ├── emprestimos.html
│   ├── devolucao.html
│   ├── css/style.css
│   └── js/
├── src/
│   ├── config/       # conexão PDO
│   ├── proxy/        # entrada e encaminhamento das requisições
│   ├── controllers/  # recebe requisições HTTP
│   ├── services/     # regras de negócio
│   ├── dao/          # comandos SQL
│   ├── models/       # entidades Livro, Leitor e Emprestimo
│   └── core/         # resposta JSON e leitura do corpo da requisição
└── README.md
```

Fluxo usado: **Proxy → Controller → Service → DAO → Model**. As regras de negócio ficam nos Services.

## 3. Configurar o banco de dados

1. Inicie o MySQL.
2. Abra o phpMyAdmin ou um terminal MySQL.
3. Execute todo o conteúdo de `database/schema.sql`. O script cria o banco `biblioteca` e as tabelas `livros`, `leitores` e `emprestimos`.
4. Por padrão, a conexão usa `127.0.0.1:3306`, banco `biblioteca`, usuário `root` e senha vazia.

Para mudar a conexão, configure as variáveis de ambiente `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` antes de iniciar o PHP.

## 4. Executar o sistema

No terminal, entre na pasta do projeto e inicie o servidor PHP:

```bash
php -S localhost:8000 -t public public/index.php
```

Acesse no navegador: <http://localhost:8000>

No Windows com XAMPP, também é possível iniciar Apache e MySQL, importar `database/schema.sql` pelo phpMyAdmin e colocar a pasta do projeto em `htdocs`. Aponte o navegador para `http://localhost/atividade-biblioteca/public/`. O arquivo `.htaccess` encaminha as rotas da API para `index.php` quando o `mod_rewrite` está habilitado.

## 5. Telas

- `index.html`: menu principal.
- `livros.html`: listar, cadastrar, editar e excluir livros.
- `leitores.html`: listar, cadastrar, editar e excluir leitores.
- `emprestimos.html`: registrar empréstimo e consultar empréstimos em aberto.
- `devolucao.html`: registrar devolução de empréstimos em aberto.

As telas fazem as requisições ao back-end com `fetch`.

## 6. Rotas da API

| Método | Rota | Ação |
|---|---|---|
| GET | `/api/livros` | Listar livros e quantidade disponível |
| POST | `/api/livros` | Cadastrar livro |
| PUT | `/api/livros/{id}` | Editar livro |
| DELETE | `/api/livros/{id}` | Excluir livro |
| GET | `/api/leitores` | Listar leitores |
| POST | `/api/leitores` | Cadastrar leitor |
| PUT | `/api/leitores/{id}` | Editar leitor |
| DELETE | `/api/leitores/{id}` | Excluir leitor |
| GET | `/api/emprestimos` | Listar todos os empréstimos |
| GET | `/api/emprestimos?status=abertos` | Listar empréstimos em aberto |
| POST | `/api/emprestimos` | Registrar empréstimo |
| POST | `/api/devolucoes/{id}` | Registrar devolução |

Os dados enviados à API usam JSON. Quando uma regra é violada, a API responde no formato `{"erro":"mensagem"}`, conforme solicitado na atividade.

## 7. Regras de negócio implementadas

1. Livro: título e autor são obrigatórios.
2. Livro: o ano deve estar entre 1450 e o ano atual; a quantidade deve ser pelo menos 1.
3. Empréstimo: só pode ocorrer quando `quantidade - empréstimos em aberto` for maior que zero.
4. Empréstimo: cada leitor pode ter no máximo 3 empréstimos em aberto.
5. Empréstimo: o leitor não pode pegar outro exemplar do mesmo livro enquanto já tiver um empréstimo desse livro em aberto.
6. Devolução: somente empréstimos em aberto podem ser devolvidos; a data é gravada pelo MySQL com `CURDATE()`.

O registro de empréstimo usa transação e bloqueio da linha do livro para reduzir o risco de dois pedidos retirarem simultaneamente o último exemplar. Livros e leitores que já possuem histórico de empréstimos não podem ser excluídos, preservando a integridade dos registros.

## 8. Observação

O projeto foi preparado para MySQL/PDO e não inclui dados de exemplo. Cadastre primeiro alguns livros e leitores para testar os empréstimos.
