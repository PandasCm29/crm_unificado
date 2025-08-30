<?php
class Area
{
    private $conn;
    private $table_name = "areas";

    // Propiedades correspondientes a los campos de la tabla
    public $id_area;
    public $area;
    public $status = true;
    public $usuario_sesion;

    public function __construct($db, $id_usuario)
    {
        $this->conn = $db;
        $this->usuario_sesion = $id_usuario;
    }

    // Obtener un solo area por ID
    public function readOne()
    {
        $query = "SELECT nombres, apellidos, area, area, tipoempleado, idpais, estado, hora_entrada, hora_salida, dni, celular, correo 
                  FROM " . $this->table_name . " 
                  WHERE id_area = ? 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_area);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;  // no existe
        }

        // ✅ Creamos una nueva instancia del objeto Cliente
        $nuevoCliente = new Area($this->conn, 0);

        // Llenamos las propiedades públicas que existan:
        foreach ($row as $key => $value) {
            $nuevoCliente->$key = $value;
        }

        return $nuevoCliente;
    }

    // Obtener todos los areas
    public function readAll(){
        $excluidos = ['Telemarketing', 'Sistemas', 'Desarrollo'];
        $placeholders = implode(',', array_fill(0, count($excluidos), '?'));
        $query = "SELECT area FROM " . $this->table_name. " WHERE area NOT IN ($placeholders)";

        $stmt = $this->conn->prepare($query);

        // Vincular los valores excluidos
        foreach ($excluidos as $index => $area) {
            $stmt->bindValue($index + 1, $area); // bindValue usa índices base 1
        }
        try {
            // Ejecutar la consulta
            $stmt->execute();

            // Obtener los resultados
            $areas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $area = $row['area'];
                // Mapear las áreas según el requisito
                if ($area === 'Recursos Humanos') {
                    $areas['GTH'] = $area;
                } elseif ($area === 'Gerencia (Admin)') {
                    $areas['Gerencia'] = $area;
                } elseif ($area === 'Administracion') {
                    $areas['Administración'] = $area;
                } elseif ($area === 'Logistica') {
                    $areas['Logística'] = $area;
                } elseif ($area === 'Programacion') {
                    $areas['Programación'] = $area;
                } elseif ($area === 'Gerencia (Admin)') {
                    $areas['Gerencia'] = $area;
                } else {
                    $areas[$area] = $area; // Para otras áreas, la clave y el valor son iguales
                }
            }

            // Devolver el resultado en formato JSON
            return [
                'success' => true,
                'data' => $areas
            ];
        } catch (PDOException $e) {
            // Manejo de errores
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }
    public function countAll()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
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

    // Leer página de areas de un área concreta
    public function readPageByArea($page = 1, $limit = 20, $area_id=null)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT u.*, a.nombres AS area_nombre
                  FROM " . $this->table_name . " u
                  LEFT JOIN areas a ON u.area = a.id_area
                  WHERE u.area = ? AND u.id_area != ?
                  LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $area_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->usuario_sesion);
        $stmt->bindParam(3, $offset,  PDO::PARAM_INT);
        $stmt->bindParam(4, $limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function readPage($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT id_area, nombres, apellidos, area, area, estado
                FROM " . $this->table_name . " 
                WHERE id_area != ?
                LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);

        $area_actual = $_SESSION['area_id'] ?? 1;

        $stmt->bindParam(1, $area_actual, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->bindParam(3, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo area
    public function create()
    {
        $this->area = htmlspecialchars(strip_tags($this->area));
        // Validar si area, dni, celular o correo ya están registrados
        $query_check = "SELECT area FROM " . $this->table_name . " WHERE area = ";

        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(1, $this->area);
        try {
            $stmt_check->execute();
            $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $errors = [];
                if ($existing['area'] === (int)$this->area) {
                    $errors[] = "El área '" . $this->area . "' ya está registrada.";
                }

                return [
                    'success' => false,
                    'message' => implode(' ', $errors)
                ];
            }

            $query = "INSERT INTO " . $this->table_name . "
                    (area, status) VALUES (?, ?)";

            $stmt = $this->conn->prepare($query);


            // Vincular valores
            $stmt->bindParam(1, $this->area);
            $stmt->bindParam(2, $this->status);

            if ($stmt->execute()) {
                // Obtener el último ID insertado
                $lastId = $this->conn->lastInsertId();
                return [
                    'success' => true,
                    'id_area' => $lastId,
                    'message' => 'Área creada correctamente'
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

    // Actualizar area
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET area = ?, status = ? WHERE id_area = ?";

        $stmt = $this->conn->prepare($query);
        // Sanitizar
        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Vincular valores
        $stmt->bindParam(1, $this->area);
        $stmt->bindParam(2, $this->status);

        try {
            if ($stmt->execute()) {
                // Obtener el último ID insertado
                $lastId = $this->conn->lastInsertId();
                return [
                    'success' => true,
                    'id_area' => $lastId,
                    'message' => 'Área creada correctamente'
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
        $query = "UPDATE " . $this->table_name . " SET estado = ? WHERE id_area = ?";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        // Vincular valores
        $stmt->bindParam(1, $this->status);
        $stmt->bindParam(2, $this->id_area);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar area
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_area = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_area);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}