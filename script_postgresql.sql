-- Criação da tabela 'usuario'
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) NOT NULL DEFAULT 'cliente'  -- Pode ser 'admin' ou 'cliente'
);

-- Criação da tabela 'fornecedor'
CREATE TABLE fornecedor (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(20) UNIQUE NOT NULL
);

-- Criação da tabela 'produto'
CREATE TABLE produto (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco NUMERIC(10,2) NOT NULL,
    quantidade INTEGER NOT NULL,
    fornecedor_id INTEGER,
    CONSTRAINT fk_fornecedor FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedor(id)
        ON DELETE SET NULL
);

-- Inserção de usuário admin padrão (login: admin | senha: admin)
-- A senha abaixo está hasheada com password_hash('admin', PASSWORD_DEFAULT)
INSERT INTO usuario (login, senha, nome, tipo)
VALUES (
    'admin',
    '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy',
    'Administrador',
    'admin'
);
