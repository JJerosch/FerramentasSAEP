<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';

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

$conn = getConnection();

// CORREÇÃO AQUI: Adicionando 'nivel_acesso' à consulta SQL
$sql = "SELECT id_usuario, nome, email, senha, nivel_acesso FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    $stmt->close();
    $conn->close();
    header('Location: ../pages/login.php');
    exit();
}

$usuario = $result->fetch_assoc();

$senhaBanco = trim($usuario['senha']);
// NOTA: Mantenho a verificação extra para senhas que não são hash, mas é recomendado usar apenas password_verify
$senhaValida = password_verify($senha, $senhaBanco) || $senha === $senhaBanco;


if (!$senhaValida) {
    $_SESSION['erro_login'] = 'E-mail ou senha incorretos.';
    $stmt->close();
    $conn->close();
    header('Location: ../pages/login.php');
    exit();
}

// CORREÇÃO AQUI: Salvando 'nivel_acesso' na sessão
$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_nivel_acesso'] = $usuario['nivel_acesso'];
$_SESSION['login_time'] = time();

$stmt->close();
$conn->close();

header('Location: ../pages/dashboard.php');
exit();
?>