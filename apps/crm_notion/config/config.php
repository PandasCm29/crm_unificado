<?php
require_once __DIR__. '/vendor/autoload.php'; // Si usas Composer

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

// Variables de entorno
define('NOTION_TOKEN', $_ENV['NOTION_TOKEN']);

define('NOTION_DATABASE_ID_CLIENTES', $_ENV['NOTION_DATABASE_ID_CLIENTES']);
define('NOTION_DATABASE_ID_USUARIOS', $_ENV['NOTION_DATABASE_ID_USUARIOS']);

define('NOTION_VERSION', $_ENV['NOTION_VERSION']);

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_PORT', $_ENV['DB_PORT']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
?>