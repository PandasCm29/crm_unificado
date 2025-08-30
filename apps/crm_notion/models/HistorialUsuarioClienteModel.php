<?php
class HistorialUsuarioCliente {
    private $conn;
    private $table_name = "historial_status";
    private $principal_relation = "usuarios";
    private $secondary_relation;

    public $id;
    public $usuario;
    public $fecha;
    public $descripcion;

    public function __construct(PDO $db, string $secondary_relation = "clientes") {
        $this->conn               = $db;
        $this->secondary_relation = $secondary_relation;
    }

    /**
     * @return array
     */
    public function getById(int $idtabla, int $idUsuarioSesion): array {
        $sql = "SELECT 
            hs.idstatus AS id,
            CASE
                WHEN u.idusuario = :idSesion THEN 'Tú'
                WHEN u.nombres IS NULL OR u.apellidos IS NULL THEN 'Usuario Inactivo o Eliminado'
                ELSE CONCAT(u.nombres, ' ', u.apellidos)
            END AS usuario,
            hs.fechaingreso AS fecha,
            hs.status       AS descripcion
        FROM " . $this->table_name . " hs 
        LEFT JOIN ".$this->principal_relation." u
          ON hs.idusuario = u.idusuario
        WHERE hs.idtabla = :idtabla
          AND hs.tabla   = :tabla
        ORDER BY hs.fechaingreso DESC";
        
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idtabla', $idtabla, PDO::PARAM_INT);
        $stmt->bindValue(':tabla',   $this->secondary_relation, PDO::PARAM_STR);
        $stmt->bindValue(':idSesion', $idUsuarioSesion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     /**
     * Inserta un status nuevo si no existe
     * @param string $status — no nulo
     * @param string $idcliente — no nulo
     * @param string $idusuario — no nulo
     * @return array  true si OK, mensaje de error si falla
     */
    public function crear(int $idtabla, int $idusuario, string $status): array {
            $fechaingreso = date('Y-m-d H:i:s');
            $sql = "INSERT INTO {$this->table_name}
                    (tabla, idtabla, idusuario, fechaingreso, status)
                    VALUES (:tabla, :idtabla, :idusuario, :fechaingreso, :status)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':tabla',        $this->secondary_relation);
            $stmt->bindValue(':idtabla',      $idtabla);
            $stmt->bindValue(':idusuario',    $idusuario);
            $stmt->bindValue(':fechaingreso', $fechaingreso);
            $stmt->bindValue(':status',       $status);
            if (!$stmt->execute()) {
                return ['success'=>false,'message'=>'Error al insertar en la base de datos'];
            }

                $newId = (int)$this->conn->lastInsertId();

            // Busco nombre completo
            $q = "SELECT nombres, apellidos FROM {$this->principal_relation}
                WHERE idusuario = :idusuario";
            $su = $this->conn->prepare($q);
            $su->bindValue(':idusuario', $idusuario);
            $su->execute();
            $ud = $su->fetch(PDO::FETCH_ASSOC);
            $nombre   = $ud ? "{$ud['nombres']} {$ud['apellidos']}" : 'Usuario desconocido';
            return [
                'success' => true,
                'message' => 'Historial creado correctamente',
                'data'    => [
                    'idstatus'    => $newId,
                    'usuario'     => $nombre,
                    'fecha'       => $fechaingreso,
                    'descripcion' => $status
                ]
            ];
        }

    public function actualizar(int $idstatus, string $status): array {
        $sql = "UPDATE {$this->table_name}
                SET status = :status
                WHERE idstatus = :idstatus";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':idstatus', $idstatus, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return ['success'=>false, 'message'=>'Error al actualizar en la base de datos'];
        }
        return ['success'=>true, 'message'=>'Comentario actualizado correctamente', 'data'=>['idstatus'=>$idstatus,'status'=>$status]];
    }

    /**
     * Elimina un comentario del historial
     */
    public function eliminar(int $idstatus): array {
        $sql = "DELETE FROM {$this->table_name}
                WHERE idstatus = :idstatus";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idstatus', $idstatus, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return ['success'=>false, 'message'=>'Error al eliminar en la base de datos'];
        }
        return ['success'=>true, 'message'=>'Comentario eliminado correctamente', 'data'=>['idstatus'=>$idstatus]];
    }

        public function clonarHistorial(int $fromId, int $toId): array {
        // 1) Obtener todos los historiales del registro original
        $sql = "
          SELECT idusuario, fechaingreso, status 
          FROM {$this->table_name}
          WHERE tabla = :tabla AND idtabla = :idtabla
          ORDER BY fechaingreso ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':tabla',   $this->secondary_relation, PDO::PARAM_STR);
        $stmt->bindValue(':idtabla', $fromId,                  PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return ['success'=>true,'message'=>'No había historial para clonar'];
        }

        // 2) Insertar cada fila adaptando idtabla = $toId
        $insSql = "
          INSERT INTO {$this->table_name}
            (tabla, idtabla, idusuario, fechaingreso, status)
          VALUES
            (:tabla, :idtabla, :idusuario, :fechaingreso, :status)
        ";
        $ins = $this->conn->prepare($insSql);
        foreach ($rows as $row) {
            $ins->bindValue(':tabla',         $this->secondary_relation, PDO::PARAM_STR);
            $ins->bindValue(':idtabla',       $toId,                      PDO::PARAM_INT);
            $ins->bindValue(':idusuario',     $row['idusuario'],         PDO::PARAM_INT);
            $ins->bindValue(':fechaingreso',  $row['fechaingreso']);
            $ins->bindValue(':status',        $row['status']);
            if (!$ins->execute()) {
                return ['success'=>false,'message'=>'Error al clonar historial'];
            }
        }

        return ['success'=>true,'message'=>count($rows).' ítems de historial clonados'];
    }
}
