<?php
require_once __DIR__.'/config.php'; // Cargar variables de entorno
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Asignar las variables de entorno a las propiedades
        $this->host = $_ENV['DB_HOST']?? "localhost";
        $this->db_name = $_ENV['DB_NAME']?? "compihost_CMR_CURRENT";
        $this->username = $_ENV['DB_USER']?? "compihost_mautic";
        $this->password = $_ENV['DB_PASS']?? "Compi.host/23-adm";
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
            // Verificar conexión
            if($this->conn) {
                // echo "Conexión exitosa a la base de datos";
            }
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
    // Métodos para obtener las credenciales de Notion (opcional)
    public static function getNotionToken() {
        return $_ENV['NOTION_TOKEN'];
    }

    public static function getClientesNotionDatabaseId() {
        return $_ENV['NOTION_DATABASE_ID_CLIENTES'];
    }

    public static function getUsuariosNotionDatabaseId() {
        return $_ENV['NOTION_DATABASE_ID_USUARIOS'];
    }
    public static function getNotionVersion() {
        return $_ENV['NOTION_VERSION'];
    }
    public static function getNotionUsuariosDatabaseId()
    {
    return 'TU_ID_DE_DATABASE_DE_USUARIOS_EN_NOTION';
    }


}
?>