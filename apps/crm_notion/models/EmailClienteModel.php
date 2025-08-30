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
        ORDER BY ge.idmail";
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
    public function update($idcliente, $emails, $sync_source = null)
    {
        // Validar parámetros
        if (empty($idcliente) || empty($emails)) {
            return [
                'success' => false,
                'message' => 'ID del cliente y emails son obligatorios'
            ];
        }

        // Validar que el ID del cliente sea un número entero
        if (!is_int($idcliente) || $idcliente <= 0) {
            return [
                'success' => false,
                'message' => 'ID del cliente debe ser un número entero positivo'
            ];
        }

        // Validar que el ID del cliente exista
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

        // Convertir el string de emails en un array
        $emails = array_map('trim', explode(',', $emails));

        
        $sqlDelete =  "DELETE FROM {$this->table_name}  WHERE idtabla = :idcliente";
        $checkStmtDelete = $this->conn->prepare($sqlDelete);
        $checkStmtDelete->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);

        // Eliminar emails del cliente
        if (!$checkStmtDelete->execute()) {
            return [
                'success' => false,
                'message' => "No se pudieron eliminar los emails del cliente con ID: '$idcliente'"
            ];
        }
        // Preparar la consulta de inserción
        $sql = "INSERT INTO {$this->table_name} (tabla, idtabla, email) VALUES (:tabla, :idcliente, :email)";

        // Procesar los correos sin iniciar una nueva transacción
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => "El email '$email' no es válido"
                ];
            }

            // Verificar si el email ya existe
            $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE email = :email";
            $checkStmt = $this->conn->prepare($query);
            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmt->execute();
            if ($checkStmt->fetchColumn() > 0) {
                continue;
            }
            // Preparar y ejecutar inserción
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tabla', $this->relation, PDO::PARAM_STR);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            // Insertar el email
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
