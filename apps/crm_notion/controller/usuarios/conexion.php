<?php
// conexion.php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/AreaModel.php';

$idusuario = (int) isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0;

function nuevoUsuario()
{
    // $idusuario = (int) isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0 ;
    global $idusuario;

    $database = new Database();
    $db = $database->getConnection();
    return new User($db, $idusuario);
}
function areas()
{
    global $idusuario;
    $database = new Database();
    $db = $database->getConnection();
    return new Area($db, $idusuario);
}
$userModel = nuevoUsuario();
$areas = areas();
