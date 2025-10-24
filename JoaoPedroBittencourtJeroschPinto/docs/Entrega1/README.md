# ENTREGA 01 - REQUISITOS FUNCIONAIS
## Sistema de Gestão de Estoque - SAEP

---

## 1. INTRODUÇÃO

Este documento descreve os requisitos funcionais do Sistema de Avaliação de Estoque Profissional (SAEP), desenvolvido para atender às necessidades de gestão de estoque de uma fabricante de ferramentas e equipamentos manuais.

### 1.1 Objetivo do Sistema
Desenvolver um sistema web que permita ao usuário do almoxarifado:
- Cadastrar e gerenciar produtos
- Controlar entradas e saídas de estoque
- Receber alertas automáticos de estoque mínimo
- Manter rastreabilidade completa das movimentações
- Registrar custos históricos para auditoria

---

## 2. REQUISITOS FUNCIONAIS

### RF01 - Autenticação de Usuários
**Descrição:** O sistema deve permitir a autenticação de usuários através de login com e-mail e senha.

**Critérios de Aceitação:**
- O sistema deve validar as credenciais fornecidas (e-mail e senha)
- Em caso de falha na autenticação, exibir mensagem clara indicando o motivo (e-mail ou senha incorretos)
- Após falha, redirecionar o usuário novamente para a tela de login
- Implementar validação de formato de e-mail
- Não permitir campos vazios no envio do formulário
- Senhas devem ser armazenadas com criptografia (bcrypt)

**Regras de Negócio:**
- Existem dois níveis de acesso: `admin` e `estoquista`
- O e-mail deve ser único no sistema
- A sessão deve expirar após logout ou fechamento do navegador

---

### RF02 - Interface Principal (Dashboard)
**Descrição:** O sistema deve exibir uma interface principal após login bem-sucedido.

**Critérios de Aceitação:**
- Exibir o nome do usuário logado no cabeçalho
- Exibir o nível de acesso do usuário (Admin/Estoquista)
- Implementar botão de Logout funcional que:
  - Encerra a sessão do usuário
  - Redireciona para a tela de login
  - Limpa todos os dados da sessão
- Exibir estatísticas do sistema:
  - Total de produtos cadastrados
  - Produtos com estoque baixo
  - Movimentações recentes (últimos 30 dias)
  - Total de usuários (apenas para admin)

**Regras de Negócio:**
- Apenas usuários autenticados podem acessar o dashboard
- Estatísticas devem ser calculadas dinamicamente do banco de dados
- Se houver produtos com estoque baixo, exibir alerta visual destacado

---

### RF03 - Navegação do Sistema
**Descrição:** O sistema deve possibilitar navegação entre os módulos principais.

**Critérios de Aceitação:**
- Fornecer acesso à interface "Cadastro de Produto"
- Fornecer acesso à interface "Gestão de Estoque"
- Fornecer acesso à interface "Cadastro de Usuários" (apenas para admin)
- Cada módulo deve permitir retorno ao Dashboard
- Interface responsiva e intuitiva

**Regras de Negócio:**
- Navegação por cards ou menu claro
- Controle de acesso baseado no nível do usuário
- Estoquistas NÃO podem acessar Cadastro de Usuários

---

### RF04 - Listagem de Produtos
**Descrição:** O sistema deve listar todos os produtos cadastrados automaticamente ao acessar a interface de Cadastro de Produto.

**Critérios de Aceitação:**
- Exibir produtos em formato de tabela
- Incluir as seguintes colunas:
  - ID do Produto
  - Nome
  - Categoria
  - Valor Unitário (formatado em R$)
  - Quantidade em Estoque
  - Estoque Mínimo
  - Status (badge visual: OK / Baixo)
  - Ações (botões Editar/Excluir)
- Carregamento automático ao abrir a página
- Ordenação alfabética por nome (no módulo de estoque)
- Exibir mensagem caso não haja produtos cadastrados

**Regras de Negócio:**
- Status "Baixo" quando quantidade_estoque ≤ estoque_minimo
- Valores monetários devem usar formato brasileiro (R$ 1.234,56)
- ID dos produtos são auto-incrementados

---

### RF05 - Busca de Produtos
**Descrição:** O sistema deve implementar campo de busca para filtrar produtos cadastrados.

**Critérios de Aceitação:**
- Campo de busca visível e acessível
- Busca deve filtrar por:
  - Nome do produto (parcial ou completo)
  - Categoria
  - Material
- Atualização dinâmica da tabela conforme digitação
- Ignorar diferenças entre maiúsculas/minúsculas
- Exibir mensagem quando não houver resultados

**Regras de Negócio:**
- Busca com mínimo de 1 caractere
- Aplicar filtro no lado do servidor (segurança)
- Limpar busca deve retornar listagem completa

---

### RF06 - Cadastro de Novo Produto
**Descrição:** O sistema deve permitir a inserção de novos produtos no banco de dados.

