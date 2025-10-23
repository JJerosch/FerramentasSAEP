<?php
/**
 * update_hash.php
 * Gera novos hashes bcrypt para usuários de teste e atualiza no DB.
 * Colocar em /api/ e executar uma vez pelo navegador.
 */

require_once '../config/database.php'; // ajuste se o caminho for diferente

$conn = getConnection();

// Lista de usuários a atualizar (email => senha_texto)
$usuarios = [
    'joao.silva@empresa.com'   => '123456',
    'maria.santos@empresa.com' => '123456',
    'pedro.oliveira@empresa.com'=> '123456',
];

$stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");

if (!$stmt) {
    die("Erro ao preparar statement: " . $conn->error);
}

foreach ($usuarios as $email => $senha_plana) {
    $hash = password_hash($senha_plana, PASSWORD_BCRYPT);
    $stmt->bind_param('ss', $hash, $email);
    $ok = $stmt->execute();

    if ($ok) {
        echo "✅ Atualizado: {$email} <br>";
        echo "Hash salvo: {$hash} <br><br>";
    } else {
        echo "❌ Erro ao atualizar {$email}: " . $stmt->error . "<br><br>";
    }
}

$stmt->close();
$conn->close();

echo "<hr><strong>Pronto. Apague este arquivo por segurança.</strong>";
