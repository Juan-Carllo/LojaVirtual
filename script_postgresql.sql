-- Criação da tabela 'usuario'
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL
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
    CONSTRAINT fk_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedor(id) ON DELETE SET NULL
);

-- Inserção de usuário admin padrão (login: admin | senha: admin)
INSERT INTO usuario (login, senha, nome)
VALUES (
    'admin',
    '$2y$10$hY5W3NHoM7eUwukUxGLiGuws8T97GVKpNR1pXucXz9A9aBvm1Gcj6',
    'Administrador'
);



