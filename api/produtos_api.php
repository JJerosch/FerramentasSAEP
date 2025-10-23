<?php
/**
 * API de Gerenciamento de Produtos
 * ENTREGA 6 - CRUD de Produtos
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

// Verifica autenticação
if (!estaLogado()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit();
}

$conn = getConnection();
$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($acao) {
    case 'listar':
        listarProdutos($conn);
        break;
    
    case 'buscar':
        buscarProduto($conn);
        break;
    
    case 'criar':
        criarProduto($conn);
        break;
    
    case 'editar':
        editarProduto($conn);
        break;
    
    case 'excluir':
        excluirProduto($conn);
        break;
    
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
}

$conn->close();

/**
 * Lista todos os produtos ou filtra por termo de busca
 */
function listarProdutos($conn) {
    $busca = $_GET['busca'] ?? '';
    
    if (!empty($busca)) {
        $sql = "SELECT * FROM produtos WHERE nome LIKE ? OR categoria LIKE ? OR material LIKE ? ORDER BY nome";
        $termoBusca = "%{$busca}%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $termoBusca, $termoBusca, $termoBusca);
    } else {
        $sql = "SELECT * FROM produtos ORDER BY nome";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    echo json_encode(['sucesso' => true, 'produtos' => $produtos]);
    $stmt->close();
}

/**
 * Busca um produto específico por ID
 */
function buscarProduto($conn) {
    $id = $_GET['id'] ?? 0;
    
    $sql = "SELECT * FROM produtos WHERE id_produto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();
        echo json_encode(['sucesso' => true, 'produto' => $produto]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado']);
    }
    
    $stmt->close();
}

/**
 * Cria um novo produto
 */
function criarProduto($conn) {
    // Validação dos dados
    $erros = validarDadosProduto($_POST);
    if (!empty($erros)) {
        echo json_encode(['sucesso' => false, 'mensagem' => implode(', ', $erros)]);
        return;
    }
    
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'];
    $material = trim($_POST['material'] ?? '');
    $tamanho = !empty($_POST['tamanho']) ? floatval($_POST['tamanho']) : null;
    $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
    $quantidade_estoque = intval($_POST['quantidade_estoque']);
    $estoque_minimo = intval($_POST['estoque_minimo']);
    
    $sql = "INSERT INTO produtos (nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssddii', $nome, $descricao, $categoria, $material, $tamanho, $peso, $quantidade_estoque, $estoque_minimo);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Produto cadastrado com sucesso!', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar produto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

/**
 * Edita um produto existente
 */
function editarProduto($conn) {
    $id_produto = intval($_POST['id_produto'] ?? 0);
    
    if ($id_produto <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID do produto inválido']);
        return;
    }
    
    // Validação dos dados
    $erros = validarDadosProduto($_POST);
    if (!empty($erros)) {
        echo json_encode(['sucesso' => false, 'mensagem' => implode(', ', $erros)]);
        return;
    }
    
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'];
    $material = trim($_POST['material'] ?? '');
    $tamanho = !empty($_POST['tamanho']) ? floatval($_POST['tamanho']) : null;
    $peso = !empty($_POST['peso']) ? floatval($_POST['peso']) : null;
    $quantidade_estoque = intval($_POST['quantidade_estoque']);
    $estoque_minimo = intval($_POST['estoque_minimo']);
    
    $sql = "UPDATE produtos SET nome = ?, descricao = ?, categoria = ?, material = ?, 
            tamanho = ?, peso = ?, quantidade_estoque = ?, estoque_minimo = ? 
            WHERE id_produto = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssddiii', $nome, $descricao, $categoria, $material, $tamanho, $peso, $quantidade_estoque, $estoque_minimo, $id_produto);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Produto atualizado com sucesso!']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar produto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

/**
 * Exclui um produto
 */
function excluirProduto($conn) {
    $id_produto = intval($_POST['id_produto'] ?? 0);
    
    if ($id_produto <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID do produto inválido']);
        return;
    }
    
    // Verifica se há movimentações associadas
    $sqlCheck = "SELECT COUNT(*) as total FROM movimentacoes WHERE id_produto = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param('i', $id_produto);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $row = $resultCheck->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Não é possível excluir este produto pois existem movimentações associadas']);
        $stmtCheck->close();
        return;
    }
    $stmtCheck->close();
    
    $sql = "DELETE FROM produtos WHERE id_produto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_produto);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Produto excluído com sucesso!']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir produto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

/**
 * Valida os dados do produto
 */
function validarDadosProduto($dados) {
    $erros = [];
    
    if (empty(trim($dados['nome'] ?? ''))) {
        $erros[] = 'Nome do produto é obrigatório';
    }
    
    if (empty($dados['categoria'] ?? '')) {
        $erros[] = 'Categoria é obrigatória';
    }
    
    $quantidade = $dados['quantidade_estoque'] ?? '';
    if ($quantidade === '' || $quantidade < 0) {
        $erros[] = 'Quantidade em estoque inválida';
    }
    
    $estoque_min = $dados['estoque_minimo'] ?? '';
    if ($estoque_min === '' || $estoque_min < 1) {
        $erros[] = 'Estoque mínimo deve ser maior que zero';
    }
    
    if (!empty($dados['tamanho']) && floatval($dados['tamanho']) < 0) {
        $erros[] = 'Tamanho não pode ser negativo';
    }
    
    if (!empty($dados['peso']) && floatval($dados['peso']) < 0) {
        $erros[] = 'Peso não pode ser negativo';
    }
    
    return $erros;
}
?>