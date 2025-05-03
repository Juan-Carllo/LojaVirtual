<?php
include_once "fachada.php";

session_start();

// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Captura da URL requisitada
$request = $_SERVER['REQUEST_URI'];

// Remove parâmetros da URL (como ?id=123)
$request = parse_url($request, PHP_URL_PATH);

// Roteamento manual
switch ($request) {
    case '/':
    case '/index.php':   // ✅ Suporte para /index.php
    case '/login':
        include 'pages/login.php';
        break;

    case '/usuario':
        include 'pages/usuario.php';
        break;

    default:
        http_response_code(404);
        echo "404 - Page not found";
        break;
}
?>
