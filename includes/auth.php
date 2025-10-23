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
 * @param bool $sair Se true, o script é encerrado (útil para APIs)
 * Redireciona para login se não estiver
 */
function verificarAutenticacao($sair = false) {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nome'])) {
        if ($sair) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado. Faça login.']);
            exit();
        } else {
            // Se estiver em um subdiretório, o caminho pode mudar. 
            // Usamos caminho relativo, mas 'login.php' deve estar na raiz.
            header('Location: login.php'); 
            exit();
        }
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
 * Retorna o nível de acesso do usuário logado (admin, estoquista)
 * ESTA É A NOVA FUNÇÃO NECESSÁRIA PARA O DASHBOARD
 * @return string|null
 */
function getNivelAcesso() {
    return $_SESSION['usuario_nivel_acesso'] ?? null;
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