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
    imagem BYTEA DEFAULT NULL,
    CONSTRAINT fk_fornecedores FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores(id)
        ON DELETE SET NULL
);

CREATE TABLE pedido (
  id SERIAL PRIMARY KEY,
  usuario_id    INTEGER NOT NULL REFERENCES usuario(id),
  data_pedido   TIMESTAMP NOT NULL,
  data_entrega  TIMESTAMP,
  situacao      VARCHAR(20) NOT NULL
);

CREATE TABLE item_pedido (
  id           SERIAL PRIMARY KEY,
  pedido_id    INTEGER NOT NULL REFERENCES pedido(id) ON DELETE CASCADE,
  produto_id   INTEGER NOT NULL REFERENCES produto(id),
  quantidade   INTEGER NOT NULL,
  preco        NUMERIC(10,2) NOT NULL
);



-- Inserção do usuário admin padrão COM endereço
-- A senha abaixo é hash de password_hash('admin', PASSWORD_DEFAULT)
-- Inserção do usuário admin com endereço
WITH admin_address AS (
  INSERT INTO endereco (rua, numero, complemento, bairro, cep, cidade, estado)
  VALUES (
    'Casa', '1', 'ComplementoDaCasa', 'CasaBairro',
    '12345-678', 'CasaCaxias', 'RS'
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

-- Inserção de 15 fornecedores (suplementos nacionais e internacionais)
INSERT INTO fornecedores (nome, cnpj) VALUES
('Optimum Nutrition',        '49.210.199/0001-55'),
('Universal Nutrition',      '52.315.463/0001-28'),
('Growth Supplements',       '61.532.714/0001-03'),
('Integralmédica',           '07.317.292/0001-20'),
('Probiótica',               '12.345.678/0001-90'),
('Max Titanium',             '23.456.789/0001-01'),
('Atlhetica Nutrition',      '34.567.890/0001-12'),
('New Millen',               '45.678.901/0001-23'),
('Black Skull',              '56.789.012/0001-34'),
('Dux Nutrition',            '67.890.123/0001-45'),
('Vitafor',                  '78.901.234/0001-56'),
('MuscleTech',               '89.012.345/0001-67'),
('Dymatize',                 '90.123.456/0001-78'),
('BSN',                      '11.234.567/0001-89'),
('Rule 1 Proteins',          '22.345.678/0001-90');

-- Inserção de 15 endereços para os usuários fisiculturistas
INSERT INTO endereco (rua, numero, complemento, bairro, cep, cidade, estado) VALUES
('Rua Muscle 1',     '100', '', 'Centro Fit', '01000-001', 'São Paulo', 'SP'),
('Rua Muscle 2',     '200', '', 'Centro Fit', '01000-002', 'São Paulo', 'SP'),
('Rua Muscle 3',     '300', '', 'Centro Fit', '01000-003', 'São Paulo', 'SP'),
('Rua Muscle 4',     '400', '', 'Centro Fit', '01000-004', 'São Paulo', 'SP'),
('Rua Muscle 5',     '500', '', 'Centro Fit', '01000-005', 'São Paulo', 'SP'),
('Rua Muscle 6',     '600', '', 'Centro Fit', '01000-006', 'São Paulo', 'SP'),
('Rua Muscle 7',     '700', '', 'Centro Fit', '01000-007', 'São Paulo', 'SP'),
('Rua Muscle 8',     '800', '', 'Centro Fit', '01000-008', 'São Paulo', 'SP'),
('Rua Muscle 9',     '900', '', 'Centro Fit', '01000-009', 'São Paulo', 'SP'),
('Rua Muscle 10',   '1000', '', 'Centro Fit', '01000-010', 'São Paulo', 'SP'),
('Rua Muscle 11',   '1100', '', 'Centro Fit', '01000-011', 'São Paulo', 'SP'),
('Rua Muscle 12',   '1200', '', 'Centro Fit', '01000-012', 'São Paulo', 'SP'),
('Rua Muscle 13',   '1300', '', 'Centro Fit', '01000-013', 'São Paulo', 'SP'),
('Rua Muscle 14',   '1400', '', 'Centro Fit', '01000-014', 'São Paulo', 'SP'),
('Rua Muscle 15',   '1500', '', 'Centro Fit', '01000-015', 'São Paulo', 'SP');

-- Inserção de 15 usuários (fisiculturistas famosos)
INSERT INTO usuario (login, senha, nome, tipo, endereco_id) VALUES
('cbum',           '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Chris Bumstead',      'cliente',  2),
('cleberbambam',   '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Cleber Bambam',       'cliente',  3),
('renatocariani',  '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Renato Cariani',      'cliente',  4),
('philheath',      '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Phil Heath',          'cliente',  5),
('ronniecoleman',  '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Ronnie Coleman',      'cliente',  6),
('arnold',         '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Arnold Schwarzenegger','cliente',  7),
('kaigreene',      '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Kai Greene',          'cliente',  8),
('jaycutler',      '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Jay Cutler',          'cliente',  9),
('flexlewis',      '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Flex Lewis',          'cliente', 10),
('branchwarren',   '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Branch Warren',       'cliente', 11),
('williambonac',   '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'William Bonac',       'cliente', 12),
('shanewicks',     '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Shane Wicks',         'cliente', 13),
('dexterjackson',  '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Dexter Jackson',      'cliente', 14),
('richpiana',      '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Rich Piana',          'cliente', 15),
('dorianyates',    '$2y$10$rF/.DAa0xQACrwQnDS2UeO8UYyz37MYsJKOEI9Xs1NcCZnYNRxTzy', 'Dorian Yates',        'cliente', 16);

-- Inserção de 15 produtos (mistura de itens engraçados e anabolizantes famosos)
INSERT INTO produto (nome, preco, quantidade, fornecedor_id) VALUES
  ('Monster Energy',              12.00, 100, 5),  -- Probiótica
  ('Suco de Monstro',             10.00, 100, 5),  -- Probiótica
  ('Água com Músculo',             5.00, 100, 5),  -- Probiótica
  ('Dianabol (Metandrostenolona)',150.00,  50, 2),  -- Universal Nutrition
  ('Oxandrolona 10mg',            180.00,  50, 2),  -- Universal Nutrition
  ('Anadrol (Oximetolona)',       220.00,  50, 3),  -- Growth Supplements
  ('Deca Durabolin (Nandrolona)', 130.00,  50, 3),  -- Growth Supplements
  ('Winstrol (Stanozolol)',       160.00,  50, 4),  -- Integralmédica
  ('Primobolan (Metenolona)',     270.00,  50, 4),  -- Integralmédica
  ('Clenbuterol 40 mcg',          110.00,  50, 6),  -- Max Titanium
  ('Masteron (Drostanolona)',     230.00,  50, 6),  -- Max Titanium
  ('Halotestin (Fluoximesterona)',250.00,  50, 7),  -- Atlhetica Nutrition
  ('Proviron (Mesterolona)',      100.00,  50, 7),  -- Atlhetica Nutrition
  ('Termogênico Turbo',            90.00,  75, 8),  -- New Millen
  ('Suco de Proteína',             15.00, 100, 8);  -- New Millen
