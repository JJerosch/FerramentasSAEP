/**
 * assets/js/usuarios.js
 * Lógica de Frontend para Cadastro e Gestão de Usuários
 */

document.addEventListener('DOMContentLoaded', () => {
    const formUsuario = document.getElementById('formUsuario');
    const tabelaUsuarios = document.getElementById('corpoUsuarios');
    const idUsuario = document.getElementById('idUsuario');
    const acaoFormulario = document.getElementById('acaoFormulario');
    const tituloFormulario = document.getElementById('tituloFormulario');
    const campoBusca = document.getElementById('campoBusca');
    const mensagemFeedback = document.getElementById('mensagemFeedback');
    
    // Elementos do formulário
    const inputSenha = document.getElementById('senha');
    const inputConfirmaSenha = document.getElementById('confirmar_senha');
    
    // NOVOS ELEMENTOS: Grupos de campos para ocultar/mostrar
    const senhaGroup = document.getElementById('senha-field-group');
    const confirmarSenhaGroup = document.getElementById('confirmar-senha-field-group');
    
    // URL da API
    const API_URL = '../api/usuarios_api.php';

    // =======================================================
    // FUNÇÕES DE UTILIDADE
    // =======================================================

    /** Exibe mensagens de feedback */
    function exibirMensagem(texto, tipo = 'success') {
        const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
        mensagemFeedback.innerHTML = `<div class="alert ${alertClass}">${texto}</div>`;
        setTimeout(() => {
            mensagemFeedback.innerHTML = '';
        }, 5000);
    }

    /** Limpa o formulário e reseta para o modo "Criar" */
    window.limparFormulario = function() {
        formUsuario.reset();
        idUsuario.value = '';
        acaoFormulario.value = 'criar';
        tituloFormulario.textContent = 'Novo Usuário';
        
        // MODO CRIAR: Senha é obrigatória e visível
        inputSenha.required = true;
        inputConfirmaSenha.required = true;
        
        if (senhaGroup) senhaGroup.style.display = 'block';
        if (confirmarSenhaGroup) confirmarSenhaGroup.style.display = 'block';
    };
    
    /** Desenha a tabela com os dados recebidos */
    function renderizarTabela(usuarios) {
        tabelaUsuarios.innerHTML = '';
        if (usuarios.length === 0) {
            tabelaUsuarios.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum usuário encontrado.</td></tr>';
            return;
        }

        usuarios.forEach(usuario => {
            const row = tabelaUsuarios.insertRow();
            
            // Formatando Data
            const dataCadastro = new Date(usuario.data_cadastro);
            const dataFormatada = dataCadastro.toLocaleDateString('pt-BR');

            row.insertCell(0).textContent = usuario.id;
            row.insertCell(1).textContent = usuario.nome;
            row.insertCell(2).textContent = usuario.email;
            row.insertCell(3).textContent = usuario.nivel_acesso.toUpperCase();
            row.insertCell(4).textContent = dataFormatada;

            // Coluna de Ações
            const acoesCell = row.insertCell(5);
            acoesCell.className = 'd-flex gap-1';
            
            const btnEditar = document.createElement('button');
            btnEditar.textContent = 'Editar';
            btnEditar.className = 'btn btn-warning btn-sm';
            btnEditar.onclick = () => carregarUsuarioParaEdicao(usuario.id);

            const btnExcluir = document.createElement('button');
            btnExcluir.textContent = 'Excluir';
            btnExcluir.className = 'btn btn-danger btn-sm';
            btnExcluir.onclick = () => excluirUsuario(usuario.id, usuario.nome);

            acoesCell.appendChild(btnEditar);
            acoesCell.appendChild(btnExcluir);
        });
    }

    // =======================================================
    // FUNÇÕES DE CRUD (GET)
    // =======================================================

    /** Busca a lista de usuários e renderiza a tabela */
    async function carregarUsuarios(termoBusca = '') {
        // ... (código inalterado)
        tabelaUsuarios.innerHTML = '<tr><td colspan="6" class="text-center">Carregando...</td></tr>';
        
        try {
            const url = termoBusca ? `${API_URL}?action=read&busca=${termoBusca}` : `${API_URL}?action=read`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                renderizarTabela(data.data);
            } else {
                tabelaUsuarios.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Erro: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Erro ao carregar usuários:', error);
            tabelaUsuarios.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro de conexão com o servidor.</td></tr>';
        }
    }

    /** Carrega os dados de um usuário para o formulário de edição */
    async function carregarUsuarioParaEdicao(id) {
        try {
            const response = await fetch(`${API_URL}?action=get_single&id=${id}`);
            const data = await response.json();

            if (data.success && data.data) {
                const usuario = data.data;
                
                // Popula o formulário
                idUsuario.value = usuario.id;
                document.getElementById('nome').value = usuario.nome;
                document.getElementById('email').value = usuario.email;
                document.getElementById('nivel_acesso').value = usuario.nivel_acesso;
                
                // Altera o modo do formulário
                acaoFormulario.value = 'editar';
                tituloFormulario.textContent = `Editar Usuário: ${usuario.nome}`;
                
                // MODO EDIÇÃO: Senha não é obrigatória e campos são ocultados
                inputSenha.required = false;
                inputConfirmaSenha.required = false;
                
                if (senhaGroup) senhaGroup.style.display = 'none';
                if (confirmarSenhaGroup) confirmarSenhaGroup.style.display = 'none';

                // Limpa os valores para evitar envio acidental de senha no PUT
                inputSenha.value = ''; 
                inputConfirmaSenha.value = '';

                window.scrollTo(0, 0); // Sobe para o formulário
            } else {
                exibirMensagem(`Erro ao carregar dados: ${data.message}`, 'danger');
            }
        } catch (error) {
            console.error('Erro ao carregar usuário:', error);
            exibirMensagem('Erro de conexão ao tentar carregar usuário.', 'danger');
        }
    }

    // =======================================================
    // FUNÇÕES DE CRUD (POST/PUT/DELETE)
    // =======================================================

    /** Manipula o envio do formulário (Criar ou Editar) */
    formUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Validação de Senha (só se os campos estiverem visíveis OU se for um POST)
        const modo = acaoFormulario.value;
        const senhaValor = inputSenha.value;
        const confirmaSenhaValor = inputConfirmaSenha.value;
        
        if (senhaValor !== confirmaSenhaValor) {
            exibirMensagem('As senhas digitadas não coincidem!', 'danger');
            inputSenha.focus();
            return;
        }

        // Se for EDIÇÃO, a senha só é validada se o usuário a preencheu (para alteração)
        if (modo === 'criar' && senhaValor === '') {
             exibirMensagem('O campo Senha é obrigatório para um novo cadastro.', 'danger');
             inputSenha.focus();
             return;
        }

        const formData = new FormData(formUsuario);
        
        // Se for edição e a senha não foi preenchida, remove os campos do FormData
        // Isso garante que a API não receba a chave 'senha' ou receba vazia, 
        // mantendo a senha atual no banco.
        if (modo === 'editar' && senhaValor === '') {
            formData.delete('senha');
            formData.delete('confirmar_senha');
        }

        // 2. Define o método da requisição
        const method = (modo === 'criar') ? 'POST' : 'PUT';
        let url = API_URL;

        if (modo === 'editar') {
            url += `?id=${idUsuario.value}`;
        }
        
        // 3. Envia a requisição
        try {
            const response = await fetch(url, {
                method: method,
                // O body precisa ser uma string para o PUT, se o fetch for em formato URL-encoded
                body: (method === 'PUT') ? new URLSearchParams(formData).toString() : formData
            });

            const data = await response.json();

            if (data.success) {
                exibirMensagem(`Usuário ${modo === 'criar' ? 'cadastrado' : 'atualizado'} com sucesso!`);
                limparFormulario();
                carregarUsuarios(); // Recarrega a lista
            } else {
                exibirMensagem(`Falha ao ${modo === 'criar' ? 'cadastrar' : 'atualizar'} usuário: ${data.message}`, 'danger');
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            exibirMensagem('Erro de conexão com a API.', 'danger');
        }
    });

    /** Função para excluir usuário */
    window.excluirUsuario = async function(id, nome) {
        // ... (código inalterado)
        if (!confirm(`Tem certeza que deseja EXCLUIR o usuário "${nome}"?`)) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}?id=${id}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                exibirMensagem(`Usuário "${nome}" excluído com sucesso.`);
                carregarUsuarios(); // Recarrega a lista
            } else {
                exibirMensagem(`Falha ao excluir usuário: ${data.message}`, 'danger');
            }
        } catch (error) {
            console.error('Erro na exclusão:', error);
            exibirMensagem('Erro de conexão ao tentar excluir usuário.', 'danger');
        }
    }

    // =======================================================
    // INICIALIZAÇÃO E EVENTOS
    // =======================================================

    // Inicializa o carregamento da lista
    carregarUsuarios();
    
    // Evento de Busca
    let timeoutBusca = null;
    campoBusca.addEventListener('keyup', (e) => {
        clearTimeout(timeoutBusca);
        // Espera 300ms após a digitação para buscar, evitando muitas requisições
        timeoutBusca = setTimeout(() => {
            carregarUsuarios(e.target.value.trim());
        }, 300);
    });

    // Garante que a senha está visível e obrigatória ao iniciar
    limparFormulario();
});