**Critérios de Aceitação:**
- Formulário com os seguintes campos:
  - **Nome*** (obrigatório, texto até 100 caracteres)
  - **Categoria*** (obrigatório, seleção: Martelos, Chaves, Alicates, Medição, Outros)
  - Material (opcional, texto até 50 caracteres)
  - Tamanho em cm (opcional, decimal)
  - Peso em kg (opcional, decimal)
  - **Valor Unitário*** (obrigatório, decimal > 0, formato R$)
  - **Quantidade em Estoque*** (obrigatório, inteiro ≥ 0, somente leitura no cadastro)
  - **Estoque Mínimo*** (obrigatório, inteiro > 0)
  - Descrição (opcional, texto longo)
- Aplicar máscaras monetárias nos campos de valor
- Botões "Salvar" e "Cancelar"
- Exibir mensagem de sucesso/erro após operação

**Regras de Negócio:**
- Quantidade em estoque inicial é sempre 0 (controlada apenas por movimentações)
- Valor unitário serve de referência para cálculo de custos
- Apenas usuários `admin` e `estoquista` podem cadastrar produtos
- Dados devem ser validados no backend

---

### RF07 - Edição de Produto
**Descrição:** O sistema deve permitir a edição de dados de produtos existentes.

**Critérios de Aceitação:**
- Ao clicar em "Editar", carregar dados do produto no formulário
- Todos os campos editáveis (exceto quantidade_estoque)
- Alterar título do formulário para "Editar Produto"
- Validações idênticas ao cadastro
- Confirmar alterações antes de salvar
- Exibir mensagem de sucesso/erro

**Regras de Negócio:**
- Apenas `admin` e `estoquista` podem editar
- Quantidade em estoque NÃO pode ser editada diretamente (apenas via movimentações)
- ID do produto não pode ser alterado
- Validações devem impedir dados inconsistentes

---

### RF08 - Exclusão de Produto
**Descrição:** O sistema deve permitir a exclusão de produtos do banco de dados.

**Critérios de Aceitação:**
- Botão "Excluir" na listagem de produtos
- Exibir confirmação antes de excluir: "Deseja realmente excluir o produto [NOME]?"
- Mensagem de sucesso após exclusão
- Atualizar listagem automaticamente
- Impedir exclusão se houver movimentações associadas

**Regras de Negócio:**
- Apenas usuários `admin` podem excluir produtos
- Não permitir exclusão de produtos com histórico de movimentações (integridade referencial)
- Exibir mensagem clara explicando impedimento se houver movimentações
- Exclusão é permanente (sem soft delete)

---

### RF09 - Validações de Dados de Produto
**Descrição:** O sistema deve validar todos os dados inseridos ou editados nos formulários de produto.

**Critérios de Aceitação:**
- Validações implementadas:
  - Nome: obrigatório, não vazio
  - Categoria: obrigatório, valor válido do enum
  - Quantidade em estoque: inteiro ≥ 0
  - Estoque mínimo: inteiro > 0
  - Tamanho: decimal ≥ 0 (se preenchido)
  - Peso: decimal ≥ 0 (se preenchido)
  - Valor unitário: decimal > 0, obrigatório
- Exibir alertas específicos para cada tipo de erro
- Destacar visualmente campos com erro
- Impedir envio do formulário enquanto houver erros
- Validações no frontend (UX) e backend (segurança)

**Regras de Negócio:**
- Mensagens de erro devem ser claras e específicas
- Validações de formato monetário (brasileiro)
- Aceitar entrada com vírgula como separador decimal

---

### RF10 - Listagem Ordenada para Gestão de Estoque
**Descrição:** O sistema deve listar produtos na interface de Gestão de Estoque em ordem alfabética.

**Critérios de Aceitação:**
- Produtos exibidos em ordem alfabética por nome
- Implementar algoritmo de ordenação (Bubble Sort)
- Exibir em cards ou lista clicável
- Cada item deve mostrar:
  - Nome do produto
  - Categoria
  - Quantidade atual em estoque
  - Badge de status (OK/Estoque Baixo)
- Carregar automaticamente ao abrir a página

**Regras de Negócio:**
- Algoritmo de ordenação deve ser implementado no frontend
- Ignorar maiúsculas/minúsculas na ordenação
- Destacar visualmente produtos com estoque baixo

---

### RF11 - Seleção de Produto para Movimentação
**Descrição:** O sistema deve permitir a seleção de um produto e escolha do tipo de operação (entrada ou saída).

**Critérios de Aceitação:**
- Permitir clique/seleção de produto na lista
- Destacar visualmente o produto selecionado
- Exibir formulário de movimentação após seleção
- Formulário com opções:
  - Tipo: Radio buttons para "Entrada" ou "Saída"
  - Ícones visuais diferenciando entrada (⬆️) e saída (⬇️)
- Exibir informações detalhadas do produto selecionado:
  - Estoque atual
  - Estoque mínimo
  - Valor unitário
  - Material, tamanho, peso (se aplicável)

