/**
 * JavaScript para Gestão de Estoque
 * ENTREGA 7 - Movimentação de Estoque (MODIFICADO PARA CUSTOS)
 */

let produtoSelecionado = null; // Armazenará o objeto completo do produto, incluindo valor_unitario

// --- FUNÇÕES DE UTILIDADE MONETÁRIA/CÁLCULO ---

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
 * Calcula e exibe o valor total da movimentação
 */
function calcularValorTotal() {
    const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
    
    // Pega o valor unitário armazenado no produtoSelecionado (já é um float)
    const valorUnitario = produtoSelecionado && produtoSelecionado.valor_unitario 
        ? parseFloat(produtoSelecionado.valor_unitario) 
        : 0;

    const valorTotal = quantidade * valorUnitario;
    
    // Atualiza o campo de exibição (somente leitura)
    document.getElementById('valor_total_display').value = formatarParaReal(valorTotal);
    
    // Atualiza o campo hidden que será enviado para a API (formato numérico)
    document.getElementById('valorTotalInput').value = valorTotal.toFixed(2);
}

// ---------------------------------------------

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

    // Event listener para o campo Quantidade e Tipo (para recalcular o Valor Total)
    document.getElementById('quantidade').addEventListener('input', calcularValorTotal);
    
    document.querySelectorAll('input[name="tipo_movimentacao"]').forEach(radio => {
        radio.addEventListener('change', calcularValorTotal); 
    });

    // Inicializa o valor total como zero
    calcularValorTotal();
});

// --- FUNÇÕES DE CARREGAMENTO E RENDERIZAÇÃO ---

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
 */
function bubbleSort(array) {
    const arr = [...array];
    const n = arr.length;
    
    for (let i = 0; i < n - 1; i++) {
        for (let j = 0; j < n - i - 1; j++) {
            if (arr[j].nome.toLowerCase() > arr[j + 1].nome.toLowerCase()) {
                const temp = arr[j];
                arr[j] = arr[j + 1];
                arr[j + 1] = temp;
            }
        }
    }
    
    return arr;
}

/**
 * Renderiza lista de produtos (CORRIGIDO PARA USO DE DATA-ATTRIBUTE)
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
        
        // CORREÇÃO DE CLIQUE: Serializa o objeto completo e escapa as aspas para o HTML
        const produtoJsonString = JSON.stringify(produto).replace(/"/g, '&quot;'); 

        return `
            <div class="produto-item" 
                 data-produto="${produtoJsonString}" 
                 onclick="selecionarProduto(this)">  
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
 * Seleciona um produto para movimentação (CORRIGIDO PARA USO DE DATA-ATTRIBUTE)
 * @param {HTMLElement} element - O elemento DIV.produto-item clicado (passado via 'this').
 */
function selecionarProduto(element) {
    // Remove seleção anterior
    document.querySelectorAll('.produto-item').forEach(item => {
        item.classList.remove('selecionado');
    });
    
    // Adiciona classe ao item selecionado
    element.classList.add('selecionado');
    
    // Recupera a string JSON do atributo e a deserializa
    const produtoJsonString = element.getAttribute('data-produto').replace(/&quot;/g, '"');
    const produto = JSON.parse(produtoJsonString);

    // Armazena o objeto completo do produto
    produtoSelecionado = produto;
    
    // Atualiza formulário
    document.getElementById('produtoSelecionadoId').value = produto.id_produto;
    document.getElementById('produtoSelecionadoNome').textContent = produto.nome;

    // Armazena o valor unitário no campo oculto para referência
    document.getElementById('produtoSelecionadoValorUnitario').value = produto.valor_unitario;

    // Recalcula o valor total
    calcularValorTotal();

    const formContainer = document.getElementById('formMovimentacao');
    if (formContainer) {
        formContainer.classList.add('ativo');
    }
    
    // Carrega informações detalhadas do produto
    carregarInfoProduto(produto.id_produto);
}

/**
 * Carrega informações detalhadas do produto
 */
function carregarInfoProduto(id) {
    fetch(`../api/estoque_api.php?acao=info_produto&id=${id}`)
        .then(response => response.json())
        .then(data => {
            const infoProdutoCard = document.getElementById('infoProduto');
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
                
                // Exibe o valor unitário
                document.getElementById('infoValorUnitario').textContent = formatarParaReal(p.valor_unitario);

                infoProdutoCard.style.display = 'block';

                produtoSelecionado = p;

            } else {
                infoProdutoCard.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar informações:', error);
            document.getElementById('infoProduto').style.display = 'none';
        });
}

