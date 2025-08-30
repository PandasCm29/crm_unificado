<?php
require_once __DIR__.'/session.php';
$base = "/crm_compipro/";


function getUserRole() {
    // return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    return 0;
}

function getUserArea() {
    return isset($_SESSION['area']) ? $_SESSION['area'] : null;
}

function isRegularUser() {
    return getUserRole() == 3; // 3 = usuario
}
function isLoggedIn() {
    return isset($_SESSION['idusuario']);
}
function requireLogin() {
    global $base;
    if (!isLoggedIn()) {
        header("Location:".$base."login.php");
        exit();
    }else{
        echo "<script>var base = '$base';</script>";
    }
}
function isTIArea() {
    return getUserArea() === 'Programacion' || getUserArea() === 'Soporte'; // 1 = admin_dios
}
function requireTIArea() { 
    global $base;   
    requireLogin();
    if (!isTIArea()) {
        header("Location: ".$base."unauthorized.php");
        exit();
    }
}
function isAdminDios() {
    return getUserRole() == 1; // 1 = admin_dios
}
function isAdminArea() {
    return getUserRole() == 2; // 2 = admin_area
}
