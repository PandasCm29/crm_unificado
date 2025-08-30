<?php
date_default_timezone_set('America/Lima');
class User
{
    private $conn;
    private $table_name = "usuarios";

    // Propiedades correspondientes a los campos de la tabla
    public $idusuario;
    public $nombres;
    public $apellidos;
    public $usuario;
    public $area;
    public $tipoempleado;
    public $idpais=1;
    public $password;
    public $url_sesion;
    public $estado = true;
    public $hora_entrada;
    public $hora_salida;
    public $dias_trabajo;
    public $dni;
    public $celular;
    public $correo;
    public $notion_page_id = null;
    public $last_sync_time = null;
    public $sync_source = 'CRM';

    public $usuario_sesion;

    public function __construct($db, $idusuario)
    {
        $this->conn = $db;
        $this->usuario_sesion = $idusuario;
    }
    public function reset()
    {
        $excluir = ['conn', 'table_name', 'usuario_sesion', 'notion_page_id', 'last_sync_time', 'sync_source', 'estado',"idpais"];
        foreach (get_object_vars($this) as $prop => $value) {
            if (in_array($prop, $excluir)) {
                continue;
            }
            $this->$prop = '';
        }
    }

    // Obtener un solo usuario por ID
    public function readOne()
    {
        $query = "SELECT idusuario, nombres, apellidos, usuario, area, tipoempleado, idpais, estado, hora_entrada, hora_salida, dni, celular, correo 
                  FROM " . $this->table_name . " 
                  WHERE idusuario = ? 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idusuario);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;  // no existe
        }

        // ✅ Creamos una nueva instancia del objeto Cliente
        $nuevoCliente = new User($this->conn, 0);

        // Llenamos las propiedades públicas que existan:
        foreach ($row as $key => $value) {
            $nuevoCliente->$key = $value;
        }

        return $nuevoCliente;
    }

    // Obtener todos los usuarios
    public function readAll($area_id = null)
    {
        $query = "SELECT u.*, a.nombres as area_nombre, r.nombres as rol_nombre 
                  FROM " . $this->table_name . " u
                  LEFT JOIN areas a ON u.area = a.idusuario
                  LEFT JOIN roles r ON u.id_rol = r.idusuario";

        if ($area_id) {
            $query .= " WHERE u.area = ?";
        }

        $stmt = $this->conn->prepare($query);

        if ($area_id) {
            $stmt->bindParam(1, $area_id);
        }

        $stmt->execute();

        return $stmt;
    }
    public function countAll($filtro = '')
    {
        $usuario_actual = $this->usuario_sesion;

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE idusuario != :usuario 
        AND (nombres LIKE :filtro OR apellidos LIKE :filtro OR usuario LIKE :filtro)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(":usuario", (int) $usuario_actual, PDO::PARAM_INT);
        $stmt->bindValue(':filtro', "%{$filtro}%");

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total =  (int)$row['total'];
        if ($total == 0) {
            return $this->countAll();
        }
        return $total;
    }

    public function countByArea($area_id)
    {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table_name . " u
                  WHERE u.area = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $area_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    // Leer página de usuarios de un área concreta
    public function readPageByArea($page = 1, $limit = 20, $area_id=null)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT u.*, a.nombres AS area_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN areas a ON u.area = a.idusuario
                  WHERE u.area = ? AND u.idusuario != ?
                  LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $area_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->usuario_sesion);
        $stmt->bindParam(3, $offset,  PDO::PARAM_INT);
        $stmt->bindParam(4, $limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Leer una página de usuarios
    //FUNCTION MIA
    public function readPage($filtro = '', $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $usuario_actual = $this->usuario_sesion;

        $query = "SELECT idusuario, nombres, apellidos, usuario, area, estado 
                FROM " . $this->table_name . " 
                WHERE idusuario != :usuario 
                AND (nombres LIKE :filtro OR apellidos LIKE :filtro OR usuario LIKE :filtro) 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);


        $stmt->bindValue(":usuario", (int) $usuario_actual, PDO::PARAM_INT);
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->bindValue(':filtro', "%{$filtro}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function readPageFilter($filtro = '', $page = 1, $limit = 10, $filtering = true)
    {
        $result = $this->readPage($filtro, $page, $limit);

        if (!$result) {
            $filtering = false;
            $result = $this->readPage('', $page, $limit);
        }
        return [
            'filtering' => $filtering,
            'data' => $result
        ];
    }
    private function bindParams($stmt, $creating = true)
    {
        $this->last_sync_time = date('Y-m-d H:i:s');
        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':dni', $this->dni);
        $stmt->bindParam(':celular', $this->celular);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':area', $this->area);
        $stmt->bindParam(':tipoempleado', $this->tipoempleado);
        $stmt->bindParam(':hora_entrada', $this->hora_entrada);
        $stmt->bindParam(':hora_salida', $this->hora_salida);
        $stmt->bindParam(':estado', $this->estado);

        if (!empty($this->password)) {
            // Hash de la contraseña
            // $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
            $password_hash = md5($this->password); // Note: Consider using password_hash instead of md5
            $stmt->bindParam(":password", $password_hash);
        }

        $stmt->bindParam(':sync_source', $this->sync_source);
        $stmt->bindParam(':last_sync_time', $this->last_sync_time, PDO::PARAM_NULL | PDO::PARAM_STR);
        if ($creating) {
            $stmt->bindParam(':notion_page_id', $this->notion_page_id);
        } else {
            $stmt->bindParam(':idusuario', $this->idusuario);
        }
    }
    // Crear un nuevo usuario
    public function create()
    {
        try {
            // Sanitizar los campos
            $this->usuario = htmlspecialchars(strip_tags($this->usuario ?? ''));
            $this->dni = htmlspecialchars(strip_tags($this->dni ?? ''));
            $this->celular = htmlspecialchars(strip_tags($this->celular ?? ''));
            $this->correo = htmlspecialchars(strip_tags($this->correo ?? ''));

            // Validar si usuario, dni, celular o correo ya están registrados, ignorando campos vacíos
            $conditions = [];
            $params = [];

            if (!empty($this->usuario)) {
                $conditions[] = "LOWER(TRIM(usuario)) = LOWER(:usuario)";
                $params[':usuario'] = $this->usuario;
            }
            if (!empty($this->dni)) {
                $conditions[] = "TRIM(dni) = :dni";
                $params[':dni'] = $this->dni;
            }
            if (!empty($this->celular)) {
                $conditions[] = "TRIM(celular) = :celular";
                $params[':celular'] = $this->celular;
            }
            if (!empty($this->correo)) {
                $conditions[] = "LOWER(TRIM(correo)) = LOWER(:correo)";
                $params[':correo'] = $this->correo;
            }

            // Si hay condiciones, ejecutar la consulta de verificación
            if (!empty($conditions)) {
                $query_check = "SELECT usuario, dni, celular, correo FROM " . $this->table_name . " WHERE " . implode(' OR ', $conditions);
                $stmt_check = $this->conn->prepare($query_check);

                // Vincular parámetros dinámicamente con nombres
                foreach ($params as $key => $value) {
                    $stmt_check->bindParam($key, $value);
                }

                if ($stmt_check->execute()) {
                    $results = $stmt_check->fetchAll(PDO::FETCH_ASSOC);
                    $errors = [];
                    foreach ($results as $existing) {
                        if (!empty($this->dni) && $existing['dni'] === $this->dni) {
                            $errors[] = "El DNI '" . $this->dni . "' ya está registrado.";
                        }
                        if (!empty($this->usuario) && $existing['usuario'] === $this->usuario) {
                            $errors[] = "El usuario '" . $this->usuario . "' ya está registrado.";
                        }
                        if (!empty($this->celular) && $existing['celular'] === $this->celular) {
                            $errors[] = "El celular '" . $this->celular . "' ya está registrado.";
                        }
                        if (!empty($this->correo) && $existing['correo'] === $this->correo) {
                            $errors[] = "El correo '" . $this->correo . "' ya está registrado.";
                        }
                    }
                    if (!empty($errors)) {
                        return [
                            'success' => false,
                            'message' => implode(' ', $errors)
                        ];
                    }
                }
            }

            // Preparar la consulta de inserción
            $query = "INSERT INTO " . $this->table_name . "
                (nombres, apellidos, usuario, area, tipoempleado, password, estado, hora_entrada, hora_salida, dni, celular, correo, 
                notion_page_id, sync_source, last_sync_time)
                VALUES (:nombres, :apellidos, :usuario, :area, :tipoempleado, :password, :estado, :hora_entrada, :hora_salida, 
                :dni, :celular, :correo, :notion_page_id, :sync_source, :last_sync_time)";

            $stmt = $this->conn->prepare($query);

            // Sanitizar los demás campos
            $this->nombres = htmlspecialchars(strip_tags($this->nombres ?? ''));
            $this->apellidos = htmlspecialchars(strip_tags($this->apellidos ?? ''));
            $this->area = htmlspecialchars(strip_tags($this->area ?? ''));
            $this->tipoempleado = htmlspecialchars(strip_tags($this->tipoempleado ?? ''));
            $this->password = htmlspecialchars(strip_tags($this->password ?? ''));
            $this->estado = htmlspecialchars(strip_tags($this->estado ?? '')); // Agregar si no está definido
            $this->hora_entrada = htmlspecialchars(strip_tags($this->hora_entrada ?? ''));
            $this->hora_salida = htmlspecialchars(strip_tags($this->hora_salida ?? ''));

            // Vincular parámetros manualmente para la consulta de inserción
            $this->bindParams($stmt);

            if ($stmt->execute()) {
                // Obtener el último ID insertado
                $lastId = $this->conn->lastInsertId();
                return [
                    'success' => true,
                    'idusuario' => $lastId,
                    'message' => 'Usuario creado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo ejecutar el INSERT'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en base de datos: ' . $e->getMessage()
            ];
        }
    }

    // Actualizar usuario
    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                SET nombres = :nombres, apellidos = :apellidos, usuario = :usuario, area = :area, 
                    tipoempleado = :tipoempleado, estado = :estado, hora_entrada = :hora_entrada, 
                    hora_salida = :hora_salida, dni = :dni, celular = :celular, correo = :correo,
                    sync_source = :sync_source, last_sync_time = :last_sync_time";

        if (!empty($this->password)) {
            $query .= ", password = :password";
            $this->password = htmlspecialchars(strip_tags($this->password));
        }
        $query .= " WHERE idusuario = :idusuario";

        $stmt = $this->conn->prepare($query);
        // Sanitizar
        $this->nombres = htmlspecialchars(strip_tags($this->nombres));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->tipoempleado = htmlspecialchars(strip_tags($this->tipoempleado));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->hora_entrada = htmlspecialchars(strip_tags($this->hora_entrada));
        $this->hora_salida = htmlspecialchars(strip_tags($this->hora_salida));
        $this->dni = htmlspecialchars(strip_tags($this->dni));
        $this->celular = htmlspecialchars(strip_tags($this->celular));
        $this->correo = htmlspecialchars(strip_tags($this->correo));

        // Vincula todos los parámetros
        $this->bindParams($stmt, false);

        try {
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo ejecutar el UPDATE'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en base de datos: ' . $e->getMessage()
            ];
        }
    }
    public function actualizarEstado()
    {
        $query = "UPDATE " . $this->table_name . " SET estado = ? WHERE idusuario = ?";

        $stmt = $this->conn->prepare($query);

        $this->estado = htmlspecialchars(strip_tags($this->estado));
        // Vincular valores
        $stmt->bindParam(1, $this->estado);
        $stmt->bindParam(2, $this->idusuario);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar usuario
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE idusuario = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idusuario);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Verificar si el correo ya existe
    public function emailExists(): bool
    {
        $query = "SELECT 1 FROM {$this->table_name} WHERE correo = :correo LIMIT 1";

        try {
            $correoLimpio = htmlspecialchars(strip_tags($this->correo));
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':correo', $correoLimpio, PDO::PARAM_STR);
            $stmt->execute();

            return (bool) $stmt->fetchColumn(); // Devuelve true si hay resultado
        } catch (PDOException $e) {
            return false;
        }
    }

    //FUNCION PARA LEER PAGINADO CON USUARIOS FILTRADOS     
    // TODO borrar ?
    public function leerPaginasConFiltros($page, $limit, $area_id, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT u.*, COALESCE(u.estado, 'PC') as estado 
                FROM usuarios u 
                WHERE u.area = :area_id";
        if (!empty($filters['nombres'])) {
            $sql .= " AND (u.nombres LIKE :nombres OR u.apellidos LIKE :nombres)";
        }
        if (!empty($filters['correo'])) {
            $sql .= " AND u.correo LIKE :correo";
        }
        $sql .= " ORDER BY u.idusuario DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
        if (!empty($filters['nombres'])) {
            $nombreParam = "%" . $filters['nombres'] . "%";
            $stmt->bindParam(':nombres', $nombreParam, PDO::PARAM_STR);
        }
        if (!empty($filters['correo'])) {
            $emailParam = "%" . $filters['correo'] . "%";
            $stmt->bindParam(':correo', $emailParam, PDO::PARAM_STR);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    //FUNCION PARA CONTAR USUARIOS FILTRADOS    
    // TODO borrar ?
    public function contaruserconfiltporArea($area_id, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE area = :area_id";
        if (!empty($filters['nombres'])) {
            $sql .= " AND (nombres LIKE :nombres OR apellidos LIKE :nombres)";
        }
        if (!empty($filters['correo'])) {
            $sql .= " AND correo LIKE :correo";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':area_id', $area_id, PDO::PARAM_INT);
        if (!empty($filters['nombres'])) {
            $nombreParam = "%" . $filters['nombres'] . "%";
            $stmt->bindParam(':nombres', $nombreParam, PDO::PARAM_STR);
        }
        if (!empty($filters['correo'])) {
            $emailParam = "%" . $filters['correo'] . "%";
            $stmt->bindParam(':correo', $emailParam, PDO::PARAM_STR);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    // TODO borrar ?
    public function readPageFiltered($page, $limit, $filterName = '', $filterEmail = '')
    {
        // Calcular offset para paginación
        $offset = ($page - 1) * $limit;

        $query = "SELECT 
                u.idusuario, u.nombres, u.apellidos, u.correo, 
                u.area, a.nombres as area_nombre, 
                u. r.nombres as rol_nombre,
                u.hora_entrada, u.hora_salida, u.activo
              FROM 
                usuarios u
              LEFT JOIN 
                areas a ON u.area = a.idusuario
              LEFT JOIN 
                roles r ON u.id_rol = r.idusuario
              WHERE 1=1";

        // Añadir filtros si existen
        if (!empty($filterName)) {
            $query .= " AND (u.nombres LIKE :filterName OR u.apellidos LIKE :filterName)";
        }
        if (!empty($filterEmail)) {
            $query .= " AND u.correo LIKE :filterEmail";
        }

        $query .= " ORDER BY u.idusuario DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        // Bind de parámetros para los filtros
        if (!empty($filterName)) {
            $filterNameParam = "%$filterName%";
            $stmt->bindParam(':filterName', $filterNameParam);
        }
        if (!empty($filterEmail)) {
            $filterEmailParam = "%$filterEmail%";
            $stmt->bindParam(':filterEmail', $filterEmailParam);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }
}
