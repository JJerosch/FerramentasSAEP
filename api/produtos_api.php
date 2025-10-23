<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

if (!estaLogado()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit();
}

/**
 * Versão Final (Robusta): Converte valores do padrão brasileiro (vírgula decimal) 
 * para o padrão SQL (ponto decimal).
* * @param string $valor O valor vindo do formulário (ex: "R$ 1.234,56" ou "300.00").
 * @return float O valor formatado (ex: 1234.56 ou 0.30).
 */
function formatarValorParaBanco($valor) {
    // 1. Remove TUDO que não for dígito, ponto ou vírgula (por segurança)
    $valorLimpo = preg_replace('/[^\d\.,]/', '', $valor);
    
    // 2. Se a string contém VÍRGULA, remove o ponto de milhar e troca vírgula por ponto decimal.
    // Isso cobre todos os campos (tamanho, peso, valor_unitario)
    if (strpos($valorLimpo, ',') !== false) {
        $valorLimpo = str_replace('.', '', $valorLimpo); // Remove pontos de milhar
        $valorLimpo = str_replace(',', '.', $valorLimpo); // Troca vírgula por ponto decimal
    }
    
    // 3. Garante que é um float válido, ou 0.00.
    // O floatval() ou casting para float é ESSENCIAL para evitar a multiplicação por 100.
    return (float)$valorLimpo;
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
        if (getNivelAcesso() !== 'admin' && getNivelAcesso() !== 'estoquista') {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado para esta operação.']);
            break;
        }
        criarProduto($conn);
        break;
    
    case 'editar':
        if (getNivelAcesso() !== 'admin' && getNivelAcesso() !== 'estoquista') {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado para esta operação.']);
            break;
        }
        editarProduto($conn);
        break;
    
    case 'excluir':
        if (getNivelAcesso() !== 'admin') {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado: Apenas administradores podem excluir.']);
            break;
        }
        excluirProduto($conn);
        break;
    
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
}

$conn->close();

function listarProdutos($conn) {
    $sqlSelect = "id_produto, nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo, valor_unitario";
    $busca = $_GET['busca'] ?? '';
    
    if (!empty($busca)) {
        $sql = "SELECT {$sqlSelect} FROM produtos WHERE nome LIKE ? OR categoria LIKE ? OR material LIKE ? ORDER BY nome";
        $termoBusca = "%{$busca}%";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $termoBusca, $termoBusca, $termoBusca);
    } else {
        $sql = "SELECT {$sqlSelect} FROM produtos ORDER BY nome";
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

function buscarProduto($conn) {
    $sqlSelect = "id_produto, nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo, valor_unitario";
    $id = $_GET['id'] ?? 0;
    
    $sql = "SELECT {$sqlSelect} FROM produtos WHERE id_produto = ?";
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

function criarProduto($conn) {
    
    $erros = validarDadosProduto($_POST);
    if (!empty($erros)) {
        echo json_encode(['sucesso' => false, 'mensagem' => implode(', ', $erros)]);
        return;
    }
    
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'];
    $material = trim($_POST['material'] ?? '');
    
    // Usando a função de formatação para campos decimais
    $tamanho = formatarValorParaBanco($_POST['tamanho'] ?? '0,00'); 
    $peso = formatarValorParaBanco($_POST['peso'] ?? '0,00');
    $quantidade_estoque = intval($_POST['quantidade_estoque']);
    $estoque_minimo = intval($_POST['estoque_minimo']);
    $valor_unitario = formatarValorParaBanco($_POST['valor_unitario'] ?? '0,00');
    
    // INSERT com todos os 9 campos
    $sql = "INSERT INTO produtos (nome, descricao, categoria, material, tamanho, peso, quantidade_estoque, estoque_minimo, valor_unitario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // bind_param: ssssddiid (strings, doubles, integers, integer, double)
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssddiid', $nome, $descricao, $categoria, $material, $tamanho, $peso, $quantidade_estoque, $estoque_minimo, $valor_unitario);
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Produto cadastrado com sucesso!', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar produto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function editarProduto($conn) {
    $id_produto = intval($_POST['id_produto'] ?? 0);
    
    if ($id_produto <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID do produto inválido']);
        return;
    }

    $erros = validarDadosProduto($_POST);
    if (!empty($erros)) {
        echo json_encode(['sucesso' => false, 'mensagem' => implode(', ', $erros)]);
        return;
    }
    
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = $_POST['categoria'];
    $material = trim($_POST['material'] ?? '');
    
    // CORRIGIDO: Variáveis decimais tratadas pela função
    $tamanho = formatarValorParaBanco($_POST['tamanho'] ?? '0,00'); 
    $peso = formatarValorParaBanco($_POST['peso'] ?? '0,00');
    $quantidade_estoque = intval($_POST['quantidade_estoque']); 
    $estoque_minimo = intval($_POST['estoque_minimo']);
    $valor_unitario = formatarValorParaBanco($_POST['valor_unitario'] ?? '0,00');
    
    // Query com 9 campos para UPDATE + WHERE
    $sqlUpdate = "UPDATE produtos SET 
                      nome=?, 
                      descricao=?, 
                      categoria=?, 
                      material=?, 
                      tamanho=?, 
                      peso=?, 
                      quantidade_estoque=?, 
                      estoque_minimo=?, 
                      valor_unitario=? 
                  WHERE id_produto=?";
    
    // bind_param: 'ssssddiidi' (10 tipos no total)
    $stmt = $conn->prepare($sqlUpdate);
    
    $stmt->bind_param('ssssddiidi', 
        $nome, 
        $descricao, 
        $categoria, 
        $material, 
        $tamanho, 
        $peso, 
        $quantidade_estoque, 
        $estoque_minimo, 
        $valor_unitario, // O valor unitário agora é um float tratado
        $id_produto
    );
    
    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Produto atualizado com sucesso!']);
    } else {
        error_log("SQL Error: " . $stmt->error); 
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar produto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function excluirProduto($conn) {
    $id_produto = intval($_POST['id_produto'] ?? 0);
    
    if ($id_produto <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'ID do produto inválido']);
        return;
    }

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
    
    // Validação usando a função de formatação correta
    $tamanho = formatarValorParaBanco($dados['tamanho'] ?? '0,00');
    if ($tamanho < 0) {
        $erros[] = 'Tamanho não pode ser negativo';
    }
    
    $peso = formatarValorParaBanco($dados['peso'] ?? '0,00');
    if ($peso < 0) {
        $erros[] = 'Peso não pode ser negativo';
    }

    // Validação de Valor Unitário
    $valor_unitario = formatarValorParaBanco($dados['valor_unitario'] ?? '0,00');
    if ($valor_unitario <= 0) { // Alterado para ser maior que zero
        $erros[] = 'Valor unitário deve ser maior que zero';
    }
    
    return $erros;
}
?>