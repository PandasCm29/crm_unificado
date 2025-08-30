<?php
require_once __DIR__.'/config/session.php';
require_once __DIR__ . '/config/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['usuario'] ?? 'Desconocido';
$area = $_SESSION['area'] ?? 'Sin área';
$idusuario = $_SESSION['idusuario'] ?? 0;

$ip = $_SERVER['REMOTE_ADDR'];
$navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';

$fecha = date("Y-m-d H:i:s");

// Ubicación con ip-api
$geo = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
$ciudad = $geo->city ?? 'No detectado';
$pais = $geo->country ?? 'No detectado';

// Crear mensaje
$mensaje = "🚪 *Cierre de sesión detectado*\n"
         . "👤 Usuario: `$usuario`\n"
         . "🆔 ID: `$idusuario`\n"
         . "📁 Área: `$area`\n"
         . "🌐 IP: `$ip`\n"
         . "📍 Ubicación: $ciudad, $pais\n"
         . "🧭 Navegador: `$navegador`\n"
         . "🕒 Fecha: `$fecha`";

// Enviar a Telegram
$token = "7911808885:AAH6jLersOogPHb8tUpFTGXloH-GCgrz8kQ";
$chat_id = "5474348715";

$url = "https://api.telegram.org/bot$token/sendMessage";
$data = [
    'chat_id' => $chat_id,
    'text' => $mensaje,
    'parse_mode' => 'Markdown'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);


// Cerrar sesión
session_unset();
session_destroy();

// Redirigir
header("Location: login.php?logout=1");
exit();
