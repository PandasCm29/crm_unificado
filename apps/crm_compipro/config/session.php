<?php
// === Sesión aislada para CRM COMPIPRO ===
define('APP_SESSION_NAME', 'CRM_COMPIPRO_SESSID'); // nombre único
define('APP_BASE_PATH', '/crm_compipro');          // <- SOLO la ruta de carpeta, no la URL

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure',
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );

    session_name(APP_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => APP_BASE_PATH,                 // <- crítico para aislar
        'domain'   => $_SERVER['HTTP_HOST'] ?? '',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
