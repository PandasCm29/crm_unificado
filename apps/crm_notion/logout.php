<?php
require_once __DIR__.'/config/auth.php';

// Verificar si la sesión está activa
if (isset($_SESSION['idusuario'])) {
    // Limpiar las variables de sesión
    session_unset();

    // Destruir la sesión
    session_destroy();

    // Redirigir al login con un mensaje de éxito
    header("Location: ".$base."login.php?message=logged_out");
    exit();
} else {
    // Si no hay sesión activa, redirigir al login
    header("Location: ".$base."login.php?message=sinSesionActiva");
    exit();
}
