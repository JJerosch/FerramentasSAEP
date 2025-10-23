<?php
/**
 * ENTREGA 6 - Interface de Cadastro de Produto
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
    <title>Cadastro de Produtos - Sistema de Gestão de Estoque</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <div class="d-flex justify-between align-center mb-3">
            <h2>Cadastro de Produtos</h2>
            <a href="dashboard.php" class="btn btn-secondary">← Voltar ao Dashboard</a>
        </div>
        
        <!-- Mensagens de Feedback -->
        <div id="mensagemFeedback"></div>
        
        <!-- Formulário de Cadastro/Edição -->
        <div class="card">
            <h3 class="card-title" id="tituloFormulario">Novo Produto</h3>
            <form id="formProduto">
                <input type="hidden" id="idProduto" name="id_produto">
                <input type="hidden" id="acaoFormulario" value="criar">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria" class="form-label">Categoria *</label>
                        <select id="categoria" name="categoria" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="Martelos">Martelos</option>
                            <option value="Chaves">Chaves</option>
                            <option value="Alicates">Alicates</option>
                            <option value="Medição">Medição</option>
                            <option value="Outros">Outros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="material" class="form-label">Material</label>
                        <input type="text" id="material" name="material" class="form-control" placeholder="Ex: Aço/Madeira">
                    </div>
                    
                    <div class="form-group">
                        <label for="tamanho" class="form-label">Tamanho (cm)</label>
                        <input type="number" id="tamanho" name="tamanho" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="peso" class="form-label">Peso (kg)</label>
                        <input type="number" id="peso" name="peso" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantidade_estoque" class="form-label">Quantidade em Estoque *</label>
                        <input type="number" id="quantidade_estoque" name="quantidade_estoque" class="form-control" required min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="estoque_minimo" class="form-label">Estoque Mínimo *</label>
                        <input type="number" id="estoque_minimo" name="estoque_minimo" class="form-control" required min="1" value="5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3" placeholder="Descrição detalhada do produto"></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Salvar Produto</button>
                    <button type="button" class="btn btn-secondary" onclick="limparFormulario()">Cancelar</button>
                </div>
            </form>
        </div>
        
        <!-- Busca e Listagem -->
        <div class="card">
            <div class="d-flex justify-between align-center mb-2">
                <h3 class="card-title">Produtos Cadastrados</h3>
                <div style="width: 300px;">
                    <input type="text" id="campoBusca" class="form-control" placeholder="🔍 Buscar produto...">
                </div>
            </div>
            
            <div class="table-container">
                <table id="tabelaProdutos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Material</th>
                            <th>Estoque</th>
                            <th>Estoque Mín.</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoProdutos">
                        <tr>
                            <td colspan="8" class="text-center">Carregando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/produtos.js"></script>
</body>
</html>