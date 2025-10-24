<?php
/**
 * Interface de Cadastro e Gestão de Usuários
 */

require_once '../includes/auth.php';
require_once '../config/database.php';

// 1. Verifica autenticação (todos devem estar logados)
verificarAutenticacao();

// 2. RESTRICÃO DE ACESSO: Se o usuário NÃO for 'admin', ele é redirecionado.
if (getNivelAcesso() !== 'admin') {
    // Redireciona de volta para a dashboard
    header('Location: dashboard.php'); 
    exit();
}

// O restante do código PHP e HTML só será executado para admins

// ... (Restante do seu código PHP, caso haja)
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários - Sistema de Gestão de Estoque</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    </head>
<body>
<div class="header">
        <div class="header-content">
            <h1>Sistema de Gestão de Estoque</h1>
            <div class="user-info">
                <div class="user-details">
                    <span class="user-name"> <?php echo htmlspecialchars(getUsuarioNome()); ?> : </span>
                    <span class="user-role"> <?php echo strtoupper(htmlspecialchars(getNivelAcesso())); ?></span>
                </div>
                <a href="../api/logout.php" class="btn btn-secondary btn-sm">Sair</a>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="d-flex justify-between align-center mb-3">
            <h2>Cadastro de Usuários</h2>
            <a href="dashboard.php" class="btn btn-secondary">← Voltar ao Dashboard</a>
        </div>
        
        <div id="mensagemFeedback"></div>
        
        <div class="card">
    <h3 class="card-title" id="tituloFormulario">Novo Usuário</h3>
    <form id="formUsuario">
        <input type="hidden" id="idUsuario" name="id_usuario">
        <input type="hidden" id="acaoFormulario" value="criar">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            
            <div class="form-group">
                <label for="nome" class="form-label">Nome Completo *</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">E-mail *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group" id="senha-field-group">
                <label for="senha" class="form-label">Senha *</label>
                <input type="password" id="senha" name="senha" class="form-control" required placeholder="Mínimo 6 caracteres (necessário apenas para novo cadastro)">
            </div>

            <div class="form-group" id="confirmar-senha-field-group">
                <label for="confirmar_senha" class="form-label">Confirmar Senha *</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="nivel_acesso" class="form-label">Nível de Acesso *</label>
                <select id="nivel_acesso" name="nivel_acesso" class="form-control" required>
                    <option value="admin">Administrador</option>
                    <option value="estoquista" selected>Estoquista</option>
                </select>
            </div>
        </div>
                
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                    <button type="button" class="btn btn-secondary" onclick="limparFormulario()">Cancelar</button>
                </div>
            </form>
        </div>
        
        <div class="card mt-3">
            <div class="d-flex justify-between align-center mb-2">
                <h3 class="card-title">Usuários Cadastrados</h3>
                <div style="width: 300px;">
                    <input type="text" id="campoBusca" class="form-control" placeholder="Buscar usuário...">
                </div>
            </div>
            
            <div class="table-container">
                <table id="tabelaUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Nível de Acesso</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="corpoUsuarios">
                        <tr>
                            <td colspan="6" class="text-center">Carregando usuários...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/usuarios.js"></script>
</body>
</html>