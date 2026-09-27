# Deploy no Google Cloud — passo a passo

Guia para publicar o Bazar Universitário numa VM **e2-micro (Always Free)** com **Ubuntu 22.04 LTS**, Apache, PHP e MySQL configurados à mão.

> Antes de começar: o repositório precisa estar no GitHub (de preferência **público**, para o `git clone` funcionar sem senha). Veja a seção "Enviar para o GitHub" no README.

---

## Passo 1 — Criar a VM

1. Acesse `console.cloud.google.com` e crie um projeto novo.
2. **Compute Engine → Instâncias de VM → Criar instância**.
3. Série **E2**, tipo **e2-micro**.
4. Região elegível ao Always Free: `us-west1`, `us-central1` ou `us-east1`.
5. Disco de inicialização: **Ubuntu 22.04 LTS** (disco padrão de até 30 GB continua no nível gratuito).
6. Em Firewall, marque **Permitir tráfego HTTP**.
7. Clique em **Criar** e anote o **IP externo**.

> Dica: em **Rede VPC → Endereços IP**, dá para "reservar" o IP externo como estático, para ele não mudar se a VM reiniciar. (IP estático parado sem VM ligada é cobrado — se apagar a VM, libere o IP.)

## Passo 2 — Instalar o LAMP

Conecte pelo botão **SSH** ao lado da instância e rode:

```bash
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-mysql mysql-server git
```

> É exatamente o comando do enunciado: o projeto só usa funções que já vêm no PHP padrão do Ubuntu (não precisa de `php-mbstring` nem de outras extensões).

Confira a versão do PHP (precisa ser 8+; no Ubuntu 22.04 vem a 8.1):

```bash
php -v
```

### (Recomendado) Criar memória swap

A e2-micro tem só 1 GB de RAM, e o MySQL 8 pode travar a VM por falta de memória. Um swap de 1 GB resolve:

```bash
sudo fallocate -l 1G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

## Passo 3 — Configurar o MySQL

```bash
sudo mysql_secure_installation
```

Respostas sugeridas: ativar validação de senha (opcional), remover usuários anônimos **Y**, desabilitar login remoto do root **Y**, remover banco de teste **Y**, recarregar privilégios **Y**.

Crie o banco e o usuário da aplicação (troque a senha!):

```bash
sudo mysql
```

```sql
CREATE DATABASE bazar_universitario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bazar_app'@'localhost' IDENTIFIED BY 'uma-senha-forte';
GRANT ALL PRIVILEGES ON bazar_universitario.* TO 'bazar_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> Se você ativou a validação de senha no `mysql_secure_installation`, a senha precisa ter maiúscula, minúscula, número e símbolo (ex.: `Bazar#2026forte`).

## Passo 4 — Publicar o projeto

```bash
cd /var/www/html
sudo rm index.html
sudo git clone https://github.com/SEU-USUARIO/bazar-universitario.git .
```

Crie as tabelas (e, se quiser, os dados de exemplo):

```bash
sudo mysql < /var/www/html/database/schema.sql
sudo mysql < /var/www/html/database/dados-exemplo.sql   # opcional
```

Crie o arquivo de configuração a partir do exemplo e edite:

```bash
sudo cp config/config.example.php config/config.php
sudo nano config/config.php
```

Deixe assim (com a senha que você criou no passo 3):

```php
'app' => [
    'nome'  => 'Bazar Universitário',
    'debug' => false,              // IMPORTANTE: false em produção
],
'db' => [
    'host'    => '127.0.0.1',
    'porta'   => 3306,
    'banco'   => 'bazar_universitario',
    'usuario' => 'bazar_app',
    'senha'   => 'uma-senha-forte',
    'charset' => 'utf8mb4',
],
```

Salve com `Ctrl+O`, `Enter`, e saia com `Ctrl+X`.

Ajuste as permissões (o Apache roda como `www-data` e precisa gravar as fotos em `public/uploads`):

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod 640 /var/www/html/config/config.php
```

### Apontar o Apache para a pasta `public/`

O front controller fica em `public/index.php`, então o `DocumentRoot` precisa apontar para lá, e o `mod_rewrite` precisa estar ativo para as URLs amigáveis (`/itens/5`, `/login`...).

O projeto já traz o arquivo pronto em `deploy/000-default.conf`:

```bash
sudo cp /var/www/html/deploy/000-default.conf /etc/apache2/sites-available/000-default.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

<details>
<summary>Prefere editar à mão? Clique aqui</summary>

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Troque a linha do `DocumentRoot` e adicione o bloco `<Directory>` logo abaixo:

```apache
DocumentRoot /var/www/html/public

<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
    Options -Indexes
</Directory>
```

Depois: `sudo a2enmod rewrite && sudo systemctl restart apache2`.
</details>

## Passo 5 — Testar

Acesse `http://SEU-IP-EXTERNO/` no navegador.

Checklist rápido:

- [ ] A listagem aparece sem login
- [ ] Criar conta e fazer login funcionam
- [ ] Publicar item **com foto** funciona (testa a permissão de `uploads/`)
- [ ] `http://SEU-IP/itens/1` abre o detalhe (testa o mod_rewrite)
- [ ] Botão "Tenho interesse" funciona com outro usuário

### (Opcional) URL amigável com DuckDNS

1. Entre em `duckdns.org` com sua conta Google/GitHub.
2. Crie um subdomínio (ex.: `bazar-nickolas`) e coloque o IP externo da VM.
3. O site passa a abrir em `http://bazar-nickolas.duckdns.org/`.

### Alerta de orçamento

Em **Faturamento → Orçamentos e alertas**, crie um orçamento de R$ 1,00 (ou US$ 1) com alerta por e-mail. Assim você é avisado se algo sair do nível gratuito.

---

## Atualizar o site depois de novos commits

```bash
cd /var/www/html
sudo -u www-data git pull
```

(Se o Git reclamar de "dubious ownership", rode uma vez: `sudo git config --system --add safe.directory /var/www/html`.)

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| Só a página inicial funciona; `/login` dá **404 do Apache** | `mod_rewrite` desligado ou `AllowOverride None` | `sudo a2enmod rewrite`, confira o bloco `<Directory>` e reinicie o Apache |
| Aparece a listagem de arquivos do projeto | `DocumentRoot` ainda aponta para `/var/www/html` | Ajuste para `/var/www/html/public` |
| "Arquivo config/config.php não encontrado" | Faltou copiar o exemplo | `sudo cp config/config.example.php config/config.php` |
| "Erro 500" genérico | Senha do banco errada, extensão faltando etc. | Veja o log: `sudo tail -n 30 /var/log/apache2/error.log` |
| "Não foi possível salvar a foto" | Permissão da pasta de uploads | `sudo chown -R www-data:www-data /var/www/html/public/uploads` |
| Site fora do ar e SSH lento | MySQL sem memória | Crie o swap do passo 2 e `sudo systemctl restart mysql` |
| Acentos aparecem como `Ã§` | Script SQL importado com charset errado | Recrie o banco e rode o `schema.sql` de novo (ele já tem `SET NAMES utf8mb4`) |
