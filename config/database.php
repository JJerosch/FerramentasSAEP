<?php
/**
 * Configuração de Conexão com Banco de Dados
 * Sistema de Gestão de Estoque - SAEP
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'saep_db');

// Classe de Conexão com Banco de Dados
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    
    /**
     * Estabelece conexão com o banco de dados
     * @return mysqli|null
     */
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("Erro na conexão: " . $this->conn->connect_error);
            }
            
            // Define charset para UTF-8
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
        
        return $this->conn;
    }
    
    /**
     * Fecha a conexão com o banco de dados
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

/**
 * Função auxiliar para obter conexão
 * @return mysqli
 */
function getConnection() {
    $database = new Database();
    return $database->connect();
}

/**
 * Função para executar queries preparadas com segurança
 * @param mysqli $conn Conexão com o banco
 * @param string $sql Query SQL
 * @param array $params Parâmetros da query
 * @param string $types Tipos dos parâmetros (s=string, i=integer, d=double)
 * @return mysqli_stmt|false
 */
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    return $stmt;
}
?>