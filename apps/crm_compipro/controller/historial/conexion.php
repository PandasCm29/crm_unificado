<?php
// cliente_conexion.php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/HistorialUsuarioClienteModel.php';

$database = new Database();
$db = $database->getConnection();
$historial = new HistorialUsuarioCliente($db);