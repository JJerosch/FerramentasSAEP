<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../config/database.php';

verificarAutenticacao(true); 

function send_response($success, $data = null, $message = null) {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit();
}

/**
 * Função para limpar e validar dados (evitar XSS/SQL Injection)
 * @param mysqli
 * @param string
 * @return string
 */
function sanitize($conn, $data) {
    if (is_array($data)) {
        $sanitized_array = [];
        foreach ($data as $key => $value) {
            $sanitized_array[$key] = mysqli_real_escape_string($conn, trim($value));
        }
        return $sanitized_array;
    }
    return mysqli_real_escape_string($conn, trim($data));
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'read') {
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
            $id = (int)$_GET['id'];
            $sql = "SELECT id_usuario AS id, nome, email, nivel_acesso FROM usuarios WHERE id_usuario = $id";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                send_response(true, $data);
            } else {
                send_response(false, null, "Usuário não encontrado.");
            }
        }
        break;

    case 'POST':
        $data = sanitize($conn, $_POST);

        if (empty($data['nome']) || empty($data['email']) || empty($data['senha']) || empty($data['nivel_acesso'])) {
            send_response(false, null, 'Todos os campos obrigatórios devem ser preenchidos.');
        }

        $check_email = $conn->query("SELECT id_usuario FROM usuarios WHERE email = '{$data['email']}'");
        if ($check_email->num_rows > 0) {
            send_response(false, null, 'O e-mail informado já está cadastrado.');
        }

        $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, data_criacao) 
                VALUES (
                    '{$data['nome']}', 
                    '{$data['email']}', 
                    '$senha_hash', 
                    '{$data['nivel_acesso']}', 
                    NOW()
                )";

        if ($conn->query($sql)) {
            send_response(true, ['id' => $conn->insert_id], 'Usuário cadastrado com sucesso.');
        } else {
            send_response(false, null, 'Erro ao cadastrar usuário: ' . $conn->error);
        }
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
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
        if (isset($data['senha']) && !empty($data['senha'])) {
            $senha_hash = password_hash($data['senha'], PASSWORD_DEFAULT);
            $set_parts[] = "senha = '$senha_hash'";
        }
        $sql = "UPDATE usuarios SET " . implode(', ', $set_parts) . " WHERE id_usuario = $id";

        if ($conn->query($sql)) {
            send_response(true, null, 'Usuário atualizado com sucesso.');
        } else {
            send_response(false, null, 'Erro ao atualizar usuário: ' . $conn->error);
        }
        break;

    case 'DELETE':
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
        http_response_code(405);
        send_response(false, null, 'Método de requisição não suportado.');
}

$conn->close();
?>