<?php 
require_once 'HistorialUsuarioClienteModel.php';
class Requerimiento {
    private $conn;
    private $table_name = "consultas";
    // public $idcliente;
    public $idconsulta;
    public $urlpagina;
    public $nombres;
    public $apellidos;
    public $empresa;
    public $historial;
    public $email;
    public $direccion;
    public $telefono;
    public $celular;
    public $celular_consulta;
    public $nextel;
    public $asunto;
    public $archivo;
    public $ip;
    public $fecha_consulta;
    public $estatus;
    public $prioridad;
    public $origen;
    public $detalle_origen;
    public $fechaaviso;
    public $idusuario;// ID del usuario de sesión
    public $estadousuario;
    public $fatencion;
    public $statusbh;
    public $status = 1;
    public $derivado;
    public $paginaWeb;
    
    public function __construct($db, int $idusuario) {
    $this->conn               = $db;
    $this->idusuario    = $idusuario;
    }
    public function reset() {
        $excluir = ['conn', 'table_name', 'status', 'idusuario'];
        foreach (get_object_vars($this) as $prop => $value) {
            if (in_array($prop, $excluir)) {
                continue;
            }
            $this->$prop = '';
        }
    }

        public function readPageConsultas(int $page = 1, int $limit = 10): array {
    $offset = ($page - 1) * $limit;
    
    $query = "SELECT 
                c.idconsulta, 
                c.idusuario,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                c.fecha_consulta,
                c.empresa,
                c.asunto,
                c.derivado,
                c.status,
                hs.status as historial_status,
                c.nombres,
                c.apellidos,
                c.fechaaviso,
                c.fatencion,
                c.email,
                c.direccion,
                c.telefono,
                c.celular,
                c.archivo,
                c.urlpagina 
              FROM consultas c
              LEFT JOIN usuarios u ON c.idusuario = u.idusuario
              LEFT JOIN (
                      SELECT * FROM (
                          SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
                          FROM historial_status
                      ) hs_sub WHERE rn = 1
                  ) hs ON c.idconsulta= hs.idtabla
              WHERE c.status != 4              
              ORDER BY c.idconsulta DESC
              LIMIT :limit OFFSET :offset";

    
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
         public function getByIdConsulta(int $idconsulta): ?object {
        // 1) Traer la consulta
        $sql = "
          SELECT 
            c.idconsulta,
            c.idusuario,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            c.fecha_consulta,
            c.empresa,
            c.asunto,
            c.derivado,
            c.status,
            c.prioridad,
            c.detalle_origen,
            c.nombres,
            c.apellidos,
            c.fechaaviso,
            c.fatencion,
            c.email,
            c.direccion,
            c.telefono,
            c.celular as celular_consulta,
            c.archivo,
            c.urlpagina
          FROM consultas c
          LEFT JOIN usuarios u 
            ON c.idusuario = u.idusuario
          WHERE c.idconsulta = :idconsulta
          LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idconsulta', $idconsulta, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        foreach ($row as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
        $histModel = new HistorialUsuarioCliente($this->conn, $this->table_name);
        $this->historial = $histModel->getById($idconsulta, $this->idusuario);
        return $this;
    }

    public function updateConsulta(int $idconsulta): array {
        date_default_timezone_set('America/Lima');
        $fecha_actual = date('Y-m-d H:i:s');

        // Verifica fechaaviso
        $sqlCheck = "SELECT fechaaviso FROM consultas WHERE idconsulta = :idconsulta";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':idconsulta', $idconsulta, PDO::PARAM_INT);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $yatienefaviso = !empty($result['fechaaviso']);

        $sql = "
            UPDATE consultas SET
                fecha_consulta   = :fecha_consulta,
                empresa          = :empresa,
                asunto           = :asunto,
                derivado         = :derivado,
                status           = :status,
                prioridad        = :prioridad,
                detalle_origen   = :detalle_origen,
                nombres          = :nombres,
                apellidos        = :apellidos,
                fatencion        = :fatencion,
                email            = :email,
                direccion        = :direccion,
                telefono         = :telefono,
                celular          = :celular_consulta";

        // Solo actualiza fechaaviso si no tiene valor aún
        if (!$yatienefaviso) {
            $sql .= ", fechaaviso = :fechaaviso";
        }

        $sql .= " WHERE idconsulta = :idconsulta";

        $stmt = $this->conn->prepare($sql);

        // Bind comunes
        $stmt->bindValue(':fecha_consulta',  $this->fecha_consulta);
        $stmt->bindValue(':empresa',         $this->empresa);
        $stmt->bindValue(':asunto',          $this->asunto);
        $stmt->bindValue(':derivado',        $this->derivado);
        $stmt->bindValue(':status',          $this->status);
        $stmt->bindValue(':prioridad',       $this->prioridad);
        $stmt->bindValue(':detalle_origen',  $this->detalle_origen);
        $stmt->bindValue(':nombres',         $this->nombres);
        $stmt->bindValue(':apellidos',       $this->apellidos);
        $stmt->bindValue(':fatencion',       $fecha_actual); // SIEMPRE se actualiza
        $stmt->bindValue(':email',           $this->email);
        $stmt->bindValue(':direccion',       $this->direccion);
        $stmt->bindValue(':telefono',        $this->telefono);
        $stmt->bindValue(':celular_consulta',$this->celular_consulta);
        $stmt->bindValue(':idconsulta',      $idconsulta, PDO::PARAM_INT);

        if (!$yatienefaviso) {
            $stmt->bindValue(':fechaaviso', $fecha_actual);
        }
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Requerimiento actualizado correctamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar requerimiento'];
    }
    public function cloneAndUpdate(int $idconsulta, array $input): array {
         try {
            $this->conn->beginTransaction();

             // 1) Obtener el requerimiento original
            $stmt = $this->conn->prepare("
            SELECT urlpagina, nombres, apellidos, empresa, email, direccion, telefono, celular,
                   nextel, asunto, archivo, ip, fecha_consulta, estatus, prioridad, origen,
                   detalle_origen, fechaaviso, idusuario, estadousuario, fatencion, statusbh,
                   status, Derivado, PaginaWeb
            FROM consultas WHERE idconsulta = :id
             ");
        $stmt->bindValue(':id', $idconsulta, PDO::PARAM_INT);
        $stmt->execute();
        $original = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$original) {
            throw new Exception("No se encontró el requerimiento con id $idconsulta");
        }

        // 2) Preparar datos para el clon (original + datos editados)
        $cloneData = array_merge($original, $input);

        // 3) Insertar el clon
        $columns = [
            'urlpagina', 'nombres', 'apellidos', 'empresa', 'email', 'direccion', 'telefono', 'celular',
            'nextel', 'asunto', 'archivo', 'ip', 'fecha_consulta', 'estatus', 'prioridad', 'origen',
            'detalle_origen', 'fechaaviso', 'idusuario', 'estadousuario', 'fatencion', 'statusbh',
            'status', 'Derivado', 'PaginaWeb'
        ];
        $placeholders = array_fill(0, count($columns), '?');
        $cloneSql = "INSERT INTO consultas (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->conn->prepare($cloneSql);

        // Mapear valores a los placeholders
        $values = [];
        foreach ($columns as $column) {
            $values[] = isset($cloneData[$column]) ? $cloneData[$column] : null;
        }
        $stmt->execute($values);
        $newId = (int)$this->conn->lastInsertId();

        $this->conn->commit();
        return [
            'success' => true,
            'newId'   => $newId,
            'message' => "Se Guardo como el ID :  $newId"
        ];
        } catch (Exception $e) {
        $this->conn->rollBack();
        return [
            'success' => false,
            'newId'   => null,
            'message' => 'Error al clonar: ' . $e->getMessage()
        ];
    }
}

    public function create() {
        $sql = "INSERT INTO " . $this->table_name . " (nombres, apellidos, empresa, email, celular, telefono, asunto, derivado, status, idusuario, archivo) 
                VALUES (:nombres, :apellidos, :empresa, :email, :celular, :telefono, :asunto, :derivado, :status, :idusuario, :archivo)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':empresa', $this->empresa);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':celular', $this->celular);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':asunto', $this->asunto);
        $stmt->bindParam(':derivado', $this->derivado);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->bindParam(':archivo', $this->archivo);

        $stmt->execute();
        
        $this->idconsulta = $this->conn->lastInsertId();

        $status = new HistorialUsuarioCliente($this->conn, $this->table_name);
        $status->crear($this->idconsulta, $this->idusuario, $this->estatus);
    }
}

