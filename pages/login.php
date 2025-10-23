<?php
/**
 * ENTREGA 4 - Interface de Autenticação de Usuários (Login)
 */
session_start();

// Se já estiver logado, redireciona para dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erro = $_SESSION['erro_login'] ?? '';
unset($_SESSION['erro_login']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão de Estoque</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #1e293b;
            font-size: 1.875rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">🔧</div>
                <h1>Sistema de Estoque</h1>
                <p>Gestão de Ferramentas e Equipamentos</p>
            </div>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <form action="../api/login_process.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        required
                        placeholder="seu.email@empresa.com"
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-control" 
                        required
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Entrar
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 0.813rem;">
                <p><strong>Usuários de teste:</strong></p>
                <p>Email: joao.silva@empresa.com | Senha: 123456</p>
                <p>Email: maria.santos@empresa.com | Senha: 123456</p>
            </div>
        </div>
    </div>
    
    <script>
        // Validação do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;
            
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }
            
            // Validação básica de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, insira um e-mail válido.');
                return false;
            }
        });
    </script>
</body>
</html>