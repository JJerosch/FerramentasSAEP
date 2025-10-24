-- ============================================
-- ENTREGA 3 - Script de Criação e População do Banco de Dados (FINAL)
-- Sistema de Gestão de Estoque (SAEP)
-- ============================================

-- Criar banco de dados (Nome do banco: saep_db)
CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

-- ============================================
-- 1. Tabela: USUARIOS
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- Deve armazenar o hash da senha
    nivel_acesso ENUM('admin', 'estoquista') NOT NULL DEFAULT 'estoquista', -- Adicionado Nível de Acesso
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. Tabela: PRODUTOS
-- ============================================
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50) NOT NULL,
    material VARCHAR(50),
    tamanho DECIMAL(10,2),
    peso DECIMAL(10,2),
    quantidade_estoque INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00, 
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. Tabela: MOVIMENTACOES (Rastreabilidade) - COM VALOR_TOTAL ADICIONADO
-- ============================================
CREATE TABLE IF NOT EXISTS movimentacoes (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00, -- CAMPO VALOR_TOTAL ADICIONADO AQUI!
    data_movimentacao DATE NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- POPULAÇÃO DO BANCO DE DADOS
-- ============================================

-- Senha padrão para todos os usuários: 123456
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES
('Maria Santos', 'maria.santos@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('João Silva', 'joao.silva@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estoquista'),
('Pedro Oliveira', 'pedro.oliveira@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estoquista');

-- Inserir produtos (Valores Unitários adicionados para teste)
INSERT INTO produtos (nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo, valor_unitario) VALUES
('Martelo Carpinteiro', 'Martelo com cabeça de aço e cabo de madeira', 'Martelos', 'Aço/Madeira', 30.00, 0.50, 15, 5, 25.50), 
('Martelo Borracha', 'Martelo com cabeça de borracha para trabalhos delicados', 'Martelos', 'Borracha/Plástico', 25.00, 0.30, 8, 3, 15.00), 
('Martelo Unha', 'Martelo tipo unha para remoção de pregos', 'Martelos', 'Aço/Fibra', 28.00, 0.45, 12, 5, 30.75), 
('Chave Fenda 1/4', 'Chave de fenda com ponta imantada', 'Chaves', 'Aço/Plástico', 15.00, 0.08, 20, 8, 5.90),
('Chave Fenda Isolada', 'Chave de fenda com revestimento isolante 1000V', 'Chaves', 'Aço/Borracha', 18.00, 0.12, 10, 5, 12.50),
('Chave Phillips', 'Chave phillips tamanho médio', 'Chaves', 'Aço/Plástico', 16.00, 0.09, 18, 7, 7.25),
('Alicate Universal', 'Alicate universal 8 polegadas', 'Alicates', 'Aço Carbono', 20.00, 0.25, 14, 6, 45.00),
('Alicate Corte', 'Alicate de corte diagonal', 'Alicates', 'Aço Temperado', 18.00, 0.22, 9, 4, 38.90),
('Trena 5m', 'Trena metálica retrátil 5 metros', 'Medição', 'Aço/Plástico', 5.00, 0.15, 25, 10, 19.99);

-- Inserir movimentações de estoque (com o VALOR_TOTAL calculado e inserido)
INSERT INTO movimentacoes (id_produto, id_usuario, tipo_movimentacao, quantidade, valor_total, data_movimentacao, observacao) VALUES
(1, 1, 'entrada', 10, 255.00, '2025-10-01', 'Compra inicial - Lote A1'),      -- 10 * 25.50
(1, 2, 'entrada', 5, 127.50, '2025-10-05', 'Reposição de estoque - Lote A2'), -- 5 * 25.50
(2, 1, 'entrada', 8, 120.00, '2025-10-02', 'Compra inicial'),                 -- 8 * 15.00
(3, 3, 'entrada', 12, 369.00, '2025-10-03', 'Novo fornecedor'),              -- 12 * 30.75
(4, 1, 'entrada', 20, 118.00, '2025-10-01', 'Estoque inicial'),              -- 20 * 5.90
(5, 2, 'entrada', 10, 125.00, '2025-10-04', 'Compra de segurança'),          -- 10 * 12.50
(6, 1, 'entrada', 18, 130.50, '2025-10-02', 'Reposição'),                    -- 18 * 7.25
(7, 3, 'entrada', 14, 630.00, '2025-10-05', 'Nova remessa'),                 -- 14 * 45.00
(8, 2, 'entrada', 9, 350.10, '2025-10-03', 'Compra programada'),              -- 9 * 38.90
(9, 1, 'entrada', 25, 499.75, '2025-10-01', 'Estoque grande devido à demanda'), -- 25 * 19.99
(4, 2, 'saida', 5, 29.50, '2025-10-10', 'Requisição produção linha 1'),      -- 5 * 5.90
(7, 1, 'saida', 3, 135.00, '2025-10-11', 'Manutenção equipamento'),          -- 3 * 45.00
(9, 3, 'saida', 8, 159.92, '2025-10-12', 'Requisição produção linha 2');      -- 8 * 19.99

-- ============================================
-- Verificar dados inseridos (Opcional)
-- ============================================
SELECT 'Usuários cadastrados:' AS info;
SELECT * FROM usuarios;

SELECT 'Produtos cadastrados:' AS info;
SELECT * FROM produtos;

SELECT 'Movimentações registradas:' AS info;
SELECT m.*, p.nome AS produto, u.nome AS usuario 
FROM movimentacoes m
JOIN produtos p ON m.id_produto = p.id_produto
JOIN usuarios u ON m.id_usuario = u.id_usuario
ORDER BY m.data_movimentacao DESC;