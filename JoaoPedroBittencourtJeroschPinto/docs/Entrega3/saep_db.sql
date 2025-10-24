-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/10/2025 às 05:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `saep_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id_movimentacao` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida') NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_movimentacao` date NOT NULL,
  `data_registro` datetime DEFAULT current_timestamp(),
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id_movimentacao`, `id_produto`, `id_usuario`, `tipo_movimentacao`, `quantidade`, `valor_total`, `data_movimentacao`, `data_registro`, `observacao`) VALUES
(1, 1, 1, 'entrada', 10, 0.00, '2025-10-01', '2025-10-24 00:10:37', 'Compra inicial - Lote A1'),
(2, 1, 2, 'entrada', 5, 0.00, '2025-10-05', '2025-10-24 00:10:37', 'Reposição de estoque - Lote A2'),
(3, 2, 1, 'entrada', 8, 0.00, '2025-10-02', '2025-10-24 00:10:37', 'Compra inicial'),
(5, 4, 1, 'entrada', 20, 0.00, '2025-10-01', '2025-10-24 00:10:37', 'Estoque inicial'),
(6, 5, 2, 'entrada', 10, 0.00, '2025-10-04', '2025-10-24 00:10:37', 'Compra de segurança'),
(7, 6, 1, 'entrada', 18, 0.00, '2025-10-02', '2025-10-24 00:10:37', 'Reposição'),
(9, 8, 2, 'entrada', 9, 0.00, '2025-10-03', '2025-10-24 00:10:37', 'Compra programada'),
(10, 9, 1, 'entrada', 25, 0.00, '2025-10-01', '2025-10-24 00:10:37', 'Estoque grande devido à demanda'),
(11, 4, 2, 'saida', 5, 0.00, '2025-10-10', '2025-10-24 00:10:37', 'Requisição produção linha 1'),
(12, 7, 1, 'saida', 3, 0.00, '2025-10-11', '2025-10-24 00:10:37', 'Manutenção equipamento'),
(14, 8, 1, 'entrada', 10, 389.00, '2025-10-24', '2025-10-24 00:34:15', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id_produto` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(50) NOT NULL,
  `material` varchar(50) DEFAULT NULL,
  `tamanho` decimal(10,2) DEFAULT NULL,
  `peso` decimal(10,2) DEFAULT NULL,
  `quantidade_estoque` int(11) NOT NULL DEFAULT 0,
  `estoque_minimo` int(11) NOT NULL DEFAULT 5,
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id_produto`, `nome`, `descricao`, `categoria`, `material`, `tamanho`, `peso`, `quantidade_estoque`, `estoque_minimo`, `valor_unitario`, `data_cadastro`) VALUES
(1, 'Martelo Carpinteiro', 'Martelo com cabeça de aço e cabo de madeira', 'Martelos', 'Aço/Madeira', 30.00, 0.50, 15, 5, 25.50, '2025-10-24 00:10:37'),
(2, 'Martelo Borracha', 'Martelo com cabeça de borracha para trabalhos delicados', 'Martelos', 'Borracha/Plástico', 25.00, 0.30, 8, 3, 15.00, '2025-10-24 00:10:37'),
(3, 'Martelo Unha', 'Martelo tipo unha para remoção de pregos', 'Martelos', 'Aço/Fibra', 28.00, 0.45, 12, 5, 30.75, '2025-10-24 00:10:37'),
(4, 'Chave Fenda 1/4', 'Chave de fenda com ponta imantada', 'Chaves', 'Aço/Plástico', 15.00, 0.08, 20, 8, 5.90, '2025-10-24 00:10:37'),
(5, 'Chave Fenda Isolada', 'Chave de fenda com revestimento isolante 1000V', 'Chaves', 'Aço/Borracha', 18.00, 0.12, 10, 5, 12.50, '2025-10-24 00:10:37'),
(6, 'Chave Phillips', 'Chave phillips tamanho médio', 'Chaves', 'Aço/Plástico', 16.00, 0.09, 18, 7, 7.25, '2025-10-24 00:10:37'),
(7, 'Alicate Universal', 'Alicate universal 8 polegadas', 'Alicates', 'Aço Carbono', 20.00, 0.25, 14, 6, 45.00, '2025-10-24 00:10:37'),
(8, 'Alicate Corte', 'Alicate de corte diagonal', 'Alicates', 'Aço Temperado', 18.00, 0.22, 19, 4, 38.90, '2025-10-24 00:10:37'),
(9, 'Trena 5m', 'Trena metálica retrátil 5 metros', 'Medição', 'Aço/Plástico', 5.00, 0.15, 25, 10, 19.99, '2025-10-24 00:10:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','estoquista') NOT NULL DEFAULT 'estoquista',
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha`, `nivel_acesso`, `data_criacao`) VALUES
(1, 'Maria Santos', 'maria.santos@empresa.com', '$2y$10$cSzSUFXi2IIXfwgWCWOq8.HKcI1xxGnRBpHVII0L275xZy3x3gdMi', 'admin', '2025-10-24 00:10:37'),
(2, 'João Silva', 'joao.silva@empresa.com', '$2y$10$f8xlr0W14Ix6B/q6lYPkz.zBXRQrLMFDVdo.51DiUfecg7FU1gcHy', 'estoquista', '2025-10-24 00:10:37');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id_movimentacao`),
  ADD KEY `id_produto` (`id_produto`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id_produto`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id_produto`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
