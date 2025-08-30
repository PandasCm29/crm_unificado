<?php
date_default_timezone_set('America/Lima');
require_once 'HistorialUsuarioClienteModel.php';
require_once 'AntecedentesClienteModel.php';
require_once 'EmailClienteModel.php';
class ClienteModel
{ // FALTA CODIGO POSTAL
    private $conn;
    private $table_name = "clientes";
    public $idcliente; //
    public $fecha; //
    public $fecha_consulta;   // <— F. Nivel urgencia
    public $asunto;           // <— Requerimientos
    public $celular_consulta;    // celular consulta
    public $nombres; //
    public $apellidos; //
    public $email; //
    public $telefono; //
    public $celular; //
    public $celular2; //
    public $celular3; //
    public $celular4; //
    public $direccion; //
    public $perfil;
    public $referencia; //
    public $provincia; //
    public $ciudad; //
    public $distrito; //
    public $distrito2;
    public $pais; //
    public $postal; //
    public $cumpleanios; //
    public $skype; //
    public $gmail;
    public $msn;
    public $yahoo;
    public $mienbros;
    public $empresa; //
    public $razon; //
    public $rubro; //
    public $direccion2; //
    public $ruc; //
    public $web; //
    public $cargo; //
    public $aniversario; //
    public $prioridad;
    public $carta_presentacion; //
    public $catalogo; //
    public $correo;
    public $prioridades;
    public $status;
    public $historial; // HISTORIAL DE USUARIOS
    public $antecedentes; // HISTORIAL DE ANTECEDENTES
    public $anotaciones;
    public $ventas_anuales;
    public $num_empleados; //
    public $fatencion; //
    public $estadocliente;
    public $estadousuario;
    public $idusuario; // ID del usuario de sesión
    public $fechaaviso; //
    public $statusbh;
    public $facebook;
    public $linkedin;
    public $twitter;
    public $otros;
    public $origen; // tipo
    public $detalle_origen; // origen
    public $obsdireccion; //
    public $accionescliente;
    public $creadopor;
    public $fecha_de_actualizacion;
    public $notion_page_id;
    public $last_sync_time = null;
    public $sync_source = 'CRM';


    public function __construct($db, int $idusuario)
    {
        $this->conn = $db;
        $this->idusuario = $idusuario;
    }
    public function reset()
    {
        $excluir = ['conn', 'table_name', 'idusuario', 'notion_page_id', 'last_sync_time', 'sync_source'];
        foreach (get_object_vars($this) as $prop => $value) {
            if (in_array($prop, $excluir)) {
                continue;
            }
            $this->$prop = '';
        }
    }


