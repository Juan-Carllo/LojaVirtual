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
        'Casa', '1', 'ComplementoDaCasa', 'CasaBairro',
        '123', 'CasaCaxias', 'RS'
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

-- Inserção do fornecedor inicial
INSERT INTO fornecedores (nome, cnpj)
VALUES ('Farmácia', '00.000.000/0001-00');

-- Inserção de 15 produtos (anabolizantes) com preços médios
INSERT INTO produto (nome, preco, quantidade, fornecedor_id) VALUES
('Oxandrolona 10mg 100 cps',        180.00, 50, 1),
('Dianabol (Metandrostenolona)',   150.00, 50, 1),
('Durateston (Testosterona)',      120.00, 50, 1),
('Deca Durabolin (Nandrolona)',    130.00, 50, 1),
('Stanozolol (Winstrol)',          160.00, 50, 1),
('Primobolan (Metenolona)',        270.00, 50, 1),
('Boldenona 200mg/ml',             140.00, 50, 1),
('Trembolona Acetato',             200.00, 50, 1),
('Masteron (Drostanolona)',        230.00, 50, 1),
('Halotestin (Fluoximesterona)',   250.00, 50, 1),
('Proviron (Mesterolona)',         100.00, 50, 1),
('Anadrol (Oximetolona)',          220.00, 50, 1),
('Clenbuterol 40mcg 100 cps',      110.00, 50, 1),
('Enantato de Testosterona',       125.00, 50, 1),
('Sustanon 250',                   150.00, 50, 1);