/**
 * Registra movimentação de estoque
 */
function registrarMovimentacao() {
    if (!validarFormulario()) {
        return false;
    }
    
    const formData = new FormData(document.getElementById('formMov'));
    formData.append('acao', 'registrar_movimentacao');
    
    // Desabilita o botão para evitar cliques múltiplos
    const submitButton = document.querySelector('#formMov button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Registrando...';
    
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

            document.getElementById('valor_total_display').value = formatarParaReal(0);
            document.getElementById('valorTotalInput').value = '0.00';
            
            carregarProdutos();
            carregarHistorico();
            
            if (produtoSelecionado) {
                carregarInfoProduto(produtoSelecionado.id_produto);
            }
        } else {
            mostrarMensagem('Erro: ' + data.mensagem, 'danger');
        }
    })
    .catch(error => {
        mostrarMensagem('Erro ao conectar com o servidor', 'danger');
        console.error('Erro:', error);
    })
    .finally(() => {
        // Reabilita o botão
        submitButton.disabled = false;
        submitButton.textContent = 'Registrar Movimentação';
    });
}

/**
 * Valida formulário de movimentação
 */
function validarFormulario() {
    const tipo = document.querySelector('input[name="tipo_movimentacao"]:checked');
    const quantidade = document.getElementById('quantidade').value;
    const data = document.getElementById('data_movimentacao').value;
    const valorTotal = parseFloat(document.getElementById('valorTotalInput').value) || 0; 

    if (!produtoSelecionado) {
        mostrarMensagem('Selecione um produto para movimentar', 'warning');
        return false;
    }

    if (!tipo) {
        mostrarMensagem('Selecione o tipo de movimentação', 'warning');
        return false;
    }
    
    if (!quantidade || parseFloat(quantidade) <= 0) {
        mostrarMensagem('A quantidade deve ser maior que zero', 'warning');
        document.getElementById('quantidade').focus();
        return false;
    }
    
    if (valorTotal <= 0) {
        mostrarMensagem('O valor total da movimentação deve ser maior que zero (verifique o valor unitário do produto e a quantidade)', 'warning');
        return false;
    }

    if (!data) {
        mostrarMensagem('Informe a data da movimentação', 'warning');
        document.getElementById('data_movimentacao').focus();
        return false;
    }
    
    // Validação extra para SAÍDA: verificar se há estoque suficiente
    if (tipo.value === 'saida') {
        const estoqueAtual = parseInt(produtoSelecionado.quantidade_estoque) || 0;
        const quantidadeSaida = parseInt(quantidade) || 0;
        
        if (quantidadeSaida > estoqueAtual) {
            mostrarMensagem(`Estoque insuficiente! Você está tentando retirar ${quantidadeSaida} unidades, mas há apenas ${estoqueAtual} em estoque.`, 'warning');
            document.getElementById('quantidade').focus();
            return false;
        }
    }

    return true;
}

/**
 * Exibe alerta de estoque baixo
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
    div.insertAdjacentHTML('afterbegin', alerta);
    
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
            } else {
                 document.getElementById('historicoMovimentacoes').innerHTML = '<p class="text-center alert alert-danger">Erro ao carregar histórico.</p>';
            }
        })
        .catch(error => {
             document.getElementById('historicoMovimentacoes').innerHTML = '<p class="text-center alert alert-danger">Falha na comunicação com o servidor.</p>';
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
        const icone = mov.tipo_movimentacao === 'entrada' ? '⬆️' : '⬇️';
        const tipoTexto = mov.tipo_movimentacao === 'entrada' ? 'Entrada' : 'Saída';
        const valorFormatado = formatarParaReal(mov.valor_total); // Formata o valor

        return `
            <div class="historico-item ${mov.tipo_movimentacao}">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">
                            ${icone} ${tipoTexto} - ${mov.produto_nome}
                        </div>
                        <div style="font-size: 0.875rem; color: #64748b;">
                            Quantidade: <strong>${mov.quantidade}</strong> unidades<br>
                            Valor Total: <strong>${valorFormatado}</strong><br>
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
    
    div.insertAdjacentHTML('afterbegin', alertaHtml);
    
    setTimeout(() => {
        const primeiroAlerta = div.querySelector('.alert:not(.alert-warning)');
        if (primeiroAlerta && primeiroAlerta.textContent.includes(mensagem)) {
             primeiroAlerta.remove();
        }
    }, 5000);
    
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}