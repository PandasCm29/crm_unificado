<?php
class Distrito {
    private $conn;
    private $table_name = "distrito";

    public $id;
    public $distrito;
    public $codigo_postal;
    public $status;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * @return array
     */
    public function obtenerTodos() {
        $sql = "SELECT id, distrito, Codigo_Postal AS codigo_postal 
                FROM {$this->table_name} 
                WHERE status = 1
                ORDER BY distrito";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     /**
     * Inserta un distrito nuevo si no existe
     * @param string $nombre
     * @param string $codigo_postal — no nulo
     * @return bool|string  true si OK, mensaje de error si falla
     */
    public function crear($nombre, $codigo_postal) {
        $nombre = trim($nombre);
        $codigo_postal = trim($codigo_postal);

        if ($nombre === "") {
            return "El nombre del distrito no puede estar vacío";
        }
        if ($codigo_postal === "") {
            $codigo_postal = "";
        }

        $check = $this->conn->prepare(
            "SELECT COUNT(*) FROM {$this->table_name}
             WHERE LOWER(distrito) = LOWER(:distrito)"
        );
        $check->execute([':distrito' => $nombre]);
        if ($check->fetchColumn() > 0) {
            return "Ese distrito ya existe";
        }

        // Insert 
        $sql = "INSERT INTO {$this->table_name} 
                (distrito, Codigo_Postal, status)
                VALUES (:distrito, :cp, '1')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':distrito', $nombre);
        $stmt->bindParam(':cp',      $codigo_postal);

        if ($stmt->execute()) {
            return true;
        }
        return "Error al insertar en la base de datos";
    }
}