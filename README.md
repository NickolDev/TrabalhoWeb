# Bazar Universitário

Aplicação web onde estudantes cadastram itens (livros, materiais, eletrônicos) para **doar** ou **trocar**, e outros alunos podem navegar, filtrar por categoria e demonstrar interesse.

Trabalho final da disciplina **AB722 — Programação para Web II**.
PHP 8 orientado a objetos · padrão MVC · MySQL com PDO · publicado na Vercel (autorizado pelo professor) com banco MySQL no Aiven.

- **Aplicação publicada:** https://trabalhoweb-blond.vercel.app  
- **Autores:**

| Nome | RA |
|---|---|
| Nickolas Goulart Galasso | 842278 |
| Pedro dos Anjos Sanches | 842621 |
| Felipe Martins Nascimento | 842399 |

---

## Funcionalidades

### Requisitos obrigatórios

| # | Requisito | Onde está |
|---|---|---|
| 1 | Cadastro e login (senha com `password_hash()`) | `AuthController`, `Usuario::registrar()`, `Usuario::verificarSenha()` |
| 2 | CRUD completo de itens (nome, descrição, categoria, tipo, dono) | `ItemController` + `ItemDAO` |
| 3 | Listagem pública de itens disponíveis, sem login | `HomeController::index()` |
| 4 | Filtro por categoria (+ busca por texto) | `ItemDAO::listarDisponiveis()` |
| 5 | Página de detalhe com botão **"Tenho interesse"** | `ItemController::show()`, `InteresseController` |
| 6 | Usuário só edita/remove **os próprios** itens | `ItemController::buscarItemDoDono()` + `WHERE usuario_id = ?` no `ItemDAO` |

### Bônus (todos implementados)

- **Upload de foto do item** — `Model/Service/FotoUpload.php` (confere com `getimagesize()` se o arquivo é mesmo uma imagem, limita a 2 MB, gera nome aleatório e guarda a imagem na tabela `fotos`, exibida pela rota `/fotos/{arquivo}`).
- **Marcar como "já doado/trocado"** — o item sai da listagem pública e pode ser reaberto (`ItemController::alternarStatus()`).
- **Painel do usuário** — quantidade de itens cadastrados, disponíveis, concluídos e interesses recebidos, além da lista de itens em que o usuário tem interesse (`PainelController`).

Extras: o dono vê **quem** demonstrou interesse (nome e e-mail para contato), proteção **CSRF** em todos os formulários e layout responsivo.

---

## Tecnologias

- PHP 8.1+ (sem frameworks e sem Composer) — testado do 8.1 ao 8.5
- MySQL 8 / MariaDB via **PDO** com **prepared statements**
- Local: XAMPP (Apache com `mod_rewrite`)
- Nuvem: **Vercel** (runtime `vercel-php`) + **Aiven** (MySQL gratuito, conexão com SSL)
- HTML + CSS próprio + um pouco de JS (só interface)

---

## Estrutura de pastas (MVC)

