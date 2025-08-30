<?php
// cliente_conexion.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ClientesModel.php';

$cliente = nuevoCliente();

function nuevoCliente(){    
    $idusuario = (int) isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0 ;
    $database = new Database();
    $db = $database->getConnection();
    return new ClienteModel($db, $idusuario);
}