<?php
class AntecedentesCliente {
    private $conn;
    private $table_name = "antecedentes_clientes";
    private $principal_relation = "usuarios";
    private $secondary_relation = "clientes";

    public $id;
    public $usuario;
    public $fecha;
    public $descripcion;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Obtiene los antecedentes de un cliente por su ID
     * @param int $idcliente
     * @return array
     */
    public function getById($idcliente, int $idUsuarioSesion) {
        $sql = "SELECT 
            ac.idantecedente AS id,
            CASE
                WHEN u.idusuario = :idSesion THEN 'Tú'
                WHEN u.nombres IS NULL OR u.apellidos IS NULL THEN 'Usuario Inactivo o Eliminado'
                ELSE CONCAT(u.nombres, ' ', u.apellidos)
            END AS usuario,
            ac.fechaingreso AS fecha, 
            ac.status       AS descripcion
        FROM " . $this->table_name . " ac
        LEFT JOIN " . $this->principal_relation . " u ON ac.idusuario = u.idusuario
        WHERE ac.idtabla = :idcliente AND ac.tabla = :tabla
        ORDER BY ac.fechaingreso DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idcliente', $idcliente, PDO::PARAM_INT);
        $stmt->bindValue(':tabla',   $this->secondary_relation, PDO::PARAM_STR);
        $stmt->bindValue(':idSesion', $idUsuarioSesion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo antecedente si no existe
     * @param int $idcliente — ID del cliente, no nulo
     * @param int $idusuario — ID del usuario, no nulo
     * @param string $status — Texto del antecedente, no nulo
     * @return array — true si OK, mensaje de error si falla
     */
    public function crear($idcliente, $idusuario, $status) {
        date_default_timezone_set('America/Lima');
        $fechaingreso = date('Y-m-d H:i:s');
        $tabla = $this->secondary_relation;

        $sql = "INSERT INTO {$this->table_name} (tabla, idtabla, idusuario, fechaingreso, status)
                VALUES (:tabla, :idcliente, :idusuario, :fechaingreso, :status)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
        $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
        $stmt->bindParam(':fechaingreso', $fechaingreso, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Obtener nombre del usuario
            $queryUser = "SELECT nombres, apellidos FROM {$this->principal_relation} WHERE idusuario = :idusuario";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
            $stmtUser->execute();
            $usuarioData = $stmtUser->fetch(PDO::FETCH_ASSOC);

            $nombreCompleto = $usuarioData ? $usuarioData['nombres'] . ' ' . $usuarioData['apellidos'] : 'Usuario desconocido';

            return [
                'success' => true,
                'message' => 'Antecedente creado correctamente',
                'data' => [
                    'usuario' => $nombreCompleto,
                    'fecha' => $fechaingreso,
                    'descripcion' => $status
                ]
            ];
        }
        return [
            'success' => false,
            'message' => 'Error al insertar en la base de datos'
        ];
    }
}
?>