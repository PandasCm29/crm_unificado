<?php
session_start();

$base = "/crm_notion_J/";


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
    }
}
function isTIArea() {
    return getUserArea() === 'Programacion' || getUserArea() === 'Soporte'; // 1 = admin_dios
}

function isWebsocketArea(){
    return in_array(getUserArea(), ['Administracion', 'Logistica', 'Comercial', 'Programacion', 'Soporte'], true );
}
function requireTIArea() { 
    global $base;   
    requireLogin();
    if (!isTIArea()) {
        header("Location: ".$base."unauthorized.php");
        exit();
    }else{
        $currentUser = json_encode(["user" => $_SESSION['usuario'], "area" => $_SESSION['area']]);
        echo "<script>var base = '$base'; var currentUser = $currentUser</script>";
    }
}
function requireRegularArea() { 
    global $base;   
    if (!isLoggedIn()) {
        header("Location:".$base."login.php");
        exit();
    }else{
        $currentUser = json_encode(["user" => $_SESSION['usuario'], "area" => $_SESSION['area']]);
        echo "<script>var base = '$base'; var currentUser = $currentUser</script>";
    }
}
function isAdminDios() {
    return getUserRole() == 1; // 1 = admin_dios
}
function isAdminArea() {
    return getUserRole() == 2; // 2 = admin_area
}
