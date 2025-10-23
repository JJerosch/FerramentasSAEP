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
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .stat-card.warning {
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <div class="header-content">
            <h1>🔧 Sistema de Gestão de Estoque</h1>
            <div class="user-info">
                <span class="user-name">👤 <?php echo htmlspecialchars(getUsuarioNome()); ?></span>
                <a href="../api/logout.php" class="btn btn-secondary btn-sm">Sair</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h2>Dashboard</h2>
        <p class="text-light mb-3">Bem-vindo ao sistema de gestão de estoque</p>
        
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?php echo $totalProdutos; ?></div>
                <div class="stat-label">Total de Produtos</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?php echo $produtosEstoqueBaixo; ?></div>
                <div class="stat-label">Produtos com Estoque Baixo</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo $movimentacoesRecentes; ?></div>
                <div class="stat-label">Movimentações (30 dias)</div>
            </div>
        </div>
        
        <!-- Alertas de Estoque Baixo -->
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
        
        <!-- Menu de Navegação -->
        <h3 class="mt-3 mb-2">Acesso Rápido</h3>
        <div class="nav-grid">
            <a href="produtos.php" class="nav-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                <h3>Cadastro de Produtos</h3>
                <p style="color: #64748b; font-size: 0.875rem;">Gerenciar produtos do estoque</p>
            </a>
            
            <a href="estoque.php" class="nav-card">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                <h3>Gestão de Estoque</h3>
                <p style="color: #64748b; font-size: 0.875rem;">Controlar entradas e saídas</p>
            </a>
        </div>
    </div>
</body>
</html>