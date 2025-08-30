<?php
class OrigenCliente {
    private $conn;
    private $table_name = "acciones";
    private $principal_relation = "clientes";
    private $secondary_relation = "detalle_origen";

    public $id; // VALOR
    public $nombre;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * @return array
     */
    public function getAll() {
        $sql = "SELECT 
            a.valor AS id,
            a.accion AS nombre
        FROM ".$this->table_name." a
        WHERE a.categoria = '".$this->secondary_relation."'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * @return string
     */
    public function getByNameOrId($value) {
        $posibles_valores = ["sistema", "wasap","resmine"];
        $sql = "SELECT 
            a.valor AS id,
            a.accion AS nombre
        FROM ".$this->table_name." a
        WHERE a.categoria = '".$this->secondary_relation."'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}