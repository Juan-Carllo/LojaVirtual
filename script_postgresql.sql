-- Criação da tabela 'endereco'
CREATE TABLE endereco (
    id SERIAL PRIMARY KEY,
    rua VARCHAR(255) NOT NULL,
    numero VARCHAR(50) NOT NULL,
    complemento VARCHAR(255),
    bairro VARCHAR(255) NOT NULL,
    cep VARCHAR(20) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL
);

-- Criação da tabela 'usuario' com referência a endereço
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo VARCHAR(20) NOT NULL DEFAULT 'cliente',  -- 'admin' ou 'cliente'
    endereco_id INTEGER,
    CONSTRAINT fk_endereco FOREIGN KEY (endereco_id)
        REFERENCES endereco(id)
        ON DELETE SET NULL
);

-- Criação da tabela 'fornecedor'
CREATE TABLE fornecedores (
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
    CONSTRAINT fk_fornecedores FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores(id)
        ON DELETE SET NULL
);

-- Inserção do usuário admin padrão COM endereço
-- A senha abaixo é hash de password_hash('admin', PASSWORD_DEFAULT)
WITH admin_address AS (
    INSERT INTO endereco (rua, numero, complemento, bairro, cep, cidade, estado)
    VALUES (
        'Casa',      -- rua (você pode trocar por um valor real)
        '1',      -- número
        'ComplementoDaCasa',      -- complemento
        'CasaBairro',      -- bairro
        '123',      -- CEP
        'CasaCaxias',      -- cidade
        'RS'       -- estado
    )
    RETURNING id
)
INSERT INTO usuario (login, senha, nome, tipo, endereco_id)
SELECT
    'admin',
    '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy',
    'Administrador',
    'admin',
    id
FROM admin_address;
