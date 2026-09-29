# Deploy na Vercel (com banco MySQL no Aiven)

Guia para publicar o Bazar Universitário de graça, **sem cartão de crédito**:

- **Vercel** (plano Hobby) roda o PHP — https://vercel.com
- **Aiven** (plano Free) hospeda o MySQL — https://aiven.io

> O deploy na Vercel foi autorizado pelo professor no lugar da VM do Google Cloud (tópico 6 do enunciado).

Tempo estimado: 30 a 40 minutos.

---

## Como o projeto roda na Vercel

A Vercel não é um servidor Apache comum. Por isso o projeto tem algumas peças extras:

| Peça | Para que serve |
|---|---|
| `vercel.json` | Diz à Vercel para servir `/css` e `/js` direto da pasta `public/` e mandar todas as outras URLs para `api/index.php` |
| `api/index.php` | Ponto de entrada na Vercel. Só repassa para o `public/index.php`, o mesmo front controller do XAMPP |
| `config/config.php` | Lê usuário e senha do banco das **variáveis de ambiente** (nunca ficam no código) |
| `config/ca.pem` | Certificado do Aiven, para a conexão com o banco ser criptografada (SSL) |
| Tabela `sessoes` | A Vercel pode atender cada acesso num servidor diferente, então o login fica no banco e não em arquivo |
| Tabela `fotos` | O disco da Vercel é somente leitura, então as fotos dos itens ficam no banco |

---

## Passo 1 — Subir o projeto no GitHub

Se ainda não fez: crie um repositório **vazio** no GitHub (sem README) e rode, na pasta do projeto:

```bash
git remote add origin https://github.com/SEU-USUARIO/bazar-universitario.git
git push -u origin main
```

## Passo 2 — Criar o banco MySQL no Aiven

1. Crie a conta em **aiven.io** (dá para entrar com a conta Google ou GitHub). Não pede cartão.
2. Clique em **Create service** → **MySQL**.
3. Em plano, escolha **Free**. Se der para escolher a região, prefira uma nos **EUA (leste)**, que fica perto dos servidores da Vercel.
4. Dê um nome (ex.: `bazar-mysql`) e clique em **Create service**. Espere o status ficar **Running** (uns 5 minutos).
5. Na página do serviço, em **Connection information**, anote:
   - **Host** (algo como `bazar-mysql-seunome.x.aivencloud.com`)
   - **Port** (um número como `12345`)
   - **User** (`avnadmin`)
   - **Password** (clique no olho para ver)
6. Ainda ali, em **CA certificate**, clique em **Download**. Salve o arquivo como **`config/ca.pem`** dentro do projeto.

> O `ca.pem` é um certificado **público** (serve para conferir que você está falando com o servidor certo). Pode ir para o GitHub sem problema. A **senha** é que nunca vai para o código.

> O Aiven desliga serviços gratuitos que ficam **sem uso**, inclusive nas primeiras horas depois de criados. Faça o Passo 3 logo em seguida.

## Passo 3 — Criar as tabelas no Aiven

O script `database/instalar.php` roda o `schema.sql` no banco que estiver configurado. Na pasta do projeto, rode (troque pelos seus dados do Aiven):

```bash
DB_HOST=bazar-mysql-seunome.x.aivencloud.com \
DB_PORT=12345 \
DB_USER=avnadmin \
DB_PASS='a-senha-do-aiven' \
DB_SSL=true \
php database/instalar.php --exemplo
```

- `--exemplo` também cria as 2 contas e os 5 itens de demonstração (senha `bazar123`). Sem ele, só as tabelas e as categorias.
- Deve aparecer: `Pronto! Banco "bazar_universitario" instalado em ...`
- Se o comando `php` não existir no seu terminal, use o PHP do XAMPP: `/opt/lampp/bin/php database/instalar.php --exemplo` (Linux) ou `C:\xampp\php\php.exe database\instalar.php --exemplo` (Windows, com as variáveis definidas antes via `set DB_HOST=...`).

Depois, faça commit do certificado e envie:

```bash
git add config/ca.pem
git commit -m "Certificado CA do banco no Aiven"
git push
```

## Passo 4 — Publicar na Vercel

1. Crie a conta em **vercel.com** entrando com o **GitHub** (plano **Hobby**, gratuito, sem cartão).
2. Clique em **Add New… → Project** e escolha o repositório `bazar-universitario` → **Import**.
3. Em **Framework Preset**, deixe **Other**. Não mexa em Build/Output (o `vercel.json` já cuida disso).
4. Abra **Environment Variables** e cadastre:

| Nome | Valor |
|---|---|
| `DB_HOST` | o Host do Aiven |
| `DB_PORT` | a Port do Aiven |
| `DB_USER` | `avnadmin` |
| `DB_PASS` | a senha do Aiven |
| `DB_SSL` | `true` |
| `APP_ENV` | `producao` |

5. Clique em **Deploy** e espere terminar (1 a 2 minutos).
6. Abra o link que aparece (algo como `https://bazar-universitario.vercel.app`).

> `APP_ENV=producao` faz o site mostrar só "erro inesperado" quando algo falha, sem expor detalhes. O detalhe vai para os logs da Vercel.

## Passo 5 — Testar

- [ ] A listagem aparece sem login, com o CSS carregado
- [ ] Criar conta e entrar funcionam, e você **continua logado** ao navegar (confere a sessão no banco)
- [ ] Publicar um item **com foto** e ver a foto no card (confere a tabela `fotos`)
- [ ] Abrir `/itens/1` direto na barra de endereço (confere o `vercel.json`)
- [ ] "Tenho interesse" com a outra conta de exemplo

Por fim, coloque o link no topo do `README.md`, faça commit e push. A Vercel publica de novo sozinha a cada `git push`.

---

## Antes da apresentação / correção

O Aiven pode ter desligado o banco por falta de uso (ele manda um e-mail avisando). Entre no painel do Aiven e, se o serviço estiver **Powered off**, clique em **Power on** e espere ficar **Running**. Sem isso o site mostra "erro inesperado".

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| Página "Ocorreu um erro inesperado" | Banco desligado, variável errada ou certificado faltando | Veja o erro em **Vercel → projeto → Logs**. Confira se o Aiven está **Running** e as variáveis do Passo 4 |
| Log: `DB_SSL=true, mas o arquivo config/ca.pem não foi encontrado` | O `ca.pem` não foi enviado ao GitHub | Refaça o fim do Passo 3 (`git add config/ca.pem` + push) |
| Log: `Access denied for user 'avnadmin'` | Senha errada em `DB_PASS` | Corrija em **Settings → Environment Variables** e clique em **Redeploy** |
| Log: `Unknown database 'bazar_universitario'` | As tabelas não foram criadas | Rode o Passo 3 |
| Site sem estilo (sem cores) | `vercel.json` fora da raiz do repositório | Confira se o `vercel.json` está na mesma pasta do `README.md` |
| Mudei uma variável e nada mudou | A Vercel só lê variáveis novas em um deploy novo | **Deployments → ⋯ → Redeploy** |
| Erro ao enviar foto grande | A Vercel recusa envios acima de 4,5 MB antes de chegar no PHP | Use fotos de até 2 MB (o próprio formulário avisa) |

---

## Rodando no XAMPP (desenvolvimento)

Nada disso é necessário no computador: sem variáveis de ambiente, o `config/config.php` usa `root` sem senha em `127.0.0.1`, sem SSL. Veja a seção "Rodando localmente" do README.
