<?php
/**
 * api/usuarios_api.php
 * API para operações CRUD de Usuários (CORRIGIDO PARA O BANCO)
 */

header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../config/database.php';

// Apenas usuários autenticados devem acessar a API
// NOTA: Em um ambiente de produção, esta API deve ser restrita apenas a administradores.
verificarAutenticacao(true); 

/**
 * Função utilitária para enviar resposta JSON
 */
function send_response($success, $data = null, $message = null) {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit();
}

/**
 * Função para limpar e validar dados (evitar XSS/SQL Injection)
 * @param mysqli $conn Conexão com o banco de dados
 * @param string $data Dados a serem limpos
 * @return string Dados limpos
 */
function sanitize($conn, $data) {
    if (is_array($data)) {
        // Correção para trabalhar com dados de formulário PUT (que são strings de query)
        $sanitized_array = [];
        foreach ($data as $key => $value) {
            $sanitized_array[$key] = mysqli_real_escape_string($conn, trim($value));
        }
        return $sanitized_array;
    }
    return mysqli_real_escape_string($conn, trim($data));
}

// Obtém o método da requisição e o corpo
$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();
$action = $_GET['action'] ?? '';

// =======================================================
// ROTEAMENTO DA API
// =======================================================

switch ($method) {
    case 'GET':
        if ($action === 'read') {
            // CORREÇÃO: Usando 'id_usuario' com ALIAS 'id' e 'data_criacao' com ALIAS 'data_cadastro'
            $busca = isset($_GET['busca']) ? sanitize($conn, $_GET['busca']) : '';
            $sql = "SELECT 
                        id_usuario AS id, 
                        nome, 
                        email, 
                        nivel_acesso, 
                        data_criacao AS data_cadastro 
                    FROM usuarios";
            
            if (!empty($busca)) {
                $sql .= " WHERE nome LIKE '%$busca%' OR email LIKE '%$busca%'";
            }
            $sql .= " ORDER BY nome ASC";

            $result = $conn->query($sql);
            if ($result) {
                $usuarios = [];
                while ($row = $result->fetch_assoc()) {
                    $usuarios[] = $row;
                }
                send_response(true, $usuarios);
            } else {
                send_response(false, null, "Erro na consulta: " . $conn->error);
            }
        } elseif ($action === 'get_single' && isset($_GET['id'])) {
            // CORREÇÃO: Usando 'id_usuario'
            $id = (int)$_GET['id'];
            $sql = "SELECT id_usuario AS id, nome, email, nivel_acesso FROM usuarios WHERE id_usuario = $id";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                // CORREÇÃO: Pega o resultado
                $data = $result->fetch_assoc();
                // O front espera 'id', mas o banco retorna 'id_usuario', o alias resolve.
                send_response(true, $data);
            } else {
                send_response(false, null, "Usuário não encontrado.");
            }
        }
        break;

    case 'POST':
        // CREATE
        $data = sanitize($conn, $_POST);

        if (empty($data['nome']) || empty($data['email']) || empty($data['senha']) || empty($data['nivel_acesso'])) {
            send_response(false, null, 'Todos os campos obrigatórios devem ser preenchidos.');
        }

        // 1. Verificar se o e-mail já existe
        $check_email = $conn->query("SELECT id_usuario FROM usuarios WHERE email = '{$data['email']}'");
        if ($check_email->num_rows > 0) {
            send_response(false, null, 'O e-mail informado já está cadastrado.');
        }

        // 2. Hash da senha
        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        // 3. Inserção no banco
        // CORREÇÃO: Usando 'data_criacao'
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, data_criacao) 
                VALUES (
                    '{$data['nome']}', 
                    '{$data['email']}', 
                    '$senha_hash', 
                    '{$data['nivel_acesso']}', 
                    NOW()
                )";

        if ($conn->query($sql)) {
            // CORREÇÃO: Não tem como garantir que o insert_id seja o id_usuario se o nome não bater
            // Mas o PHP geralmente pega o ID da chave primária gerada
            send_response(true, ['id' => $conn->insert_id], 'Usuário cadastrado com sucesso.');
        } else {
            send_response(false, null, 'Erro ao cadastrar usuário: ' . $conn->error);
        }
        break;

    case 'PUT':
        // UPDATE
        parse_str(file_get_contents("php://input"), $_PUT);
        
        // CORREÇÃO: Estamos usando o ID real do banco (id_usuario) na cláusula WHERE
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
             send_response(false, null, 'ID do usuário não fornecido.');
        }

        $data = sanitize($conn, $_PUT);

        if (empty($data['nome']) || empty($data['email']) || empty($data['nivel_acesso'])) {
            send_response(false, null, 'Todos os campos obrigatórios devem ser preenchidos.');
        }
        
        $set_parts = [
            "nome = '{$data['nome']}'",
            "email = '{$data['email']}'",
            "nivel_acesso = '{$data['nivel_acesso']}'"
        ];
        
        // 1. Atualizar senha APENAS se o campo não estiver vazio
        if (isset($data['senha']) && !empty($data['senha'])) {
            $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
            $set_parts[] = "senha = '$senha_hash'";
        }

        // 2. Montar SQL
        // CORREÇÃO: Usando 'id_usuario' na cláusula WHERE
        $sql = "UPDATE usuarios SET " . implode(', ', $set_parts) . " WHERE id_usuario = $id";

        if ($conn->query($sql)) {
            send_response(true, null, 'Usuário atualizado com sucesso.');
        } else {
            send_response(false, null, 'Erro ao atualizar usuário: ' . $conn->error);
        }
        break;

    case 'DELETE':
        // DELETE
        // CORREÇÃO: Usando 'id_usuario' na cláusula WHERE
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            send_response(false, null, 'ID do usuário não fornecido.');
        }

        $sql = "DELETE FROM usuarios WHERE id_usuario = $id";

        if ($conn->query($sql)) {
            send_response(true, null, 'Usuário excluído com sucesso.');
        } else {
            send_response(false, null, 'Erro ao excluir usuário: ' . $conn->error);
        }
        break;

    default:
        http_response_code(405); // Método não permitido
        send_response(false, null, 'Método de requisição não suportado.');
}

$conn->close();
?>