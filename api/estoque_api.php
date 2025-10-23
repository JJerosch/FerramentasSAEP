<?php

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../config/database.php';
require_once '../includes/auth.php';

if (!estaLogado()) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit();
}

// Funções de utilidade para conversão de valores (Não usada no fluxo principal, mas mantida)
function formatarValorParaBanco($valor) {
    $valorLimpo = str_replace(['R$', '.'], '', $valor);
    $valorBanco = str_replace(',', '.', $valorLimpo);
    return is_numeric($valorBanco) ? (float)$valorBanco : 0.00;
}


$conn = getConnection();
$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

switch ($acao) {
    case 'listar_produtos':
        listarProdutos($conn);
        break;
    
    case 'info_produto':
        infoProduto($conn);
        break;
    
    case 'registrar_movimentacao':
        registrarMovimentacao($conn);
        break;
    
    case 'historico':
        historicoMovimentacoes($conn);
        break;
    
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
}

// Fechamento da conexão
if ($conn instanceof mysqli && $conn->ping()) {
    $conn->close();
}


function listarProdutos($conn) {
    // Garantindo que valor_unitario seja incluído
    $sql = "SELECT id_produto, nome, categoria, material, quantidade_estoque, estoque_minimo, valor_unitario 
             FROM produtos 
             ORDER BY nome";
    
    $result = $conn->query($sql);
    
    $produtos = [];
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
    
    echo json_encode(['sucesso' => true, 'produtos' => $produtos]);
}

function infoProduto($conn) {
    $id = $_GET['id'] ?? 0;
    
    // SELECT * incluirá valor_unitario
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

function registrarMovimentacao($conn) {
    $id_produto = intval($_POST['id_produto'] ?? 0);
    $tipo_movimentacao = $_POST['tipo_movimentacao'] ?? '';
    $quantidade = intval($_POST['quantidade'] ?? 0);
    $data_movimentacao = $_POST['data_movimentacao'] ?? '';
    $observacao = trim($_POST['observacao'] ?? '');
    $id_usuario = getUsuarioId();
    
    // CAPTURA DO NOVO CAMPO DE CUSTO
    $valor_total = floatval($_POST['valor_total'] ?? 0.00); 

    // Validações
    if ($id_produto <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não selecionado']);
        return;
    }
    
    if (!in_array($tipo_movimentacao, ['entrada', 'saida'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo de movimentação inválido']);
        return;
    }
    
    if ($quantidade <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Quantidade deve ser maior que zero']);
        return;
    }
    
    if (empty($data_movimentacao)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Data da movimentação é obrigatória']);
        return;
    }

    // Validação do valor total
    if ($valor_total <= 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Valor total da movimentação deve ser maior que zero']);
        return;
    }
    
    // Busca informações do produto
    $sqlProduto = "SELECT nome, quantidade_estoque, estoque_minimo FROM produtos WHERE id_produto = ?";
    $stmtProduto = $conn->prepare($sqlProduto);
    $stmtProduto->bind_param('i', $id_produto);
    $stmtProduto->execute();
    $resultProduto = $stmtProduto->get_result();
    
    if ($resultProduto->num_rows === 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado']);
        $stmtProduto->close();
        return;
    }
    
    $produto = $resultProduto->fetch_assoc();
    $estoque_atual = intval($produto['quantidade_estoque']);
    $estoque_minimo = intval($produto['estoque_minimo']);
    $nome_produto = $produto['nome'];
    $stmtProduto->close();
    
    // Calcula novo estoque e verifica saída
    if ($tipo_movimentacao === 'entrada') {
        $novo_estoque = $estoque_atual + $quantidade;
    } else {
        if ($estoque_atual < $quantidade) {
            echo json_encode([
                'sucesso' => false, 
                'mensagem' => "Estoque insuficiente. Disponível: {$estoque_atual} unidades"
            ]);
            return;
        }
        $novo_estoque = $estoque_atual - $quantidade;
    }
    
    $conn->begin_transaction();
    
    try {
        // CORREÇÃO: Adicionando valor_total no INSERT
        $sqlMov = "INSERT INTO movimentacoes (id_produto, id_usuario, tipo_movimentacao, quantidade, valor_total, data_movimentacao, observacao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtMov = $conn->prepare($sqlMov);
        // Binding: i (id_produto), i (id_usuario), s (tipo), i (quantidade), d (valor_total), s (data), s (observacao)
        $stmtMov->bind_param('iisidss', $id_produto, $id_usuario, $tipo_movimentacao, $quantidade, $valor_total, $data_movimentacao, $observacao);
        
        if (!$stmtMov->execute()) {
            throw new Exception('Erro ao registrar movimentação: ' . $stmtMov->error);
        }
        $stmtMov->close();

        // Atualização de estoque
        $sqlUpdate = "UPDATE produtos SET quantidade_estoque = ? WHERE id_produto = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('ii', $novo_estoque, $id_produto);
        
        if (!$stmtUpdate->execute()) {
            throw new Exception('Erro ao atualizar estoque');
        }
        $stmtUpdate->close();

        $conn->commit();

        $alerta_estoque = ($tipo_movimentacao === 'saida' && $novo_estoque <= $estoque_minimo);
        
        $response = [
            'sucesso' => true,
            'mensagem' => 'Movimentação registrada com sucesso!',
            'estoque_atual' => $novo_estoque,
            'alerta_estoque' => $alerta_estoque
        ];
        
        if ($alerta_estoque) {
            $response['produto_nome'] = $nome_produto;
            $response['estoque_minimo'] = $estoque_minimo;
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['sucesso' => false, 'mensagem' => 'Falha na transação: ' . $e->getMessage()]);
    }
}

function historicoMovimentacoes($conn) {
    // CORREÇÃO: Incluindo m.valor_total no SELECT
    $sql = "SELECT m.*, p.nome as produto_nome, u.nome as usuario_nome 
             FROM movimentacoes m
             INNER JOIN produtos p ON m.id_produto = p.id_produto
             INNER JOIN usuarios u ON m.id_usuario = u.id_usuario
             ORDER BY m.data_registro DESC
             LIMIT 20";
    
    $result = $conn->query($sql);
    
    $movimentacoes = [];
    while ($row = $result->fetch_assoc()) {
        $movimentacoes[] = $row;
    }
    
    echo json_encode(['sucesso' => true, 'movimentacoes' => $movimentacoes]);
}
?>