```
bazar-universitario/
├── public/                  ← única pasta exposta na web
│   ├── index.php            ← Front Controller: toda requisição entra aqui
│   ├── .htaccess            ← (XAMPP) URLs amigáveis → index.php
│   ├── css/style.css
│   └── js/app.js            ← só interface (menu, confirmação, preview da foto)
├── api/index.php            ← (Vercel) ponto de entrada; repassa para public/index.php
├── vercel.json              ← (Vercel) /css e /js estáticos, o resto vai para api/index.php
├── src/
│   ├── autoload.php         ← autoload PSR-4 (App\ → src/)
│   ├── Core/                ← "motor" do MVC
│   │   ├── Router.php       ← mapeia método + URL → Controller@ação
│   │   ├── Controller.php   ← classe base dos controllers
│   │   ├── View.php         ← renderiza templates; e() = htmlspecialchars
│   │   ├── Database.php     ← conexão PDO (Singleton)
│   │   ├── Session.php      ← login, mensagens flash
│   │   ├── SessaoNoBanco.php← guarda as sessões no MySQL (necessário na Vercel)
│   │   ├── Csrf.php, Url.php, Config.php, HttpException.php
│   ├── Controller/          ← Home, Auth, Item, Interesse, Painel, Foto
│   ├── Model/
│   │   ├── Entity/          ← Usuario, Categoria, Item (abstrata), ItemDoacao, ItemTroca, Interesse
│   │   ├── DAO/             ← acesso ao banco (todas as queries com prepare/execute)
│   │   └── Service/         ← FotoUpload
│   └── View/                ← templates (layout, home, itens, auth, painel, erros)
├── config/
│   ├── config.php           ← lê as variáveis de ambiente (senha nunca fica no código)
│   └── ca.pem               ← (Vercel) certificado do Aiven para a conexão SSL
├── database/
│   ├── schema.sql           ← criação do banco e tabelas + categorias
│   ├── dados-exemplo.sql    ← (opcional) 2 usuários e 5 itens para demonstração
│   └── instalar.php         ← roda os .sql pelo terminal (local ou na nuvem)
└── docs/DEPLOY.md           ← passo a passo do deploy na Vercel + Aiven
```

### Como uma requisição percorre o MVC

```
Navegador → public/.htaccess (XAMPP) ou api/index.php (Vercel) → public/index.php (Front Controller)
          → Router → Controller → DAO (Model) → MySQL
                              ↘ Entity (regras)   
          → View (template + layout, saída escapada) → HTML
```

### Orientação a objetos

- **Encapsulamento:** todos os atributos das entidades são `private`; a alteração passa por setters que **validam** os dados (ex.: `Item::setNome()` rejeita nome vazio ou com mais de 120 caracteres; `Usuario::setEmail()` valida o formato).
- **Herança e polimorfismo:** `Item` é **abstrata** e define métodos abstratos (`getTipo()`, `getRotuloTipo()`, `getChamada()`, `getRotuloConcluido()`). `ItemDoacao` e `ItemTroca` herdam toda a lógica comum e implementam só o que muda. A View chama `$item->getRotuloConcluido()` sem saber qual é a subclasse — aparece "Doado" ou "Trocado" automaticamente.
- **Fábrica:** `Item::fabricar($tipo, ...)` devolve a subclasse certa a partir do valor do banco.
- **Herança no Core/DAO:** todos os controllers estendem `Core\Controller` e todos os DAOs estendem `Model\DAO\DAO`.

### Segurança

| Ameaça | Proteção |
|---|---|
| SQL Injection | 100% das queries com `prepare()` + parâmetros; `PDO::ATTR_EMULATE_PREPARES = false` |
| XSS | Toda saída passa por `$this->e()` → `htmlspecialchars(ENT_QUOTES, 'UTF-8')` |
| Senhas | `password_hash()` no cadastro e `password_verify()` no login |
| CSRF | Token por sessão em todos os `POST`, comparado com `hash_equals()` |
| Sequestro de sessão | `session_regenerate_id()` no login, cookie `HttpOnly` + `SameSite=Lax` + `Secure` em HTTPS |
| Acesso a item alheio | Checagem no Controller **e** `WHERE usuario_id = ?` no SQL (retorna 403) |
| Upload malicioso | Tipo verificado pelo conteúdo (`getimagesize`), nome aleatório, imagem guardada no banco (nada é gravado em disco) |
| Exposição de código | Só `public/` é acessível; `src/`, `config/` e `database/` dão 404 |
| Senha do banco | Fica só nas variáveis de ambiente da Vercel; a conexão com o Aiven é criptografada (SSL) |

---

## Rotas

