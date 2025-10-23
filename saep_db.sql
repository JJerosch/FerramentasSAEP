-- ============================================
-- Script de Criação do Banco de Dados
-- Sistema de Gestão de Estoque
-- ============================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;

-- ============================================
-- Tabela: USUARIOS
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabela: PRODUTOS
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
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabela: MOVIMENTACOES
-- ============================================
CREATE TABLE IF NOT EXISTS movimentacoes (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- POPULAÇÃO DO BANCO DE DADOS
-- ============================================

-- Inserir usuários (senha: 123456 - em produção usar password_hash)
INSERT INTO usuarios (nome, email, senha) VALUES
('João Silva', 'joao.silva@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Maria Santos', 'maria.santos@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Pedro Oliveira', 'pedro.oliveira@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir produtos (ferramentas)
INSERT INTO produtos (nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo) VALUES
('Martelo Carpinteiro', 'Martelo com cabeça de aço e cabo de madeira', 'Martelos', 'Aço/Madeira', 30.00, 0.50, 15, 5),
('Martelo Borracha', 'Martelo com cabeça de borracha para trabalhos delicados', 'Martelos', 'Borracha/Plástico', 25.00, 0.30, 8, 3),
('Martelo Unha', 'Martelo tipo unha para remoção de pregos', 'Martelos', 'Aço/Fibra', 28.00, 0.45, 12, 5),
('Chave Fenda 1/4', 'Chave de fenda com ponta imantada', 'Chaves', 'Aço/Plástico', 15.00, 0.08, 20, 8),
('Chave Fenda Isolada', 'Chave de fenda com revestimento isolante 1000V', 'Chaves', 'Aço/Borracha', 18.00, 0.12, 10, 5),
('Chave Phillips', 'Chave phillips tamanho médio', 'Chaves', 'Aço/Plástico', 16.00, 0.09, 18, 7),
('Alicate Universal', 'Alicate universal 8 polegadas', 'Alicates', 'Aço Carbono', 20.00, 0.25, 14, 6),
('Alicate Corte', 'Alicate de corte diagonal', 'Alicates', 'Aço Temperado', 18.00, 0.22, 9, 4),
('Trena 5m', 'Trena metálica retrátil 5 metros', 'Medição', 'Aço/Plástico', 5.00, 0.15, 25, 10);

-- Inserir movimentações de estoque
INSERT INTO movimentacoes (id_produto, id_usuario, tipo_movimentacao, quantidade, data_movimentacao, observacao) VALUES
(1, 1, 'entrada', 10, '2025-10-01', 'Compra inicial de estoque'),
(1, 2, 'entrada', 5, '2025-10-05', 'Reposição de estoque'),
(2, 1, 'entrada', 8, '2025-10-02', 'Compra inicial'),
(3, 3, 'entrada', 12, '2025-10-03', 'Novo fornecedor'),
(4, 1, 'entrada', 20, '2025-10-01', 'Estoque inicial'),
(5, 2, 'entrada', 10, '2025-10-04', 'Compra de segurança'),
(6, 1, 'entrada', 18, '2025-10-02', 'Reposição'),
(7, 3, 'entrada', 14, '2025-10-05', 'Nova remessa'),
(8, 2, 'entrada', 9, '2025-10-03', 'Compra programada'),
(9, 1, 'entrada', 25, '2025-10-01', 'Estoque grande devido à demanda'),
(4, 2, 'saida', 5, '2025-10-10', 'Requisição produção linha 1'),
(7, 1, 'saida', 3, '2025-10-11', 'Manutenção equipamento'),
(9, 3, 'saida', 8, '2025-10-12', 'Requisição produção linha 2');

-- ============================================
-- Verificar dados inseridos
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