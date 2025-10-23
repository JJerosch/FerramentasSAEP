<?php
$senha_digitada = '123456';
$hash_no_banco = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($senha_digitada, $hash_no_banco)) {
    echo "✅ Senha correta (password_verify OK)";
} else {
    echo "❌ Senha incorreta (password_verify FALHOU)";
}