| Método | URL | Ação | Login |
|---|---|---|---|
| GET | `/` | Listagem pública (`?categoria=ID&busca=texto`) | — |
| GET | `/itens/{id}` | Detalhe do item | — |
| GET/POST | `/cadastro` | Criar conta | — |
| GET/POST | `/login` | Entrar | — |
| POST | `/logout` | Sair | ✔ |
| GET | `/itens/novo` · POST `/itens` | Criar item | ✔ |
| GET/POST | `/itens/{id}/editar` | Editar item | ✔ dono |
| POST | `/itens/{id}/remover` | Remover item | ✔ dono |
| POST | `/itens/{id}/status` | Marcar doado/trocado ↔ disponível | ✔ dono |
| POST | `/itens/{id}/interesse` | "Tenho interesse" | ✔ |
| POST | `/itens/{id}/interesse/remover` | Cancelar interesse | ✔ |
| GET | `/painel` | Painel do usuário | ✔ |
| GET | `/fotos/{arquivo}` | Imagem de um item (guardada no banco) | — |

---

## Rodando localmente (XAMPP)

1. Copie a pasta do projeto para `C:\xampp\htdocs\bazar-universitario` (Linux: `/opt/lampp/htdocs/bazar-universitario`).
2. No painel do XAMPP, inicie **Apache** e **MySQL**.
3. Crie o banco de uma destas formas:
   - `http://localhost/phpmyadmin` → aba **Importar** → `database/schema.sql` → **Executar** (opcional: depois `database/dados-exemplo.sql`); ou
   - no terminal, na pasta do projeto: `php database/instalar.php --exemplo`
4. Acesse **http://localhost/bazar-universitario/**

Não é preciso configurar nada: sem variáveis de ambiente, o `config/config.php` usa o padrão do XAMPP (`root` sem senha, em `127.0.0.1`).

O `.htaccess` da raiz redireciona tudo para `public/`, então não é preciso colocar `/public` na URL.

**Contas de demonstração** (se importou `dados-exemplo.sql`): `ana@exemplo.com` e `bruno@exemplo.com`, senha `bazar123`.

> Se as URLs como `/login` derem 404, confira se o `mod_rewrite` está ativo no `httpd.conf` do XAMPP (linha `LoadModule rewrite_module` sem `#`).

---

## Deploy na nuvem (Vercel + Aiven)

O professor autorizou publicar na **Vercel** no lugar da VM do Google Cloud. O passo a passo completo, com a solução de problemas comuns, está em **[docs/DEPLOY.md](docs/DEPLOY.md)**. Resumo:

1. Criar um MySQL gratuito no **Aiven** e baixar o certificado para `config/ca.pem`.
2. Criar as tabelas lá com `php database/instalar.php --exemplo` (usando as variáveis `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS` e `DB_SSL=true`).
3. Importar o repositório na **Vercel** e cadastrar as mesmas variáveis, mais `APP_ENV=producao`.

**O que foi adaptado para a Vercel** (e continua funcionando no XAMPP):

| Na Vercel... | Então o projeto... |
|---|---|
| só roda PHP que está em `api/` | tem o `api/index.php`, que repassa para o `public/index.php` |
| não lê `.htaccess` | tem o `vercel.json` com as rotas |
| pode atender cada acesso num servidor diferente | guarda as sessões no MySQL (`Core/SessaoNoBanco.php`) |
| tem o disco somente leitura | guarda as fotos no MySQL (tabela `fotos`) |
| não tem MySQL | usa o MySQL gratuito do Aiven, com conexão SSL |

---

## Enviar para o GitHub

```bash
# crie um repositório VAZIO no GitHub chamado bazar-universitario (sem README) e depois:
git remote add origin https://github.com/SEU-USUARIO/bazar-universitario.git
git push -u origin main
```

---

## Modelo de dados

Segue o modelo sugerido no enunciado, com três ajustes:

- `itens.foto` (VARCHAR, opcional) — nome da foto do item, para o bônus de upload;
- `UNIQUE (item_id, usuario_id)` em `interesses` — o mesmo aluno não registra interesse duas vezes no mesmo item;
- `ON DELETE CASCADE` em `interesses.item_id` — ao remover um item, os interesses dele somem junto.

E duas tabelas de apoio, necessárias para rodar na Vercel: `fotos` (o conteúdo das imagens) e `sessoes` (as sessões de login).

```
usuarios 1───N itens N───1 categorias
    │            │
    └──N interesses N──┘
```
