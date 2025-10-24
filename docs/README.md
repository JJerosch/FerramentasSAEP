# FerramentasSAEP

Estes são os dados estruturados para preencher o seu arquivo `README.md`, cobrindo a descrição, as funcionalidades e os requisitos técnicos do seu projeto SAEP.

---

# SAEP - Sistema de Avaliação de Estoque Profissional

## Descrição do Projeto

[cite_start]O SAEP (Sistema de Avaliação de Estoque Profissional) é uma solução web desenvolvida para atender aos desafios críticos na gestão de estoque de uma fabricante de ferramentas e equipamentos manuais[cite: 124]. [cite_start]O sistema informatizado foi criado para controlar de forma intuitiva a entrada e saída de materiais, combatendo a falta e o excesso de produtos no almoxarifado[cite: 124, 128].

[cite_start]A principal característica é a rastreabilidade completa das movimentações, com alertas automáticos para gerenciamento de estoque mínimo e registro de custos históricos para fins de integridade e auditoria[cite: 133, 134].

## Funcionalidades Principais

[cite_start]O sistema foi estruturado em módulos claros para atender aos requisitos definidos[cite: 136, 153, 160, 173]:

### Módulo de Autenticação
* [cite_start]**Login e Logout:** Permite a autenticação de usuários (Admin/Estoquista) e o encerramento seguro da sessão[cite: 151, 155].
* [cite_start]**Exibição de Usuário:** Exibe o nome do usuário atualmente logado na interface principal[cite: 154].

### Módulo de Cadastro de Produtos
* [cite_start]**CRUD Completo:** Permite a Criação, Leitura (listagem), Edição e Exclusão de produtos no banco de dados[cite: 166, 167, 168].
* [cite_start]**Listagem Dinâmica:** Exibe os produtos cadastrados em uma tabela, carregada automaticamente e com opção de busca[cite: 163, 165].
* [cite_start]**Validação de Dados:** Implementa validações para garantir a integridade dos dados, exibindo alertas em caso de campos ausentes ou inválidos[cite: 170].

### Módulo de Gestão de Estoque
* [cite_start]**Listagem Ordenada:** Lista os produtos em ordem alfabética para facilitar a seleção[cite: 175].
* [cite_start]**Registro de Movimentação:** Permite registrar operações de **Entrada** e **Saída** de estoque[cite: 177].
* [cite_start]**Rastreabilidade Total:** Cada movimentação registra a quantidade, o tipo, o responsável (usuário) e o **Valor Total** da transação para garantir a integridade histórica dos custos[cite: 134].
* [cite_start]**Alerta de Estoque Mínimo:** Dispara um alerta automático após uma movimentação de **Saída** se o nível de estoque ficar abaixo do limite mínimo configurado[cite: 180, 181].
* [cite_start]**Registro Temporal:** Permite o registro da data exata da movimentação (passado ou presente)[cite: 178].

## Tecnologias Utilizadas

| Categoria | Tecnologia | Versão Mínima |
| :--- | :--- | :--- |
| **Backend/API** | PHP | [cite_start]7.4+ [cite: 193] |
| **Banco de Dados (SGBD)** | MySQL/MariaDB | [cite_start]5.7+ [cite: 192] |
| **Frontend** | HTML5, CSS3, JavaScript | N/A |
| **Servidor Web** | Apache HTTP Server | N/A |
| **Ambiente de Desenvolvimento**| XAMPP, WAMP ou Laragon | N/A |

## Estrutura do Banco de Dados

O projeto utiliza um banco de dados relacional chamado `saep_db` com três tabelas principais:

1.  **`usuarios`**: Armazena dados de login, nome e `nivel_acesso` (`admin` ou `estoquista`).
2.  **`produtos`**: Contém detalhes do produto, incluindo `quantidade_estoque`, `estoque_minimo` e o crucial `valor_unitario`.
3.  **`movimentacoes`**: Tabela de rastreabilidade (histórico), ligada a `produtos` e `usuarios`.
    * **Ponto Chave:** Contém a coluna **`valor_total`** (`DECIMAL(10, 2)`), que garante a integridade dos custos históricos no momento da transação.

## Instalação e Configuração

Siga os passos para configurar e executar o projeto em seu ambiente local:

### 1. Requisitos Prévios
Certifique-se de ter um ambiente de desenvolvimento web (como XAMPP, WAMP ou Laragon) instalado, com suporte a PHP 7.4+ e MySQL/MariaDB.

### 2. Configuração do Código
1.  Clone ou baixe o repositório do projeto para a pasta do seu servidor web (ex: `htdocs` no XAMPP).
2.  Ajuste as credenciais de conexão com o banco de dados no arquivo **`config/database.php`** (caminho relativo assumido com base no `require_once` do `estoque_api.php`).

### 3. Configuração do Banco de Dados
1.  Acesse seu gerenciador de banco de dados (ex: PHPMyAdmin).
2.  [cite_start]Crie o banco de dados com o nome **`saep_db`**[cite: 144].
3.  [cite_start]Execute o script SQL fornecido (**`saep_db_script.sql`** ou similar) para criar as tabelas e popular os registros iniciais[cite: 146].

### 4. Execução
1.  Inicie o servidor Apache e MySQL.
2.  Acesse o projeto no seu navegador (ex: `http://localhost/seu-projeto-saep/`).
3.  Utilize as seguintes credenciais de teste (conforme script SQL de população):
    * **Admin:** `maria.santos@empresa.com` / Senha: `123456`
    * **Estoquista:** `joao.silva@empresa.com` / Senha: `123456`