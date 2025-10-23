/**
 * JavaScript para Gestão de Estoque
 * ENTREGA 7 - Movimentação de Estoque
 */

let produtoSelecionado = null;

// Carrega dados ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    carregarProdutos();
    carregarHistorico();
    
    // Define data atual como padrão
    document.getElementById('data_movimentacao').valueAsDate = new Date();
    
    // Event listener para formulário
    document.getElementById('formMov').addEventListener('submit', function(e) {
        e.preventDefault();
        registrarMovimentacao();
    });
});

/**
 * Carrega e ordena produtos alfabeticamente
 * REQUISITO 7.1.1 - Ordenação alfabética com algoritmo de ordenação
 */
function carregarProdutos() {
    fetch('../api/estoque_api.php?acao=listar_produtos')
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                // Aplica algoritmo de ordenação (Bubble Sort) para ordem alfabética
                const produtosOrdenados = bubbleSort(data.produtos);
                renderizarProdutos(produtosOrdenados);
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
 * Algoritmo Bubble Sort para ordenação alfabética
 * REQUISITO 7.1.1 - Implementação de algoritmo de ordenação
 */
function bubbleSort(array) {
    const arr = [...array];
    const n = arr.length;
    
    for (let i = 0; i < n - 1; i++) {
        for (let j = 0; j < n - i - 1; j++) {
            if (arr[j].nome.toLowerCase() > arr[j + 1].nome.toLowerCase()) {
                // Troca elementos
                const temp = arr[j];
                arr[j] = arr[j + 1];
                arr[j + 1] = temp;
            }
        }
    }
    
    return arr;
}

/**
 * Renderiza lista de produtos
 */
function renderizarProdutos(produtos) {
    const container = document.getElementById('listaProdutos');
    
    if (produtos.length === 0) {
        container.innerHTML = '<p class="text-center" style="color: #64748b;">Nenhum produto cadastrado</p>';
        return;
    }
    
    container.innerHTML = produtos.map(produto => {
        const estoqueBaixo = parseInt(produto.quantidade_estoque) <= parseInt(produto.estoque_minimo);
        const badgeClass = estoqueBaixo ? 'badge-danger' : 'badge-success';
        const badgeText = estoqueBaixo ? 'Estoque Baixo' : 'Estoque OK';
        
        return `
            <div class="produto-item" onclick="selecionarProduto(${produto.id_produto}, '${produto.nome.replace(/'/g, "\\'")}')">
                <div class="produto-info">
                    <div>
                        <div class="produto-nome">${produto.nome}</div>
                        <div class="produto-categoria">${produto.categoria}</div>
                    </div>
                    <div class="produto-estoque">
                        <div style="font-size: 1.25rem; font-weight: 600;">${produto.quantidade_estoque}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">em estoque</div>
                        <span class="badge ${badgeClass}" style="margin-top: 0.25rem;">${badgeText}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Seleciona um produto para movimentação
 * REQUISITO 7.1.2 - Seleção de produto e tipo de movimentação
 */
function selecionarProduto(id, nome) {
    // Remove seleção anterior
    document.querySelectorAll('.produto-item').forEach(item => {
        item.classList.remove('selecionado');
    });
    
    // Adiciona classe ao item selecionado
    event.currentTarget.classList.add('selecionado');
    
    // Armazena produto selecionado
    produtoSelecionado = id;
    
    // Atualiza formulário
    document.getElementById('produtoSelecionadoId').value = id;
    document.getElementById('produtoSelecionadoNome').textContent = nome;
    document.getElementById('formMovimentacao').classList.add('ativo');
    
    // Carrega informações detalhadas do produto
    carregarInfoProduto(id);
}

/**
 * Carrega informações detalhadas do produto
 */
function carregarInfoProduto(id) {
    fetch(`../api/estoque_api.php?acao=info_produto&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                const p = data.produto;
                const estoqueBaixo = parseInt(p.quantidade_estoque) <= parseInt(p.estoque_minimo);
                
                document.getElementById('infoConteudo').innerHTML = `
                    <div style="display: grid; gap: 0.75rem;">
                        <div>
                            <strong>Estoque Atual:</strong> ${p.quantidade_estoque} unidades
                        </div>
                        <div>
                            <strong>Estoque Mínimo:</strong> ${p.estoque_minimo} unidades
                        </div>
                        <div>
                            <strong>Status:</strong> 
                            <span class="badge ${estoqueBaixo ? 'badge-danger' : 'badge-success'}">
                                ${estoqueBaixo ? 'Abaixo do Mínimo' : 'OK'}
                            </span>
                        </div>
                        ${p.material ? `<div><strong>Material:</strong> ${p.material}</div>` : ''}
                        ${p.tamanho ? `<div><strong>Tamanho:</strong> ${p.tamanho} cm</div>` : ''}
                        ${p.peso ? `<div><strong>Peso:</strong> ${p.peso} kg</div>` : ''}
                    </div>
                `;
                document.getElementById('infoProduto').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar informações:', error);
        });
}

/**
 * Registra movimentação de estoque
 * REQUISITO 7.1.3 - Registro de movimentação com data
 * REQUISITO 7.1.4 - Verificação de estoque mínimo
 */
function registrarMovimentacao() {
    if (!validarFormulario()) {
        return false;
    }
    
    const formData = new FormData(document.getElementById('formMov'));
    formData.append('acao', 'registrar_movimentacao');
    
    fetch('../api/estoque_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem(data.mensagem, 'success');
            
            // Verifica alerta de estoque baixo
            if (data.alerta_estoque) {
                mostrarAlertaEstoqueBaixo(data.produto_nome, data.estoque_atual, data.estoque_minimo);
            }
            
            // Limpa formulário e recarrega dados
            document.getElementById('formMov').reset();
            document.getElementById('data_movimentacao').valueAsDate = new Date();
            carregarProdutos();
            carregarHistorico();
            
            // Recarrega informações do produto se ainda selecionado
            if (produtoSelecionado) {
                carregarInfoProduto(produtoSelecionado);
            }
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
 * Valida formulário de movimentação
 */
function validarFormulario() {
    const tipo = document.querySelector('input[name="tipo_movimentacao"]:checked');
    const quantidade = document.getElementById('quantidade').value;
    const data = document.getElementById('data_movimentacao').value;
    
    if (!tipo) {
        mostrarMensagem('Selecione o tipo de movimentação', 'warning');
        return false;
    }
    
    if (!quantidade || quantidade <= 0) {
        mostrarMensagem('A quantidade deve ser maior que zero', 'warning');
        document.getElementById('quantidade').focus();
        return false;
    }
    
    if (!data) {
        mostrarMensagem('Informe a data da movimentação', 'warning');
        document.getElementById('data_movimentacao').focus();
        return false;
    }
    
    return true;
}

/**
 * Exibe alerta de estoque baixo
 * REQUISITO 7.1.4 - Alerta automático de estoque baixo
 */
function mostrarAlertaEstoqueBaixo(produto, estoqueAtual, estoqueMinimo) {
    const alerta = `
        <div class="alert alert-warning" style="border: 2px solid #f59e0b; background-color: #fef3c7;">
            <strong>⚠️ ALERTA DE ESTOQUE BAIXO!</strong><br>
            O produto <strong>${produto}</strong> está com estoque abaixo do mínimo:<br>
            Estoque Atual: <strong>${estoqueAtual}</strong> unidades<br>
            Estoque Mínimo: <strong>${estoqueMinimo}</strong> unidades<br>
            <em>É recomendado realizar reposição.</em>
        </div>
    `;
    
    const div = document.getElementById('mensagemFeedback');
    div.innerHTML = alerta + div.innerHTML;
    
    // Scroll para o alerta
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Carrega histórico de movimentações
 */
function carregarHistorico() {
    fetch('../api/estoque_api.php?acao=historico')
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                renderizarHistorico(data.movimentacoes);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar histórico:', error);
        });
}

/**
 * Renderiza histórico de movimentações
 */
function renderizarHistorico(movimentacoes) {
    const container = document.getElementById('historicoMovimentacoes');
    
    if (movimentacoes.length === 0) {
        container.innerHTML = '<p class="text-center" style="color: #64748b;">Nenhuma movimentação registrada</p>';
        return;
    }
    
    container.innerHTML = movimentacoes.map(mov => {
        const icone = mov.tipo_movimentacao === 'entrada' ? '' : '';
        const tipoTexto = mov.tipo_movimentacao === 'entrada' ? 'Entrada' : 'Saída';
        
        return `
            <div class="historico-item ${mov.tipo_movimentacao}">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">
                            ${icone} ${tipoTexto} - ${mov.produto_nome}
                        </div>
                        <div style="font-size: 0.875rem; color: #64748b;">
                            Quantidade: <strong>${mov.quantidade}</strong> unidades<br>
                            Data: ${formatarData(mov.data_movimentacao)}<br>
                            Responsável: ${mov.usuario_nome}
                            ${mov.observacao ? `<br>Obs: ${mov.observacao}` : ''}
                        </div>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; text-align: right;">
                        ${formatarDataHora(mov.data_registro)}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Formata data para exibição
 */
function formatarData(dataString) {
    const data = new Date(dataString + 'T00:00:00');
    return data.toLocaleDateString('pt-BR');
}

/**
 * Formata data e hora para exibição
 */
function formatarDataHora(dataString) {
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR');
}

/**
 * Exibe mensagem de feedback
 */
function mostrarMensagem(mensagem, tipo) {
    const div = document.getElementById('mensagemFeedback');
    const alertaHtml = `<div class="alert alert-${tipo}">${mensagem}</div>`;
    
    div.innerHTML = alertaHtml + div.innerHTML;
    
    // Remove mensagens antigas após 5 segundos
    setTimeout(() => {
        const alertas = div.querySelectorAll('.alert');
        if (alertas.length > 3) {
            alertas[alertas.length - 1].remove();
        }
    }, 5000);
    
    // Scroll para a mensagem
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}