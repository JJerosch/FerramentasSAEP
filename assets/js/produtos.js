/**
 * JavaScript para Gerenciamento de Produtos
 * ENTREGA 6 - Cadastro de Produtos
 */

// Carrega produtos ao iniciar a página
document.addEventListener('DOMContentLoaded', function() {
    carregarProdutos();
    
    // Event listener para busca
    document.getElementById('campoBusca').addEventListener('input', function() {
        const termo = this.value;
        carregarProdutos(termo);
    });
    
    // Event listener para formulário
    document.getElementById('formProduto').addEventListener('submit', function(e) {
        e.preventDefault();
        salvarProduto();
    });
});

/**
 * Carrega produtos do banco de dados
 */
function carregarProdutos(termoBusca = '') {
    const url = termoBusca 
        ? `../api/produtos_api.php?acao=listar&busca=${encodeURIComponent(termoBusca)}`
        : '../api/produtos_api.php?acao=listar';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                renderizarProdutos(data.produtos);
            } else {
                mostrarMensagem('Erro ao carregar produtos: ' + data.mensagem, 'danger');
            }
        })
        .catch(error => {
            mostrarMensagem('Erro ao conectar com o servidor', 'danger');
            console.error('Erro:', error);
        });
}

/**
 * Renderiza a tabela de produtos
 */
function renderizarProdutos(produtos) {
    const tbody = document.getElementById('corpoProdutos');
    
    if (produtos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">Nenhum produto encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = produtos.map(produto => {
        const estoqueBaixo = parseInt(produto.quantidade_estoque) <= parseInt(produto.estoque_minimo);
        const badgeClass = estoqueBaixo ? 'badge-danger' : 'badge-success';
        const badgeText = estoqueBaixo ? 'Baixo' : 'OK';
        
        return `
            <tr>
                <td>${produto.id_produto}</td>
                <td>${produto.nome}</td>
                <td>${produto.categoria}</td>
                <td>${produto.material || '-'}</td>
                <td>${produto.quantidade_estoque}</td>
                <td>${produto.estoque_minimo}</td>
                <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarProduto(${produto.id_produto})">
                        Editar
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="excluirProduto(${produto.id_produto}, '${produto.nome}')">
                        Excluir
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Salva produto (criar ou editar)
 */
function salvarProduto() {
    const form = document.getElementById('formProduto');
    const acao = document.getElementById('acaoFormulario').value;
    
    // Validações
    if (!validarFormulario()) {
        return false;
    }
    
    const formData = new FormData(form);
    formData.append('acao', acao);
    
    fetch('../api/produtos_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem(data.mensagem, 'success');
            limparFormulario();
            carregarProdutos();
        } else {
            mostrarMensagem('Erro: ' + data.mensagem, 'danger');
        }
    })
    .catch(error => {
        mostrarMensagem('Erro ao conectar com o servidor', 'danger');
        console.error('Erro:', error);
    });
}

/**
 * Valida os campos do formulário
 */
function validarFormulario() {
    const nome = document.getElementById('nome').value.trim();
    const categoria = document.getElementById('categoria').value;
    const quantidadeEstoque = document.getElementById('quantidade_estoque').value;
    const estoqueMinimo = document.getElementById('estoque_minimo').value;
    
    if (!nome) {
        mostrarMensagem('O nome do produto é obrigatório', 'warning');
        document.getElementById('nome').focus();
        return false;
    }
    
    if (!categoria) {
        mostrarMensagem('A categoria é obrigatória', 'warning');
        document.getElementById('categoria').focus();
        return false;
    }
    
    if (quantidadeEstoque === '' || quantidadeEstoque < 0) {
        mostrarMensagem('A quantidade em estoque deve ser informada e não pode ser negativa', 'warning');
        document.getElementById('quantidade_estoque').focus();
        return false;
    }
    
    if (estoqueMinimo === '' || estoqueMinimo < 1) {
        mostrarMensagem('O estoque mínimo deve ser informado e maior que zero', 'warning');
        document.getElementById('estoque_minimo').focus();
        return false;
    }
    
    return true;
}

/**
 * Edita um produto existente
 */
function editarProduto(id) {
    fetch(`../api/produtos_api.php?acao=buscar&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                const produto = data.produto;
                
                // Preenche o formulário
                document.getElementById('idProduto').value = produto.id_produto;
                document.getElementById('nome').value = produto.nome;
                document.getElementById('categoria').value = produto.categoria;
                document.getElementById('material').value = produto.material || '';
                document.getElementById('tamanho').value = produto.tamanho || '';
                document.getElementById('peso').value = produto.peso || '';
                document.getElementById('quantidade_estoque').value = produto.quantidade_estoque;
                document.getElementById('estoque_minimo').value = produto.estoque_minimo;
                document.getElementById('descricao').value = produto.descricao || '';
                
                // Altera o modo do formulário
                document.getElementById('acaoFormulario').value = 'editar';
                document.getElementById('tituloFormulario').textContent = 'Editar Produto';
                
                // Scroll para o formulário
                document.getElementById('formProduto').scrollIntoView({ behavior: 'smooth' });
            } else {
                mostrarMensagem('Erro ao carregar produto: ' + data.mensagem, 'danger');
            }
        })
        .catch(error => {
            mostrarMensagem('Erro ao conectar com o servidor', 'danger');
            console.error('Erro:', error);
        });
}

/**
 * Exclui um produto
 */
function excluirProduto(id, nome) {
    if (!confirm(`Deseja realmente excluir o produto "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('acao', 'excluir');
    formData.append('id_produto', id);
    
    fetch('../api/produtos_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem(data.mensagem, 'success');
            carregarProdutos();
        } else {
            mostrarMensagem('Erro: ' + data.mensagem, 'danger');
        }
    })
    .catch(error => {
        mostrarMensagem('Erro ao conectar com o servidor', 'danger');
        console.error('Erro:', error);
    });
}

/**
 * Limpa o formulário
 */
function limparFormulario() {
    document.getElementById('formProduto').reset();
    document.getElementById('idProduto').value = '';
    document.getElementById('acaoFormulario').value = 'criar';
    document.getElementById('tituloFormulario').textContent = 'Novo Produto';
}

/**
 * Exibe mensagem de feedback
 */
function mostrarMensagem(mensagem, tipo) {
    const div = document.getElementById('mensagemFeedback');
    div.innerHTML = `<div class="alert alert-${tipo}">${mensagem}</div>`;
    
    // Remove a mensagem após 5 segundos
    setTimeout(() => {
        div.innerHTML = '';
    }, 5000);
    
    // Scroll para a mensagem
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}