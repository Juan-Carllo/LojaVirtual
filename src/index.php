<?php
// index.php (na raiz do projeto)
require_once __DIR__ . '/fachada.php';
session_start();

// debug (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// extrai só o path (sem query string)
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// roteia via PATH_INFO (ex: index.php/login)
switch ($request) {
    case '/':
    case '/index.php':
    case '/index.php/':
    case '/login':
    case '/index.php/login':
        include __DIR__ . '/pages/login.php';
        break;

    case '/registrar':
    case '/index.php/registrar':
        include __DIR__ . '/pages/registrar.php';
        break;

    case '/usuario':
    case '/index.php/usuario':
        include __DIR__ . '/pages/usuario.php';
        break;

    case '/validaLogin':
    case '/index.php/validaLogin':
        include __DIR__ . '/pages/validaLogin.php';
        break;

    case '/salvaUsuario':
    case '/index.php/salvaUsuario':
        include __DIR__ . '/pages/salvaUsuario.php';
        break;

    case '/logout':
    case '/index.php/logout':
        include __DIR__ . '/pages/logout.php';
        break;

    case '/home':
    case '/index.php/home':
        include __DIR__ . '/pages/home.php';
        break;

    default:
        http_response_code(404);
        echo '<h1 style="text-align:center;margin-top:2rem;">404 — Página não encontrada</h1>';
        break;
}
