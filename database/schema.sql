CREATE DATABASE IF NOT EXISTS couple_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE couple_finance;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'padrao') NOT NULL DEFAULT 'padrao',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tipos_movimentacao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria ENUM('renda', 'despesa') NOT NULL,
    nome VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tipos_categoria_nome (categoria, nome)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rendas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor_planejado DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_real DECIMAL(12,2) NOT NULL DEFAULT 0,
    data_referencia DATE NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_id INT UNSIGNED NULL,
    renda_fixa_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rendas_usuario_data (usuario_id, data_referencia),
    INDEX idx_rendas_tipo (tipo_id),
    CONSTRAINT fk_rendas_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rendas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS despesas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor_planejado DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_real DECIMAL(12,2) NOT NULL DEFAULT 0,
    data_referencia DATE NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo_id INT UNSIGNED NULL,
    despesa_fixa_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_despesas_usuario_data (usuario_id, data_referencia),
    INDEX idx_despesas_tipo (tipo_id),
    CONSTRAINT fk_despesas_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_despesas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rendas_fixas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor_planejado DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_real DECIMAL(12,2) NOT NULL DEFAULT 0,
    tipo_id INT UNSIGNED NULL,
    dia_referencia TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rendas_fixas_tipo (tipo_id),
    CONSTRAINT fk_rendas_fixas_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rendas_fixas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS despesas_fixas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor_planejado DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_real DECIMAL(12,2) NOT NULL DEFAULT 0,
    tipo_id INT UNSIGNED NULL,
    dia_referencia TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_despesas_fixas_tipo (tipo_id),
    CONSTRAINT fk_despesas_fixas_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_despesas_fixas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Compatibilidade para bancos ja existentes
ALTER TABLE rendas ADD COLUMN IF NOT EXISTS tipo_id INT UNSIGNED NULL AFTER usuario_id;
ALTER TABLE despesas ADD COLUMN IF NOT EXISTS tipo_id INT UNSIGNED NULL AFTER usuario_id;
ALTER TABLE rendas_fixas ADD COLUMN IF NOT EXISTS tipo_id INT UNSIGNED NULL AFTER valor_real;
ALTER TABLE despesas_fixas ADD COLUMN IF NOT EXISTS tipo_id INT UNSIGNED NULL AFTER valor_real;

SET @fk_rendas_tipo_exists := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'rendas'
      AND CONSTRAINT_NAME = 'fk_rendas_tipo'
);
SET @sql := IF(@fk_rendas_tipo_exists = 0,
    'ALTER TABLE rendas ADD CONSTRAINT fk_rendas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_despesas_tipo_exists := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'despesas'
      AND CONSTRAINT_NAME = 'fk_despesas_tipo'
);
SET @sql := IF(@fk_despesas_tipo_exists = 0,
    'ALTER TABLE despesas ADD CONSTRAINT fk_despesas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_rendas_fixas_tipo_exists := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'rendas_fixas'
      AND CONSTRAINT_NAME = 'fk_rendas_fixas_tipo'
);
SET @sql := IF(@fk_rendas_fixas_tipo_exists = 0,
    'ALTER TABLE rendas_fixas ADD CONSTRAINT fk_rendas_fixas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_despesas_fixas_tipo_exists := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'despesas_fixas'
      AND CONSTRAINT_NAME = 'fk_despesas_fixas_tipo'
);
SET @sql := IF(@fk_despesas_fixas_tipo_exists = 0,
    'ALTER TABLE despesas_fixas ADD CONSTRAINT fk_despesas_fixas_tipo FOREIGN KEY (tipo_id) REFERENCES tipos_movimentacao(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Senha padrao: admin123
INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@casal.com', '$2y$12$k4J9rrGs.KAcXy9PXswXO.aI/g5O3qzinCW505cct5Fd3IDdIbVeO', 'admin')
ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role);
