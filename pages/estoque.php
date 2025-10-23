<?php
/**
 * ENTREGA 7 - Interface de Gestão de Estoque
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

// Verifica autenticação
verificarAutenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque - Sistema de Gestão de Estoque</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Cabeçalho -->
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
        <div class="d-flex justify-between align-center mb-3">
            <h2>Gestão de Estoque</h2>
            <a href="dashboard.php" class="btn btn-secondary">← Voltar ao Dashboard</a>
        </div>
        
        <!-- Mensagens de Feedback -->
        <div id="mensagemFeedback"></div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Lista de Produtos -->
            <div class="card">
                <h3 class="card-title">Produtos (ordem alfabética)</h3>
                <div id="listaProdutos">
                    <p class="text-center" style="color: #64748b;">Carregando produtos...</p>
                </div>
            </div>
            
            <!-- Formulário de Movimentação -->
            <div>
                <div class="card">
                    <h3 class="card-title">Registrar Movimentação</h3>
                    <p style="color: #64748b; margin-bottom: 1rem;">Selecione um produto na lista ao lado</p>
                    
                    <div id="formMovimentacao" class="form-movimentacao">
                        <form id="formMov">
                            <input type="hidden" id="produtoSelecionadoId" name="id_produto">
                            
                            <div class="form-group">
                                <label class="form-label">Produto Selecionado</label>
                                <div style="padding: 0.75rem; background: white; border-radius: 0.375rem; font-weight: 600;">
                                    <span id="produtoSelecionadoNome"></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Tipo de Movimentação *</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="tipoEntrada" name="tipo_movimentacao" value="entrada" required>
                                        <label for="tipoEntrada">
                                            <div style="font-size: 2rem; margin-bottom: 0.5rem;"></div>
                                            <div>Entrada</div>
                                        </label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="tipoSaida" name="tipo_movimentacao" value="saida" required>
                                        <label for="tipoSaida">
                                            <div style="font-size: 2rem; margin-bottom: 0.5rem;"></div>
                                            <div>Saída</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantidade" class="form-label">Quantidade *</label>
                                <input type="number" id="quantidade" name="quantidade" class="form-control" required min="1">
                            </div>
                            
                            <div class="form-group">
                                <label for="data_movimentacao" class="form-label">Data da Movimentação *</label>
                                <input type="date" id="data_movimentacao" name="data_movimentacao" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="observacao" class="form-label">Observação</label>
                                <textarea id="observacao" name="observacao" class="form-control" rows="3" placeholder="Observações sobre a movimentação..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Registrar Movimentação</button>
                        </form>
                    </div>
                </div>
                
                <!-- Informações do Produto Selecionado -->
                <div id="infoProduto" class="card" style="display: none; margin-top: 1.5rem;">
                    <h3 class="card-title">Informações do Produto</h3>
                    <div id="infoConteudo"></div>
                </div>
            </div>
        </div>
        
        <!-- Histórico de Movimentações -->
        <div class="card" style="margin-top: 1.5rem;">
            <h3 class="card-title">Histórico de Movimentações Recentes</h3>
            <div id="historicoMovimentacoes">
                <p class="text-center" style="color: #64748b;">Nenhuma movimentação registrada</p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/estoque.js"></script>
</body>
</html>