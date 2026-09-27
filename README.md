# Bazar Universitário

Aplicação web onde estudantes cadastram itens (livros, materiais, eletrônicos) para **doar** ou **trocar**, e outros alunos podem navegar, filtrar por categoria e demonstrar interesse.

Trabalho final da disciplina **AB722 — Programação para Web II**.
PHP 8 orientado a objetos · padrão MVC · MySQL com PDO · deploy em VM Linux no Google Cloud.

- **Aplicação publicada:** http://SEU-IP-OU-DUCKDNS/  <!-- trocar pelo link real -->
- **Autor(es):** Nickolas Goulart Galasso <!-- adicionar dupla, se houver -->

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

- **Upload de foto do item** — `Model/Service/FotoUpload.php` (confere com `getimagesize()` se o arquivo é mesmo uma imagem, limita a 2 MB, gera nome aleatório).
- **Marcar como "já doado/trocado"** — o item sai da listagem pública e pode ser reaberto (`ItemController::alternarStatus()`).
- **Painel do usuário** — quantidade de itens cadastrados, disponíveis, concluídos e interesses recebidos, além da lista de itens em que o usuário tem interesse (`PainelController`).

Extras: o dono vê **quem** demonstrou interesse (nome e e-mail para contato), proteção **CSRF** em todos os formulários e layout responsivo.

---

## Tecnologias

- PHP 8.1+ (sem frameworks e sem Composer)
- MySQL 8 / MariaDB via **PDO** com **prepared statements**
- Apache com `mod_rewrite`
- HTML + CSS próprio + um pouco de JS (só interface)

---

## Estrutura de pastas (MVC)

```
bazar-universitario/
├── public/                  ← única pasta exposta na web (DocumentRoot)
│   ├── index.php            ← Front Controller: toda requisição entra aqui
│   ├── .htaccess            ← URLs amigáveis → index.php
│   ├── css/style.css
│   ├── js/app.js            ← só interface (menu, confirmação, preview da foto)
│   └── uploads/             ← fotos dos itens (PHP bloqueado aqui)
├── src/
│   ├── autoload.php         ← autoload PSR-4 (App\ → src/)
│   ├── Core/                ← "motor" do MVC
│   │   ├── Router.php       ← mapeia método + URL → Controller@ação
│   │   ├── Controller.php   ← classe base dos controllers
│   │   ├── View.php         ← renderiza templates; e() = htmlspecialchars
│   │   ├── Database.php     ← conexão PDO (Singleton)
│   │   ├── Session.php      ← login, mensagens flash
│   │   ├── Csrf.php, Url.php, Config.php, HttpException.php
│   ├── Controller/          ← Home, Auth, Item, Interesse, Painel
│   ├── Model/
│   │   ├── Entity/          ← Usuario, Categoria, Item (abstrata), ItemDoacao, ItemTroca, Interesse
│   │   ├── DAO/             ← acesso ao banco (todas as queries com prepare/execute)
│   │   └── Service/         ← FotoUpload
│   └── View/                ← templates (layout, home, itens, auth, painel, erros)
├── config/
│   └── config.example.php   ← copiar para config.php (este fica fora do Git)
├── database/
│   ├── schema.sql           ← criação do banco e tabelas + categorias
│   └── dados-exemplo.sql    ← (opcional) 2 usuários e 5 itens para demonstração
├── deploy/000-default.conf  ← VirtualHost do Apache para a VM
└── docs/DEPLOY.md           ← passo a passo do deploy no Google Cloud
```

### Como uma requisição percorre o MVC

```
Navegador → public/.htaccess → public/index.php (Front Controller)
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
| Sequestro de sessão | `session_regenerate_id()` no login, cookie `HttpOnly` + `SameSite=Lax` |
| Acesso a item alheio | Checagem no Controller **e** `WHERE usuario_id = ?` no SQL (retorna 403) |
| Upload malicioso | Tipo verificado pelo conteúdo (`getimagesize`), nome aleatório, PHP desativado em `uploads/` |
| Exposição de código | Só `public/` é acessível; `config.php` fora do Git |

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

---

## Rodando localmente (XAMPP)

1. Copie a pasta do projeto para `C:\xampp\htdocs\bazar-universitario` (Linux: `/opt/lampp/htdocs/bazar-universitario`).
2. No painel do XAMPP, inicie **Apache** e **MySQL**.
3. Abra `http://localhost/phpmyadmin` → aba **Importar** → selecione `database/schema.sql` → **Executar**.
   (Opcional: importe também `database/dados-exemplo.sql`.)
4. Copie `config/config.example.php` para `config/config.php`. O padrão (`root` sem senha) já funciona no XAMPP.
5. Acesse **http://localhost/bazar-universitario/**

O `.htaccess` da raiz redireciona tudo para `public/`, então não é preciso colocar `/public` na URL.

**Contas de demonstração** (se importou `dados-exemplo.sql`): `ana@exemplo.com` e `bruno@exemplo.com`, senha `bazar123`.

> Se as URLs como `/login` derem 404, confira se o `mod_rewrite` está ativo no `httpd.conf` do XAMPP (linha `LoadModule rewrite_module` sem `#`).

---

## Deploy na nuvem (Google Cloud)

Resumo — o passo a passo completo, com os comandos e a solução de problemas comuns, está em **[docs/DEPLOY.md](docs/DEPLOY.md)**.

1. VM **e2-micro** com **Ubuntu 22.04 LTS**, tráfego HTTP liberado, região `us-central1`/`us-east1`/`us-west1`.
2. `sudo apt install -y apache2 php libapache2-mod-php php-mysql mysql-server git` (o mesmo comando do enunciado)
3. `mysql_secure_installation`, criação do banco `bazar_universitario` e do usuário `bazar_app`.
4. `git clone` em `/var/www/html`, `sudo mysql < database/schema.sql`, criação do `config/config.php` com `debug => false`.
5. `DocumentRoot /var/www/html/public` + `AllowOverride All` + `a2enmod rewrite` (arquivo pronto em `deploy/000-default.conf`).
6. Acessar `http://IP-EXTERNO/` (opcional: subdomínio DuckDNS).

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

- `itens.foto` (VARCHAR, opcional) — para o bônus de upload;
- `UNIQUE (item_id, usuario_id)` em `interesses` — o mesmo aluno não registra interesse duas vezes no mesmo item;
- `ON DELETE CASCADE` em `interesses.item_id` — ao remover um item, os interesses dele somem junto.

```
usuarios 1───N itens N───1 categorias
    │            │
    └──N interesses N──┘
```
