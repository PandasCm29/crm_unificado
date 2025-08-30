<?php

class EmailCliente
{
    private $conn;
    private $table_name = "groupemail";
    private $relation;

    public $id;
    public $email;

    public function __construct(PDO $db, $relation = "clientes")
    {
        $this->conn = $db;
        $this->relation = $relation;
    }

    /**
     * Obtiene los antecedentes de un cliente por su ID
     * @param int $id
     * @return array
     */
    public function getByClienteId($id)
    {
        $sql = "SELECT 
            ge.idmail AS id,            
            ge.email AS email
        FROM {$this->table_name} ge
        WHERE ge.idtabla = :idcliente AND ge.tabla = :tabla
        ORDER BY ge.email DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idcliente', $id, PDO::PARAM_INT);
        $stmt->bindValue(':tabla',   $this->relation, PDO::PARAM_STR);
        $stmt->execute();
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Formatear resultados, si uno de los emails es NULL o si no tiene formato de email se borra
        // foreach ($emails as $key => $email) {
        //     if (empty($email['email']) || !filter_var($email['email'], FILTER_VALIDATE_EMAIL)) {
        //         unset($emails[$key]);
        //     } else {
        //         $emails[$key]['email'] = strtolower(trim($email['email']));
        //     }
        // }
        return $emails;
    }

    /**
     * Inserta un nuevo antecedente si no existe
     * @param int $idcliente — ID del cliente, no nulo
     * @param int $idusuario — ID del usuario, no nulo
     * @param string $emails — Texto del antecedente, no nulo
     * @return array — true si OK, mensaje de error si falla
     */
    public function update($idcliente, $emails, $sync_source=null)
    {
        // Validar que el ID del cliente es válido
        if (empty($idcliente) || !is_int($idcliente) || $idcliente <= 0) {
            return [
                'success' => false,
                'message' => 'ID del cliente y emails son obligatorios'
            ];
        }

        // Verificar que el cliente exista en la tabla correspondiente
        $query = "SELECT COUNT(*) FROM {$this->relation} WHERE idcliente = :idcliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            return [
                'success' => false,
                'message' => 'El ID del cliente no existe'
            ];
        }

        // Convertir string de emails a array (si viene como string)
        if (is_string($emails)) {
            $emails = array_map('trim', explode(',', $emails));
        }

        // Filtrar emails válidos
        $emails = array_filter($emails, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        // Si no hay correos válidos, no borres ni insertes nada
        if (empty($emails)) {
            return [
                'success' => true,
                'message' => 'Sin cambios en correos: no se enviaron correos válidos'
            ];
        }

        // Eliminar correos anteriores solo si hay nuevos y viene de CRM
        if ($sync_source === "CRM") {
            $sqlDelete = "DELETE FROM {$this->table_name} WHERE idtabla = :idcliente AND tabla = :tabla";
            $checkStmtDelete = $this->conn->prepare($sqlDelete);
            $checkStmtDelete->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $checkStmtDelete->bindParam(':tabla', $this->relation, PDO::PARAM_STR);
            if (!$checkStmtDelete->execute()) {
                return [
                    'success' => false,
                    'message' => "No se pudieron eliminar los emails anteriores del cliente con ID: '$idcliente'"
                ];
            }
        }
        // Preparar consulta de inserción
        $sql = "INSERT INTO {$this->table_name} (tabla, idtabla, email) VALUES (:tabla, :idcliente, :email)";
        $stmt = $this->conn->prepare($sql);

        // Iterar sobre los emails y realizar inserciones
        foreach ($emails as $email) {
            // Evitar duplicados
            $checkQuery = "SELECT COUNT(*) FROM {$this->table_name} WHERE email = :email AND idtabla = :idcliente AND tabla = :tabla";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $checkStmt->bindParam(':tabla', $this->relation, PDO::PARAM_STR);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                continue; // Ya existe
            }

            // Insertar nuevo correo
            $stmt->bindParam(':tabla', $this->relation, PDO::PARAM_STR);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            if (!$stmt->execute()) {
                return [
                    'success' => false,
                    'message' => "Error al insertar el email '$email'"
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Emails insertados correctamente'
        ];
    }
}
