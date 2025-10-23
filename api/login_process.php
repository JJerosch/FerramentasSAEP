<?php
/**
 * Processamento de Autenticação
 * Valida credenciais e cria sessão
 */

session_start();
require_once '../config/database.php';

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit();
}

// Recebe e sanitiza os dados
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';

// Validações básicas
if (empty($email) || empty($senha)) {
    $_SESSION['erro_login'] = 'Por favor, preencha todos os campos.';
    header('Location: ../pages/login.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_login'] = 'E-mail inválido.';
    header('Location: ../pages/login.php');
    exit();
}

// Conecta ao banco de dados
$conn = getConnection();

// Busca o usuário pelo email
$sql = "SELECT id_usuario, nome, email, senha FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se o usuário existe
if ($result->num_rows === 0) {
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    $stmt->close();
    $conn->close();
    header('Location: ../pages/login.php');
    exit();
}

$usuario = $result->fetch_assoc();

// Verifica a senha
// Nota: Para senhas de teste simples, comparamos diretamente
// Em produção, use password_verify() com password_hash()
$senhaValida = false;

// Tenta verificar com password_verify (hash bcrypt)
if (password_verify($senha, $usuario['senha'])) {
    $senhaValida = true;
} 
// Fallback para senhas em texto simples (apenas para ambiente de teste)
elseif ($senha === $usuario['senha']) {
    $senhaValida = true;
}

if (!$senhaValida) {
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    $stmt->close();
    $conn->close();
    header('Location: ../pages/login.php');
    exit();
}

// Login bem-sucedido - cria a sessão
$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['login_time'] = time();

// Fecha conexões
$stmt->close();
$conn->close();

// Redireciona para o dashboard
header('Location: ../pages/dashboard.php');
exit();
?>