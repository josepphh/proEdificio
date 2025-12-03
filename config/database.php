<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "edificios_db";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Crear conexión mysqli
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Verificar si hay error de conexión
            if ($this->conn->connect_error) {
                error_log("Error de conexión MySQL: " . $this->conn->connect_error);
                return null;
            }
            
            // Establecer charset UTF-8
            if (!$this->conn->set_charset("utf8mb4")) {
                error_log("Error al establecer charset: " . $this->conn->error);
            }
            
        } catch (Exception $e) {
            error_log("Excepción en conexión: " . $e->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}
?>