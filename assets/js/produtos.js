/**
 * JavaScript para Gerenciamento de Produtos
 * ENTREGA 6 - Cadastro de Produtos (MODIFICADO PARA CUSTOS)
 */

// --- FUNÇÃO DE UTILIDADE MONETÁRIA ---
/**
 * Aplica máscara monetária (R$ 0.000,00) a um campo de input.
 */
function aplicarMascaraMonetaria(input) {
    let valor = input.value.replace(/\D/g, ''); // Remove tudo que não for dígito
    
    // Converte para Real e formata (ex: 123456 -> R$ 1.234,56)
    const formatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
    });

    // Divide por 100 para ter o valor decimal e formata
    const valorFormatado = formatter.format(valor / 100);
    
    input.value = valorFormatado;
}
// -----------------------------------

/**
 * CORRIGIDA: Obtém o valor numérico limpo de um input monetário (ex: 'R$ 300,00' -> '300.00').
 * Esta versão garante que o valor seja enviado no formato SQL/Internacional (ponto decimal)
 * para máxima compatibilidade com o PHP.
 */
function obterValorMonetarioLimpo(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return '';

    // Pega o valor formatado (ex: R$ 1.234.567,89)
    let valorFormatado = input.value; 

    // 1. Remove R$, espaços e PONTOS (separador de milhar)
    // Ex: 'R$ 1.234.567,89' -> '1234567,89'
    let valorSemMilhar = valorFormatado.replace(/[R$\s.]/g, ''); 
    
    // 2. Troca VÍRGULA (separador decimal BR) por PONTO (separador decimal SQL/Internacional)
    // Ex: '1234567,89' -> '1234567.89'
    let valorSQL = valorSemMilhar.replace(',', '.'); 
    
    // Retorna a string numérica pronta para o PHP/SQL (ex: '300.00')
    return valorSQL; 
}


// Carrega produtos ao iniciar a página
document.addEventListener('DOMContentLoaded', function() {
    carregarProdutos();
    
    // APLICAÇÃO DA MÁSCARA MONETÁRIA
    const inputValorUnitario = document.getElementById('valor_unitario');
    if (inputValorUnitario) {
        inputValorUnitario.addEventListener('input', function() {
            aplicarMascaraMonetaria(this);
        });
        // Aplica a máscara no carregamento para o valor default 0,00
        aplicarMascaraMonetaria(inputValorUnitario); 
    }

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
 * Formata um número decimal para o formato monetário brasileiro (R$ X.XXX,XX)
 */
function formatarParaReal(valor) {
    if (valor === null || valor === undefined || isNaN(parseFloat(valor))) {
        return 'R$ 0,00';
    }
    // O valor vindo do PHP já é um float/string decimal (ex: "10.50")
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(parseFloat(valor));
}


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
                <td>${formatarParaReal(produto.valor_unitario)}</td> 
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
 * Salva produto (criar ou editar) - CORRIGIDA
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
    
    // --- CORREÇÃO PRINCIPAL: Substituir o valor formatado pelo valor limpo ---
    const valorUnitarioLimpo = obterValorMonetarioLimpo('valor_unitario');
    
    // Sobrescreve o valor formatado na FormData pelo valor limpo (ex: '300.00')
    // O PHP agora receberá $_POST['valor_unitario'] como '300.00'
    formData.set('valor_unitario', valorUnitarioLimpo); 
    // --------------------------------------------------------------------------
    
    fetch('../api/produtos_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // CORREÇÃO: Tratar o erro de JSON, pois o PHP pode estar retornando um HTML de erro
        if (!response.ok) {
            // Tenta logar a resposta não JSON do PHP para debug
            response.text().then(text => console.error('Resposta não-JSON do PHP:', text));
            throw new Error(`Erro HTTP! Status: ${response.status}`);
        }
        return response.json();
    })
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
        // Se a resposta não for JSON (HTML de erro do PHP), este catch é ativado.
        mostrarMensagem('Erro ao conectar com o servidor ou resposta inválida. Detalhes no console.', 'danger');
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
    const valorUnitario = document.getElementById('valor_unitario').value;
    
    // Validação do valor unitário (verifica se o campo contém algo que não seja R$, dígitos ou vírgula/ponto)
    // Usamos a função de limpeza para validar
    const valorLimpoParaValidacao = obterValorMonetarioLimpo('valor_unitario'); 
    
    if (valorLimpoParaValidacao === '' || isNaN(parseFloat(valorLimpoParaValidacao)) || parseFloat(valorLimpoParaValidacao) <= 0) {
        mostrarMensagem('O valor unitário deve ser informado e ser maior que zero (ex: R$ 10,50)', 'warning');
        document.getElementById('valor_unitario').focus();
        return false;
    }

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
                
                // Preenchimento de decimais
                document.getElementById('tamanho').value = (produto.tamanho || '0.00').replace('.', ','); // Exibe com vírgula decimal
                document.getElementById('peso').value = (produto.peso || '0.00').replace('.', ','); // Exibe com vírgula decimal

                // NOVO CAMPO: Preenche o valor unitário com o formato monetário
                const inputValorUnitario = document.getElementById('valor_unitario');
                if (inputValorUnitario) {
                    inputValorUnitario.value = formatarParaReal(produto.valor_unitario);
                    // Garante que a máscara esteja aplicada no formato R$ 0,00
                    aplicarMascaraMonetaria(inputValorUnitario); 
                }

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

    // Garante que o campo valor_unitario volte ao formato R$ 0,00
    const inputValorUnitario = document.getElementById('valor_unitario');
    if (inputValorUnitario) {
        inputValorUnitario.value = 'R$ 0,00';
    }
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