    public function getCumpleaniosAniversarios($mes = null, $tipo = 'ambos')
    {
        $sql = "SELECT idcliente, razon, nombres, apellidos, cargo, cumpleanios, aniversario, accionescliente, estadousuario
                FROM clientes 
                WHERE estadousuario=1";

        if ($tipo === 'cumpleanios') {
            $sql .= " AND cumpleanios IS NOT NULL";
        } elseif ($tipo === 'aniversario') {
            $sql .= " AND aniversario IS NOT NULL";
        }
        if ($mes) {
            $sql .= " AND (MONTH(cumpleanios) = :mes OR MONTH(aniversario) = :mes)";
        }

        $stmt = $this->conn->prepare($sql);

        if ($mes) {
            $stmt->bindParam(':mes', $mes);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarCumpleYAcciones()
    {
        $query = "UPDATE clientes SET 
                cumpleanios = :cumpleanios, accionescliente = :accionescliente
                WHERE idcliente = :idcliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cumpleanios', $this->cumpleanios);
        $stmt->bindParam(':accionescliente', $this->accionescliente);
        $stmt->bindParam(':idcliente', $this->idcliente);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function obtenerClientes()
    {
        try {
            $sql = "SELECT * FROM clientes";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener clientes: " . $e->getMessage();
            return [];
        }
    }

    public function countAll()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }


    public function obtenerClientesPorFechaAtencion($desde = null, $hasta = null, $page = 1, $limit = 25)
    {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT * FROM clientes";
            $where = [];
            $params = [];

            if ($desde !== null && $hasta !== null) {
                $where[] = "fatencion BETWEEN :desde AND :hasta";
                $params[':desde'] = $desde;
                $params[':hasta'] = $hasta;
            } elseif ($desde !== null) {
                $where[] = "fatencion >= :desde";
                $params[':desde'] = $desde;
            } elseif ($hasta !== null) {
                $where[] = "fatencion <= :hasta";
                $params[':hasta'] = $hasta;
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY fatencion DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener clientes por fecha de atención: " . $e->getMessage());
            return [];
        }
    }

    // Leer una página de clientes
    public function readPage(int $page = 1, int $limit = 10, bool $ordenarPorFecha=false, bool $filtrarPorFecha = false, bool $fechaAviso = false, ?string $desde = null, ?string $hasta = null): array
    {
        $offset = ($page - 1) * $limit;
        // Elegir la columna de fecha según $fechaAviso
        $campoFecha = $fechaAviso ? 'c.fechaaviso' : 'c.fatencion';

        // Construir condicionales WHERE
        $whereConditions = ["estadousuario = 1"];
        $params = [];
        if ($filtrarPorFecha && !empty($desde) && !empty($hasta)) {
            $whereConditions[] = "{$campoFecha} BETWEEN :desde AND :hasta";
            $params[':desde'] = $desde . ' 00:00:00';
            $params[':hasta'] = $hasta . ' 23:59:59';
        } elseif ($filtrarPorFecha && !empty($desde)) {
            $whereConditions[] = "{$campoFecha} >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        } elseif ($filtrarPorFecha && !empty($hasta)) {
            $whereConditions[] = "{$campoFecha} <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }

        $whereClause = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

        if ($ordenarPorFecha) {
            $orderBy = "{$campoFecha} DESC";
        } else {
            $orderBy = "GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01 00:00:00')) DESC";
        }

        $query = "SELECT c.*,
                  CONCAT(DAY(GREATEST(c.fechaaviso, c.fatencion,COALESCE(hs.fechaingreso, '1000-01-01'))), ' de ', ELT(MONTH(GREATEST(c.fechaaviso, c.fatencion,COALESCE(hs.fechaingreso, '1000-01-01'))), 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'), ' del ', YEAR(GREATEST(c.fechaaviso, c.fatencion,COALESCE(hs.fechaingreso, '1000-01-01')))) AS fatencion,

                  CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                  hs.status AS estado_cliente,
                  aor.accion AS origen_nombre,
                  atip.accion AS tipo_cliente,
                  pr.prioridad AS estado_atencion,
                  c.idusuario = :usuario AS propio
                  FROM " . $this->table_name . " c
                  LEFT JOIN (
                    SELECT * FROM (
                        SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
                        FROM historial_status WHERE tabla = 'clientes'
                        ) hs_sub WHERE rn = 1
                    ) hs ON c.idcliente = hs.idtabla

                    LEFT JOIN usuarios u ON u.idusuario = 
                        CASE 
                            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fechaaviso THEN c.idusuario
                            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fatencion THEN c.idusuario
                            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = hs.fechaingreso THEN hs.idusuario
                        END

                    LEFT JOIN ( SELECT valor, accion FROM acciones WHERE categoria = 'detalle_origen' GROUP BY valor
                  ) aor ON c.detalle_origen = aor.valor
                  LEFT JOIN ( SELECT * FROM acciones WHERE categoria = 'Origen' GROUP BY valor
                  ) atip ON c.origen = atip.valor
                  LEFT JOIN ( SELECT p.valor, p.prioridad FROM prioridad p WHERE p.tabla = 'clientes' GROUP BY valor
                  ) pr ON c.prioridad = pr.valor"
            . $whereClause .
            " ORDER BY {$orderBy}
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);


        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(":usuario", (int)$this->idusuario, PDO::PARAM_INT);

        $stmt->execute();
        // Recorrer los resultados y formatear el campo email, para obtener los emails de los clientes por id
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Recorrer en un for loop para obtener los emails de cada cliente y asignar al result
        $emailModel = new EmailCliente($this->conn, $this->table_name);
        foreach ($result as &$clienteRead) {
            $emails = $emailModel->getByClienteId($clienteRead['idcliente']) ?? [];
            $clienteRead['email'] = implode(', ', array_column($emails, 'email'));
        }
        unset($clienteRead); // Desreferenciar el último elemento para evitar referencias no deseadas
        // Devolver los resultados
        $stmt->closeCursor(); // Cerrar el cursor para liberar recursos
        $stmt = null; // Desreferenciar el objeto PDOStatement
        // Devolver los resultados
        return $result;
    }
    public function readEliminados(int $page = 1, int $limit = 10) {
        $offset = ($page - 1) * $limit;
        $whereConditions = ["c.estadousuario = 0"];
        $whereClause = " WHERE " . implode(" AND ", $whereConditions);
        $orderBy = "c.fecha_de_actualizacion DESC";
        $query = "SELECT c.*,
              DATE_FORMAT(c.fechaaviso, '%Y-%m-%d') AS fechaaviso,
              u.nombres AS usuario,
              hs.status AS estado_cliente,
              aor.accion AS origen_nombre,
              atip.accion AS tipo_cliente, 
              pr.prioridad AS estado_atencion
              FROM " . $this->table_name . " c
              LEFT JOIN usuarios u ON c.idusuario = u.idusuario
              LEFT JOIN (
                  SELECT * FROM (
                      SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
                      FROM historial_status
                  ) hs_sub WHERE rn = 1
              ) hs ON c.idcliente = hs.idtabla
              LEFT JOIN ( SELECT valor, accion FROM acciones WHERE categoria = 'detalle_origen' GROUP BY valor
              ) aor ON c.origen = aor.valor
              LEFT JOIN ( SELECT * FROM acciones WHERE categoria = 'Origen' GROUP BY valor
              ) atip ON c.estadocliente = atip.valor
              LEFT JOIN ( SELECT p.valor, p.prioridad FROM prioridad p WHERE p.tabla = 'clientes' GROUP BY valor
              ) pr ON c.prioridad = pr.valor"
            . $whereClause .
            " ORDER BY {$orderBy}
              LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countFiltered(bool $filtrarPorFecha = false, bool $fechaAviso = false, ?string $desde = null, ?string $hasta = null)
    { //, string $tipo_cliente = '', string $status_atencion = '', string $origen=''
        //CAMPOS DE FECHAS
        $campoFecha = $fechaAviso ? 'fechaaviso' : 'fatencion';
        //CONTEO DE FILAS
        $query = "SELECT COUNT(*) as TOTAL 
          FROM {$this->table_name} c";
        $query .= " WHERE estadousuario = 1";
        $params = [];
        //FILTROS DE FECHA
        if ($filtrarPorFecha && !empty($desde) && !empty($hasta)) {
            $query .= " AND c.{$campoFecha} BETWEEN :desde AND :hasta";
            $params[':desde'] = $desde . ' 00:00:00';
            $params[':hasta'] = $hasta . ' 23:59:59';
        } elseif ($filtrarPorFecha && !empty($desde)) {
            $query .= " AND c.{$campoFecha} >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        } elseif ($filtrarPorFecha && !empty($hasta)) {
            $query .= " AND c.{$campoFecha} <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }
        $stmt = $this->conn->prepare($query);
        //VINCULAR PARAMETROS
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['TOTAL'];
    }

    public function countWithOutFilters($eliminados = true)
    {
        $value = $eliminados ? 0 : 1;
        $query = "SELECT COUNT(*) as TOTAL FROM {$this->table_name} WHERE estadousuario = :eliminado ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":eliminado", $value);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['TOTAL'];
    }

    public function getById($idcliente)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE idcliente = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $idcliente);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;  // no existe
        }

        // ✅ Creamos una nueva instancia del objeto Cliente
        $nuevoCliente = new ClienteModel($this->conn, 0);
        // Asignamos los emails obtenidos de EmailClienteModel
        $emailModel = new EmailCliente($this->conn, $this->table_name);
        $emails = $emailModel->getByClienteId($idcliente);

        // Llenamos las propiedades públicas que existan:
        foreach ($row as $key => $value) {
            // Si es uno de los campos de fecha, lo formateamos
            if (in_array($key, ['fechaviso', 'fatencion']) && !empty($value)) {
                $fecha = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                $nuevoCliente->$key = $fecha ? $fecha->format('d-m-Y') : $value;
            } else if ($key === 'email') {
                $nuevoCliente->$key = implode(', ', array_column($emails, 'email'));
            } else {
                $nuevoCliente->$key = $value;
            }
        }
        $historialStatus = new HistorialUsuarioCliente($this->conn, $this->table_name);
        $nuevoCliente->historial = $historialStatus->getById($idcliente,  $this->idusuario);

        $historialAntecedentes = new AntecedentesCliente($this->conn);
        $nuevoCliente->antecedentes = $historialAntecedentes->getById($idcliente, $this->idusuario);

        return $nuevoCliente;  // devolvemos el objeto Cliente con datos
    }
    public function getIdByInfo($email, $telefono1, $telefono2)
    {
        $query = "SELECT idcliente FROM {$this->table_name} c
                WHERE EXISTS (SELECT 1 FROM groupemail g WHERE g.idtabla = c.idcliente AND g.email = :email)
                OR (telefono IS NOT NULL AND telefono != '' AND (telefono = :tel1 OR telefono = :tel2))
                OR (celular IS NOT NULL AND celular != '' AND (celular = :tel1 OR celular = :tel2))
                OR (celular2 IS NOT NULL AND celular2 != '' AND (celular2 = :tel1 OR celular2 = :tel2))
                OR (celular3 IS NOT NULL AND celular3 != '' AND (celular3 = :tel1 OR celular3 = :tel2))
                OR (celular4 IS NOT NULL AND celular4 != '' AND (celular4 = :tel1 OR celular4 = :tel2))
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':tel1', $telefono1, PDO::PARAM_STR);
        $stmt->bindValue(':tel2', $telefono2, PDO::PARAM_STR);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['idcliente'] : null;
    }

    public function updatePerfil(){
        $fechaaviso = date('Y-m-d H:i:s'); // Obtener la fecha actual
        $query = "UPDATE clientes SET 
                    perfil = :perfil,
                    fechaaviso = :fechaaviso,
                    idusuario = :idusuario
                  WHERE idcliente = :idcliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':perfil', $this->perfil, PDO::PARAM_STR);
        $stmt->bindParam(':fechaaviso', $fechaaviso, PDO::PARAM_STR);
        $stmt->bindParam(':idusuario', $this->idusuario, PDO::PARAM_INT);
        $stmt->bindParam(':idcliente', $this->idcliente, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    
    // Método auxiliar para vincular parámetros
    private function bindParams($stmt, $creating = true)
    {
        $this->last_sync_time = date('Y-m-d H:i:s');
        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':celular', $this->celular);
        $stmt->bindParam(':celular2', $this->celular2);
        $stmt->bindParam(':celular3', $this->celular3);
        $stmt->bindParam(':celular4', $this->celular4);
        $stmt->bindParam(':direccion', $this->direccion);
        $stmt->bindParam(':direccion2', $this->direccion2);
        $stmt->bindParam(':referencia', $this->referencia);
        $stmt->bindParam(':provincia', $this->provincia);
        $stmt->bindParam(':ciudad', $this->ciudad);
        $stmt->bindParam(':distrito', $this->distrito);
        $stmt->bindParam(':distrito2', $this->distrito2);
        $stmt->bindParam(':pais', $this->pais);
        $stmt->bindParam(':postal', $this->postal);
        $stmt->bindParam(':cumpleanios', $this->cumpleanios);
        $stmt->bindParam(':skype', $this->skype);
        $stmt->bindParam(':empresa', $this->empresa);
        $stmt->bindParam(':razon', $this->razon);
        $stmt->bindParam(':rubro', $this->rubro);
        $stmt->bindParam(':ruc', $this->ruc);
        $stmt->bindParam(':web', $this->web);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':aniversario', $this->aniversario);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->bindParam(':carta_presentacion', $this->carta_presentacion);
        $stmt->bindParam(':catalogo', $this->catalogo);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':num_empleados', $this->num_empleados);
        $stmt->bindParam(':idusuario', $this->idusuario);
        $stmt->bindParam(':origen', $this->origen);
        $stmt->bindParam(':fechaaviso', $this->fechaaviso);
        $stmt->bindParam(':detalle_origen', $this->detalle_origen);
        $stmt->bindParam(':obsdireccion', $this->obsdireccion);
        $stmt->bindParam(':notion_page_id', $this->notion_page_id);
        $stmt->bindParam(':sync_source', $this->sync_source);
        $stmt->bindParam(':last_sync_time', $this->last_sync_time, PDO::PARAM_NULL | PDO::PARAM_STR);
        $stmt->bindParam(':estadousuario', $this->estadousuario);
        
        if ($creating) {
            $stmt->bindParam(':perfil', $this->perfil);
            $stmt->bindParam(':mienbros', $this->mienbros);
            $stmt->bindParam(':anotaciones', $this->anotaciones);
            $stmt->bindParam(':ventas_anuales', $this->ventas_anuales);
            $stmt->bindParam(':estadocliente', $this->estadocliente);
            $stmt->bindParam(':prioridades', $this->prioridades);
            $stmt->bindParam(':statusbh', $this->statusbh);
            $stmt->bindParam(':facebook', $this->facebook);
            $stmt->bindParam(':linkedin', $this->linkedin);
            $stmt->bindParam(':twitter', $this->twitter);
            $stmt->bindParam(':otros', $this->otros);
            $stmt->bindParam(':accionescliente', $this->accionescliente);
            $stmt->bindParam(':gmail', $this->gmail);
            $stmt->bindParam(':msn', $this->msn);
            $stmt->bindParam(':yahoo', $this->yahoo);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':fatencion', $this->fatencion);
            $stmt->bindParam(':creadopor', $this->idusuario);
        } else {
            $stmt->bindParam(':idcliente', $this->idcliente);
        }
    }
    // Crear un nuevo Cliente
    public function create()
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (
                nombres, apellidos, telefono, celular, celular2, celular3, celular4, direccion,
                perfil, referencia, provincia, ciudad, distrito, distrito2, pais, postal, cumpleanios,
                skype, gmail, msn, yahoo, mienbros, empresa, razon, rubro, direccion2, ruc, web,
                cargo, aniversario, prioridad, carta_presentacion, catalogo, correo, prioridades,
                status, anotaciones, ventas_anuales, num_empleados, fatencion, estadocliente,
                estadousuario, idusuario, fechaaviso, statusbh, facebook, linkedin, twitter, otros,
                origen, detalle_origen, obsdireccion, accionescliente, notion_page_id, sync_source, last_sync_time,
                creadopor
            ) VALUES (
                :nombres, :apellidos, :telefono, :celular, :celular2, :celular3, :celular4, :direccion,
                :perfil, :referencia, :provincia, :ciudad, :distrito, :distrito2, :pais, :postal, :cumpleanios,
                :skype, :gmail, :msn, :yahoo, :mienbros, :empresa, :razon, :rubro, :direccion2, :ruc, :web,
                :cargo, :aniversario, :prioridad, :carta_presentacion, :catalogo, :correo, :prioridades,
                :status, :anotaciones, :ventas_anuales, :num_empleados, :fatencion, :estadocliente,
                :estadousuario, :idusuario, :fechaaviso, :statusbh, :facebook, :linkedin, :twitter, :otros,
                :origen, :detalle_origen, :obsdireccion, :accionescliente, :notion_page_id, :sync_source, :last_sync_time,
                :creadopor
            )";

            $stmt = $this->conn->prepare($query);

            // Vincula todos los parámetros
            $this->bindParams($stmt);

            // Iniciar transacción para garantizar atomicidad
            $this->conn->beginTransaction();
            // Ejecutar la inserción del cliente
            if (!$stmt->execute()) {
                throw new Exception('Error al insertar el cliente');
            }

            // Obtener el ID del cliente recién creado
            $idcliente = (int)$this->conn->lastInsertId();
            $emails = $this->email;

            // Guardar emails en la tabla group_email
            $emailModel = new EmailCliente($this->conn, $this->table_name); // Usar 'group_email' explícitamente
            $emailResult = $emailModel->update($idcliente, $emails, $this->sync_source); // No sobrescribir $emails

            // Verificar si la inserción de correos falló
            if (!$emailResult['success']) {
                throw new Exception($emailResult['message']);
            }

            // Confirmar transacción
            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Cliente y correos insertados correctamente',
                'idcliente' => $idcliente
            ];
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Actualizar cliente
    public function update()
    {
        try {
            $query = "UPDATE " . $this->table_name . " SET
            nombres = :nombres,
            apellidos = :apellidos,
            telefono = :telefono,
            celular = :celular,
            celular2 = :celular2,
            celular3 = :celular3,
            celular4 = :celular4,
            direccion = :direccion,
            direccion2 = :direccion2,
            referencia = :referencia,
            provincia = :provincia,
            ciudad = :ciudad,
            distrito = :distrito,
            distrito2 = :distrito2,
            pais = :pais,
            postal = :postal,
            cumpleanios = :cumpleanios,
            skype = :skype,"
                // mienbros = :mienbros,
                . "empresa = :empresa,
            razon = :razon,
            rubro = :rubro,
            ruc = :ruc,
            web = :web,
            cargo = :cargo,
            aniversario = :aniversario,
            prioridad = :prioridad,
            carta_presentacion = :carta_presentacion,
            catalogo = :catalogo,
            correo = :correo,"
                // prioridades = :prioridades,
                // anotaciones = :anotaciones,
                // ventas_anuales = :ventas_anuales,
            . "num_empleados = :num_empleados,"
                // estadocliente = :estadocliente,
            ."estadousuario = :estadousuario,
            idusuario = :idusuario,
            fechaaviso = :fechaaviso,"
                // statusbh = :statusbh,
                // facebook = :facebook,
                // linkedin = :linkedin,
                // twitter = :twitter,
                // otros = :otros,
                . "origen = :origen,
            detalle_origen = :detalle_origen,
            obsdireccion = :obsdireccion,"
                // accionescliente = :accionescliente,
                . "notion_page_id = :notion_page_id,
             sync_source = :sync_source,
             last_sync_time = :last_sync_time "
                . "WHERE idcliente = :idcliente";

            $stmt = $this->conn->prepare($query);

            $this->bindParams($stmt, false);

            // Iniciar transacción para garantizar atomicidad
            $this->conn->beginTransaction();
            // Ejecutar la inserción del cliente
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar el cliente');
            }

            // Obtener el ID del cliente
            $idcliente = (int) $this->idcliente;
            $emails = $this->email;

            // Guardar emails en la tabla group_email
            $emailModel = new EmailCliente($this->conn, $this->table_name); // Usar 'group_email' explícitamente
            $emailResult = $emailModel->update($idcliente, $emails, $this->sync_source); // No sobrescribir $emails

            // Verificar si la inserción de correos falló
            if (!$emailResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Error al insertar emails: ' . $emailResult['message']
                ];
            }

            // Confirmar transacción
            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Cliente y correos actualizados correctamente',
            ];
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    //DUPLICAR CLIENTE    
    public function duplicarCliente(): array
    {
        try {
            // 1) Traer el registro original por ID (una sola fila)
            $sql  = "SELECT * FROM {$this->table_name} WHERE idcliente = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => (int)$this->idcliente]);
            $orig = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orig) {
                return ['success' => false, 'message' => 'Cliente no encontrado'];
            }

            // 2) Columnas que tu create() inserta
            $cols = [
                'nombres','apellidos','telefono','celular','celular2','celular3','celular4','direccion',
                'perfil','referencia','provincia','ciudad','distrito','distrito2','pais','postal','cumpleanios',
                'skype','gmail','msn','yahoo','mienbros','empresa','razon','rubro','direccion2','ruc','web',
                'cargo','aniversario','prioridad','carta_presentacion','catalogo','correo','prioridades',
                'status','anotaciones','ventas_anuales','num_empleados','fatencion','estadocliente',
                'estadousuario','idusuario','fechaaviso','statusbh','facebook','linkedin','twitter','otros',
                'origen','detalle_origen','obsdireccion','accionescliente','notion_page_id','sync_source','last_sync_time',
                'creadopor'
            ];

            // 3) Ajustes de duplicación
            $now = date('Y-m-d H:i:s');        // <- fecha/hora de duplicación

            // Usuario/auditoría
            $orig['idusuario'] = (int)$this->idusuario;
            $orig['creadopor'] = (int)$this->idusuario;

            // Únicos reales
            if (array_key_exists('ruc', $orig)) $orig['ruc'] = null;

            // Flags NOT NULL -> 0/1
            foreach (['carta_presentacion','catalogo','correo'] as $flag) {
                if (array_key_exists($flag, $orig)) {
                    $orig[$flag] = (int)!empty($orig[$flag]);
                }
            }

            // Fechas: poner AHORA en duplicación (pedido tuyo)
            if (array_key_exists('fatencion', $orig))  $orig['fatencion']  = $now;
            if (array_key_exists('fechaaviso', $orig)) $orig['fechaaviso'] = $now;

            // Otros NOT NULL comunes (ajusta si tu default es otro)
            foreach (['statusbh','estadocliente','estadousuario'] as $col) {
                if (array_key_exists($col, $orig) && $orig[$col] === null) {
                    $orig[$col] = 0;
                }
            }

            // Sync/Auditoría
            if (empty($orig['sync_source'])) $orig['sync_source'] = 'duplicate';
            if (array_key_exists('last_sync_time', $orig)) $orig['last_sync_time'] = null;

            // 4) Emails del original → create() los reinsertará
            $emails = [];
            if (class_exists('EmailCliente')) {
                $emailModel = new EmailCliente($this->conn, $this->table_name);
                $emailsList = $emailModel->getByClienteId((int)$this->idcliente) ?? [];
                $emails = array_values(array_unique(array_filter(array_map(
                    fn($e) => $e['email'] ?? '',
                    $emailsList
                ))));
            }

            // 5) Volcar al objeto para bindParams() y create()
            foreach ($cols as $c) {
                $this->$c = $orig[$c] ?? null;
            }
            $this->email = $emails;

            // 6) Crear nuevo (create() maneja transacción + group_email)
            return $this->create();

        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Error al duplicar cliente: ' . $e->getMessage()];
        }
    }




    // Eliminar usuario
    public function delete($date=null)
    {
        $fecha = !$date ? date('Y-m-d H:i:s'): $date;
        $query = "UPDATE " . $this->table_name . " SET estadousuario = 0, fecha_de_actualizacion = :fecha WHERE idcliente = :idcliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idcliente', $this->idcliente);
        $stmt->bindParam(':fecha', $fecha);
        return $stmt->execute();
    }

    // Verificar si el email ya existe
    public function emailExists($email)
    {
        $query = "SELECT idcliente FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getByTipoCliente($tipo_cliente)
    {
        if ($tipo_cliente === '' || $tipo_cliente === null) {
            // Si no hay filtro, trae todo
            $query = "SELECT * FROM clientes"; // cambia 'clientes' por tu tabla real
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        } else if ($tipo_cliente === 'null') {
            // Si quieres filtrar los que tienen NULL en tipo cliente
            $query = "SELECT * FROM clientes WHERE accionescliente IS NULL OR accionescliente = ''";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        } else {
            $query = "SELECT * FROM clientes WHERE accionescliente = :tipo_cliente";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tipo_cliente', $tipo_cliente);
            $stmt->execute();
        }

        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $clientes;
    }
    public function getByOrigen($origen)
    {
        $sql = "SELECT * FROM clientes";
        if (!empty($origen)) {
            $sql .= " WHERE origen = :origen";
        }
        $stmt = $this->conn->prepare($sql);
        if (!empty($origen)) {
            $stmt->bindParam(':origen', $origen);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByNumero(string $num): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table_name} c
                WHERE CAST(c.idcliente AS CHAR) LIKE :num
                AND c.estadocliente IN (0,1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':num', "%{$num}%");
        $stmt->execute();
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getByNumero(string $num, int $limit, int $offset): array
    {

        $sql = "
        SELECT
          c.idcliente,
          c.idusuario,
            -- Usuario que generó la fecha más reciente (fechaaviso, fatencion o historial)
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
            -- Fecha de atención más relevante
            CONCAT(DAY(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))), ' de ',
                    ELT(MONTH(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))),
                        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                        'septiembre', 'octubre', 'noviembre', 'diciembre'),
                    ' del ', YEAR(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')))) AS fatencion,
          c.empresa,
          c.razon,
          c.rubro,
          -- ÚLTIMO STATUS
          hs.status           AS estado_cliente,
          -- ORIGEN Y TIPO_CLIENTE
          aor.accion          AS origen_nombre,
          atip.accion         AS tipo_cliente,
          -- ESTADO DE ATENCIÓN
          pr.prioridad        AS estado_atencion,
          c.nombres,
          c.apellidos,
          c.cargo,
          c.telefono,
          c.celular,
          c.celular2,
          c.celular3,
          c.celular4,
          c.ruc,
          c.web,
          c.direccion,
          c.direccion2        AS direccion2,
          c.obsdireccion      AS obsdireccion,
          c.referencia,
          c.distrito,
          c.ciudad,
          c.cumpleanios,
          c.aniversario,
          c.idusuario = :usuario AS propio
        FROM clientes c
        -- join al último historial de status
        LEFT JOIN (
          SELECT * FROM (
            SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
            FROM historial_status WHERE tabla = 'clientes'
          ) AS sub WHERE rn = 1
        ) hs ON c.idcliente = hs.idtabla
         -- Usuario de la última acción (fechaaviso, fatencion o historial)
        LEFT JOIN usuarios u ON u.idusuario = 
        CASE
            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fechaaviso THEN c.idusuario
            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fatencion THEN c.idusuario
            WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = hs.fechaingreso THEN hs.idusuario
            ELSE c.idusuario
        END
        -- join a prioridad (estado de atención)
        LEFT JOIN (
          SELECT valor, prioridad
          FROM prioridad
          WHERE tabla = 'clientes'
          GROUP BY valor
        ) pr ON c.prioridad = pr.valor
        -- joins opcionales para origen y tipo_cliente si quieres mantenerlos
        LEFT JOIN ( SELECT valor, accion FROM acciones WHERE categoria = 'detalle_origen' GROUP BY valor ) aor ON c.detalle_origen = aor.valor
        LEFT JOIN ( SELECT * FROM acciones WHERE categoria = 'Origen' GROUP BY valor ) atip ON c.origen = atip.valor
        WHERE CAST(c.idcliente AS CHAR) LIKE :num
            AND c.estadocliente IN (0,1) ORDER BY GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) DESC
        LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':num',    "%{$num}%");
        $stmt->bindValue(':limit',  $limit,   PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->bindValue(":usuario", (int)$this->idusuario, PDO::PARAM_INT);
        $stmt->execute();

        // Recorrer los resultados y formatear el campo email, para obtener los emails de los clientes por id
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Recorrer en un for loop para obtener los emails de cada cliente y asignar al result
        $emailModel = new EmailCliente($this->conn, $this->table_name);
        foreach ($result as &$clienteRead) {
            $emails = $emailModel->getByClienteId($clienteRead['idcliente']) ?? [];
            $clienteRead['email'] = implode(' - ', array_column($emails, 'email'));
        }
        unset($clienteRead); // Desreferenciar el último elemento para evitar referencias no deseadas
        // Devolver los resultados
        $stmt->closeCursor(); // Cerrar el cursor para liberar recursos
        $stmt = null; // Desreferenciar el objeto PDOStatement
        // Devolver los resultados
        return $result;
    }


    // FLITAR SEGUN LO QUE SELECIONE EL USUARIO
    public function getByCampo(string $campo, string $valor, int $limit, int $offset): array {
        $permitidos = [
            'idcliente', 'empresa', 'razon', 'nombres', 'apellidos',
            'usuario', 'rubro', 'ruc', 'telefono', 'celular',
            'fatencion', 'direccion', 'direccion2', 'email', 'web'
        ];

        if (!in_array($campo, $permitidos)) return [];
        switch ($campo) {
            case 'usuario':
                $whereCampo = "CONCAT(u.nombres, ' ', u.apellidos) LIKE :valor";
                break;
            case 'celular':
                $whereCampo = "(c.celular LIKE :valor OR c.celular2 LIKE :valor OR c.celular3 LIKE :valor OR c.celular4 LIKE :valor)";
                break;
            default:
                $whereCampo = "c.$campo LIKE :valor";
                break;
        }

        $sql = "
            SELECT
            c.idcliente,
            c.idusuario,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                -- Fecha de atención más relevante
                CONCAT(DAY(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))), ' de ',
                        ELT(MONTH(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))),
                            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                            'septiembre', 'octubre', 'noviembre', 'diciembre'),
                        ' del ', YEAR(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')))) AS fatencion,
            c.empresa,
            c.razon,
            c.rubro,
            hs.status           AS estado_cliente,
            aor.accion          AS origen_nombre,
            atip.accion         AS tipo_cliente,
            pr.prioridad        AS estado_atencion,
            c.nombres,
            c.apellidos,
            c.cargo,
            c.telefono,
            c.celular,
            c.celular2,
            c.celular3,
            c.celular4,
            c.ruc,
            c.web,
            c.direccion,
            c.direccion2,
            c.obsdireccion,
            c.referencia,
            c.distrito,
            c.ciudad,
            c.cumpleanios,
            c.aniversario,
            c.idusuario = :usuario AS propio
            FROM clientes c
            LEFT JOIN (
            SELECT * FROM (
                SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
                FROM historial_status where tabla = 'clientes'
            ) AS sub WHERE rn = 1
            ) hs ON c.idcliente = hs.idtabla
            -- Usuario de la última acción (fechaaviso, fatencion o historial)
            LEFT JOIN usuarios u ON u.idusuario = 
            CASE
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fechaaviso THEN c.idusuario
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fatencion THEN c.idusuario
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = hs.fechaingreso THEN hs.idusuario
                ELSE c.idusuario
            END
            LEFT JOIN (
            SELECT valor, prioridad
            FROM prioridad
            WHERE tabla = 'clientes'
            GROUP BY valor
            ) pr ON c.prioridad = pr.valor
            LEFT JOIN ( SELECT valor, accion FROM acciones WHERE categoria = 'detalle_origen' GROUP BY valor ) aor ON c.detalle_origen = aor.valor
            LEFT JOIN ( SELECT * FROM acciones WHERE categoria = 'Origen' GROUP BY valor ) atip ON c.origen = atip.valor
            WHERE $whereCampo
                AND c.estadocliente IN (0,1) ORDER BY GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':valor', "%{$valor}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(":usuario", (int)$this->idusuario, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener emails por cliente
        $emailModel = new EmailCliente($this->conn, $this->table_name);
        foreach ($result as &$clienteRead) {
            $emails = $emailModel->getByClienteId($clienteRead['idcliente']) ?? [];
            $clienteRead['email'] = implode(' - ', array_column($emails, 'email'));
        }
        unset($clienteRead); // prevenir referencia persistente

        $stmt->closeCursor();
        return $result;
    }

    // OBTENER CANTIDAD DE CLIENTES SEGUN EL CAMPO Y VALOR
    public function countByCampo(string $campo, string $valor): int {
        $permitidos = [
            'idcliente', 'empresa', 'razon', 'nombres', 'apellidos',
            'usuario', 'rubro', 'ruc', 'telefono', 'celular',
            'fatencion', 'direccion', 'direccion2', 'email', 'web'
        ];

        if (!in_array($campo, $permitidos)) {
            return 0;
        }

        switch ($campo) {
            case 'usuario':
                $sql = "
                    SELECT COUNT(*) as total
                    FROM clientes c
                    LEFT JOIN usuarios u ON c.idusuario = u.idusuario
                    WHERE CONCAT(u.nombres, ' ', u.apellidos) LIKE :valor
                    AND c.estadocliente IN (0,1)";
                break;
            case 'celular':
                $sql = "
                    SELECT COUNT(*) as total
                    FROM clientes
                    WHERE (
                        celular LIKE :valor OR
                        celular2 LIKE :valor OR
                        celular3 LIKE :valor OR
                        celular4 LIKE :valor
                    ) AND estadocliente IN (0,1)";
                break;
            default:
                $sql = "
                    SELECT COUNT(*) as total
                    FROM clientes
                    WHERE $campo LIKE :valor AND estadocliente IN (0,1)";
                break;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':valor', "%{$valor}%");
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($data['total'] ?? 0);
    }

    // BUSCAR EN TODA LA TABLA
    public function buscarEnTodos(string $valor, int $limit, int $offset): array {
        $sql = "
            SELECT
            c.idcliente,
            c.idusuario,
            CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                -- Fecha de atención más relevante
                CONCAT(DAY(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))), ' de ',
                    ELT(MONTH(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01'))),
                        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
                        'septiembre', 'octubre', 'noviembre', 'diciembre'),
                    ' del ', YEAR(GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')))) AS fatencion,
            c.empresa,
            c.razon,
            c.rubro,
            hs.status AS estado_cliente,
            aor.accion AS origen_nombre,
            atip.accion AS tipo_cliente,
            pr.prioridad AS estado_atencion,
            c.nombres,
            c.apellidos,
            c.cargo,
            c.telefono,
            c.celular,
            c.celular2,
            c.celular3,
            c.celular4,
            c.ruc,
            c.web,
            c.direccion,
            c.direccion2,
            c.obsdireccion,
            c.referencia,
            c.distrito,
            c.ciudad,
            c.cumpleanios,
            c.aniversario,
            c.idusuario = :usuario AS propio
            FROM clientes c
            LEFT JOIN (
                SELECT * FROM (
                    SELECT *, ROW_NUMBER() OVER (PARTITION BY idtabla ORDER BY fechaingreso DESC) AS rn
                    FROM historial_status WHERE tabla = 'clientes'
                ) AS sub WHERE rn = 1
            ) hs ON c.idcliente = hs.idtabla
            -- Usuario de la última acción (fechaaviso, fatencion o historial)
            LEFT JOIN usuarios u ON u.idusuario = 
            CASE
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fechaaviso THEN c.idusuario
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = c.fatencion THEN c.idusuario
                WHEN GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) = hs.fechaingreso THEN hs.idusuario
                ELSE c.idusuario
            END
            LEFT JOIN (
                SELECT valor, prioridad FROM prioridad WHERE tabla = 'clientes' GROUP BY valor
            ) pr ON c.prioridad = pr.valor
            LEFT JOIN ( SELECT valor, accion FROM acciones WHERE categoria = 'detalle_origen' GROUP BY valor ) aor ON c.detalle_origen = aor.valor
            LEFT JOIN ( SELECT * FROM acciones WHERE categoria = 'Origen' GROUP BY valor ) atip ON c.origen = atip.valor
            WHERE (
                c.empresa LIKE :valor OR
                c.razon LIKE :valor OR
                c.nombres LIKE :valor OR
                c.apellidos LIKE :valor OR
                c.ruc LIKE :valor OR
                c.telefono LIKE :valor OR
                c.celular LIKE :valor
            )
            AND c.estadocliente IN (0,1) ORDER BY GREATEST(c.fechaaviso, c.fatencion, COALESCE(hs.fechaingreso, '1000-01-01')) DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':valor', "%{$valor}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(":usuario", (int)$this->idusuario, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Agregamos los correos desde la tabla relacionada
        $emailModel = new EmailCliente($this->conn, $this->table_name);
        foreach ($result as &$clienteRead) {
            $emails = $emailModel->getByClienteId($clienteRead['idcliente']) ?? [];
            $clienteRead['email'] = implode(' - ', array_column($emails, 'email'));
        }
        unset($clienteRead);

        return $result;
    }

    // OBTENER CANTIDAD DE CLIENTES SEGUN EL VALOR
    public function countEnTodos(string $valor): int {
        $sql = "
            SELECT COUNT(*) as total
            FROM clientes
            WHERE (
                empresa LIKE :valor OR
                razon LIKE :valor OR
                nombres LIKE :valor OR
                apellidos LIKE :valor OR
                ruc LIKE :valor OR
                telefono LIKE :valor OR
                celular LIKE :valor OR
                email LIKE :valor
            )
            AND estadocliente IN (0,1) 
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':valor', "%{$valor}%");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($data['total'] ?? 0);
    }
}
