<?php
// cliente_conexion.php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/RequerimientoModel.php';

$idUsuarioSesion = (int) $_SESSION['idusuario'];
$database = new Database();
$db = $database->getConnection();
$requerimiento = new Requerimiento($db, $idUsuarioSesion);