**Regras de Negócio:**
- Apenas um produto pode ser selecionado por vez
- Tipo de movimentação é obrigatório
- Desabilitar formulário até que um produto seja selecionado

---

### RF12 - Data da Movimentação
**Descrição:** O sistema deve permitir que o usuário insira a data exata da movimentação.

**Critérios de Aceitação:**
- Campo de data (input type="date")
- Data atual como valor padrão
- Permitir seleção de datas passadas ou presente
- Não permitir datas futuras
- Validar formato de data
- Campo obrigatório

**Regras de Negócio:**
- Data não pode ser futura
- Formato aceito: YYYY-MM-DD (padrão SQL)
- Data deve ser registrada junto com a movimentação

---

### RF13 - Registro de Movimentação com Rastreabilidade
**Descrição:** O sistema deve registrar o histórico completo de cada movimentação, incluindo cálculo de custo total.

**Critérios de Aceitação:**
- Formulário de movimentação com campos:
  - Produto selecionado (readonly)
  - Tipo (entrada/saída)
  - **Quantidade*** (inteiro > 0)
  - **Valor Total** (calculado automaticamente: quantidade × valor_unitario)
  - **Data da movimentação*** (data válida)
  - Observação (texto livre para detalhes)
- Ao registrar, salvar na tabela `movimentacoes`:
  - id_produto
  - id_usuario (usuário logado)
  - tipo_movimentacao
  - quantidade
  - **valor_total** (campo crítico para auditoria)
  - data_movimentacao
  - observacao
  - data_registro (timestamp automático)
- Atualizar `quantidade_estoque` do produto:
  - **Entrada:** quantidade_estoque + quantidade
  - **Saída:** quantidade_estoque - quantidade
- Exibir histórico das últimas 20 movimentações
- Histórico deve mostrar:
  - Tipo e ícone (⬆️/⬇️)
  - Nome do produto
  - Quantidade
  - **Valor total** (formatado em R$)
  - Data da movimentação
  - Responsável (nome do usuário)
  - Observação (se houver)
  - Data/hora do registro

**Regras de Negócio:**
- Valor total é calculado e armazenado permanentemente (não recalculado depois)
- Impedir saída se quantidade solicitada > estoque atual
- Transação deve ser atômica (tudo ou nada)
- Em caso de erro, fazer rollback
- Observação pode ser usada para justificativas ou detalhes

---

### RF14 - Alerta de Estoque Mínimo
**Descrição:** O sistema deve verificar automaticamente e emitir alerta quando o estoque ficar abaixo do mínimo após uma saída.

**Critérios de Aceitação:**
- Verificação automática a cada movimentação de **saída**
- Condição de alerta: `novo_estoque ≤ estoque_minimo`
- Exibir alerta visual destacado contendo:
  - Ícone de aviso (⚠️)
  - Nome do produto
  - Estoque atual
  - Estoque mínimo configurado
  - Mensagem: "É recomendado realizar reposição"
- Alerta deve aparecer imediatamente após o registro
- Cores de destaque (laranja/amarelo)
- Alertas devem permanecer visíveis por tempo adequado

**Regras de Negócio:**
- Alerta só para movimentações de **saída**
- Movimentações de entrada não disparam alerta
- Alerta não impede a operação, apenas informa
- No dashboard, exibir card com contagem de produtos em estoque baixo
- Listar produtos críticos em tabela específica no dashboard

---

## 3. REQUISITOS NÃO FUNCIONAIS

### RNF01 - Segurança
- Senhas criptografadas com bcrypt
- Proteção contra SQL Injection (uso de Prepared Statements)
- Proteção contra XSS (sanitização de entradas)
- Sessões seguras com timeout
- Controle de acesso baseado em níveis (RBAC)

### RNF02 - Performance
- Carregamento de páginas em menos de 2 segundos
- Consultas ao banco otimizadas com índices
- Cache de sessão

### RNF03 - Usabilidade
- Interface responsiva (desktop e mobile)
- Mensagens de feedback claras
- Confirmações para ações destrutivas
- Atalhos visuais (badges, ícones, cores)

### RNF04 - Manutenibilidade
- Código organizado em módulos (MVC-like)
- Comentários em código complexo
- Nomenclatura clara de variáveis e funções
- Separação entre lógica de negócio e apresentação

---

## 4. REGRAS DE ACESSO POR NÍVEL

| Funcionalidade | Admin | Estoquista |
|---|---|---|
| Login | ✅ | ✅ |
| Visualizar Dashboard | ✅ | ✅ |
| Listar Produtos | ✅ | ✅ |
| Buscar Produtos | ✅ | ✅ |
| Cadastrar Produto | ✅ | ✅ |
| Editar Produto | ✅ | ✅ |
| Excluir Produto | ✅ | ❌ |
| Registrar Movimentação | ✅ | ✅ |
| Visualizar Histórico | ✅ | ✅ |
| Cadastrar Usuários | ✅ | ❌ |
| Editar Usuários | ✅ | ❌ |
| Excluir Usuários | ✅ | ❌ |

---