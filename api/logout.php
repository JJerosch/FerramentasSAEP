<?php
/**
 * Processamento de Logout
 * Destrói a sessão e redireciona para login
 */

session_start();
session_unset();
session_destroy();

// Redireciona para a página de login
header('Location: ../pages/login.php');
exit();
?>