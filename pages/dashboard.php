<?php
/**
 * ENTREGA 5 - Interface Principal do Sistema (Dashboard)
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

// Verifica autenticação
verificarAutenticacao();

// Obtém estatísticas do sistema
$conn = getConnection();

// Total de produtos
$sqlProdutos = "SELECT COUNT(*) as total FROM produtos";
$resultProdutos = $conn->query($sqlProdutos);
$totalProdutos = $resultProdutos->fetch_assoc()['total'];

// Produtos com estoque baixo
$sqlEstoqueBaixo = "SELECT COUNT(*) as total FROM produtos WHERE quantidade_estoque <= estoque_minimo";
$resultEstoqueBaixo = $conn->query($sqlEstoqueBaixo);
$produtosEstoqueBaixo = $resultEstoqueBaixo->fetch_assoc()['total'];

// Total de movimentações (último mês)
$sqlMovimentacoes = "SELECT COUNT(*) as total FROM movimentacoes WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$resultMovimentacoes = $conn->query($sqlMovimentacoes);
$movimentacoesRecentes = $resultMovimentacoes->fetch_assoc()['total'];

// NOVO: Total de Usuários Cadastrados
// Assumindo que você tem uma tabela 'usuarios'
$sqlUsuarios = "SELECT COUNT(*) as total FROM usuarios";
$resultUsuarios = $conn->query($sqlUsuarios);
$totalUsuarios = $resultUsuarios->fetch_assoc()['total'];

// Produtos com estoque baixo (detalhes)
$sqlProdutosBaixos = "SELECT nome, quantidade_estoque, estoque_minimo FROM produtos WHERE quantidade_estoque <= estoque_minimo ORDER BY quantidade_estoque ASC LIMIT 5";
$resultProdutosBaixos = $conn->query($sqlProdutosBaixos);

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão de Estoque</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Sistema de Gestão de Estoque</h1>
            <div class="user-info">
                <span class="user-name"> <?php echo htmlspecialchars(getUsuarioNome()); ?></span>
                <a href="../api/logout.php" class="btn btn-secondary btn-sm">Sair</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h2>Dashboard</h2>
        <p class="text-light mb-3">Bem-vindo ao sistema de gestão de estoque</p>
        
        <div class="stats-grid">
            <div class="stat-card d-flex flex-col align-center text-center">
                <div class="stat-value"><?php echo $totalProdutos; ?></div>
                <div class="stat-label">Total de Produtos</div>
            </div>
            
            <div class="stat-card warning d-flex flex-col align-center text-center">
                <div class="stat-value"><?php echo $produtosEstoqueBaixo; ?></div>
                <div class="stat-label">Produtos com Estoque Baixo</div>
            </div>
            
            <div class="stat-card d-flex flex-col align-center text-center">
                <div class="stat-value"><?php echo $movimentacoesRecentes; ?></div>
                <div class="stat-label">Movimentações (30 dias)</div>
            </div>
        </div>
        
        <?php if ($produtosEstoqueBaixo > 0): ?>
        <div class="card">
            <h3 class="card-title">⚠️ Alertas de Estoque Baixo</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($produto = $resultProdutosBaixos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td><?php echo $produto['quantidade_estoque']; ?></td>
                            <td><?php echo $produto['estoque_minimo']; ?></td>
                            <td>
                                <?php if ($produto['quantidade_estoque'] < $produto['estoque_minimo']): ?>
                                    <span class="badge badge-danger">Crítico</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Atenção</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <h3 class="mt-3 mb-2">Gestão de Itens</h3>
        <div class="nav-grid">
            <a href="produtos.php" class="nav-card">
                <div class="nav-icon"></div>
                <h3>Cadastro de Produtos</h3>
                <p class="text-light-p">Gerenciar produtos do estoque</p>
            </a>
            
            <a href="estoque.php" class="nav-card">
                <div class="nav-icon"></div>
                <h3>Gestão de Estoque</h3>
                <p class="text-light-p">Controlar entradas e saídas</p>
            </a>
        </div>

        <h3 class="mt-3 mb-2">Gestão de Usuários</h3>
        <div class="nav-grid">
            <a href="usuarios.php" class="nav-card">
                <div class="nav-icon" style="color: var(--text-color);"></div>
                <h3>Cadastro de Usuários</h3>
                <p class="text-light-p">Adicionar e gerenciar contas</p>
            </a>
            
            <div class="nav-card" style="cursor: default;">
                <div class="nav-icon" style="color: var(--text-color);"></div>
                <h3>Usuários Ativos</h3>
                <p class="text-light-p">Total de usuários no sistema</p>
                <div style="font-size: 2rem; font-weight: 700; color: var(--text-color); margin-top: 0.5rem;"><?php echo $totalUsuarios; ?></div>
            </div>
        </div>
    </div>
</body>
</html>