<?php
/**
 * Sistema de Autenticação
 * Verifica se o usuário está logado
 */

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está autenticado
 * Redireciona para login se não estiver
 */
function verificarAutenticacao() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nome'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Retorna o ID do usuário logado
 * @return int|null
 */
function getUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna o nome do usuário logado
 * @return string|null
 */
function getUsuarioNome() {
    return $_SESSION['usuario_nome'] ?? null;
}

/**
 * Retorna o email do usuário logado
 * @return string|null
 */
function getUsuarioEmail() {
    return $_SESSION['usuario_email'] ?? null;
}

/**
 * Verifica se o usuário está logado
 * @return bool
 */
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Realiza logout do usuário
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>