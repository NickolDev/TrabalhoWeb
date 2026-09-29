-- =====================================================================
-- Bazar Universitário — criação do banco e das tabelas
-- Baseado no "Modelo de dados sugerido" (seção 5 do enunciado), com
-- três ajustes para os requisitos bônus e integridade:
--   1. itens.foto                  -> guarda o nome da foto do item (bônus)
--   2. UNIQUE (item_id, usuario_id) -> o mesmo usuário não registra interesse 2x
--   3. ON DELETE CASCADE            -> remover um item remove seus interesses
--
-- E duas tabelas de apoio, necessárias para rodar na Vercel
-- (lá o disco é somente leitura e cada acesso pode cair num servidor diferente):
--   - fotos   -> o conteúdo das imagens enviadas
--   - sessoes -> as sessões de login do PHP
-- =====================================================================

-- Garante que os acentos sejam gravados corretamente
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS bazar_universitario
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bazar_universitario;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT,
    tipo ENUM('doacao', 'troca') NOT NULL,
    status ENUM('disponivel', 'concluido') DEFAULT 'disponivel',
    foto VARCHAR(255) NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_itens_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS interesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    usuario_id INT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES itens(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_interesse_item_usuario (item_id, usuario_id)
) ENGINE=InnoDB;

-- Fotos dos itens (bônus). O nome é o mesmo guardado em itens.foto.
CREATE TABLE IF NOT EXISTS fotos (
    nome VARCHAR(64) PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL,
    dados MEDIUMBLOB NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sessões de login (usadas pela classe Core/SessaoNoBanco)
CREATE TABLE IF NOT EXISTS sessoes (
    id VARCHAR(128) PRIMARY KEY,
    dados MEDIUMBLOB NOT NULL,
    atualizado_em INT UNSIGNED NOT NULL,
    INDEX idx_sessoes_atualizado (atualizado_em)
) ENGINE=InnoDB;

-- Categorias iniciais (INSERT IGNORE: rodar o script de novo não duplica)
INSERT IGNORE INTO categorias (nome) VALUES
    ('Livros'),
    ('Eletrônicos'),
    ('Material de Estudo'),
    ('Roupas'),
    ('Móveis'),
    ('Outros');
