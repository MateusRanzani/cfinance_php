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

CREATE TABLE IF NOT EXISTS budgets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    year SMALLINT UNSIGNED NOT NULL,
    month TINYINT UNSIGNED NOT NULL,
    planned_income DECIMAL(12,2) NOT NULL DEFAULT 0,
    planned_expense DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_budgets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget_per_user_month (user_id, year, month)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('renda', 'despesa') NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_entries_budget FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE,
    CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Senha padrão: admin123
INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@casal.com', '$2y$10$w6sct0e7arD0l3H7fygWm.xIKCE8uW2Mqd7qV3CyMfCVxYjBy06Sn', 'admin')
ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role);
