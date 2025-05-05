<?php
// index.php (na raiz do projeto)

// captura apenas o path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1) Servir estáticos (assets/css/js/imagens) tanto em /assets/... quando em /index.php/assets/... 
if (preg_match('#^(/index\.php)?/assets/#', $request)) {
    // remove o /index.php prefixo, se houver
    $staticPath = preg_replace('#^/index\.php#', '', $request);
    $file = __DIR__ . $staticPath;
    if (file_exists($file) && !is_dir($file)) {
        // envia mime e conteúdo
        $mime = mime_content_type($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        http_response_code(404);
        exit;
    }
}

require_once __DIR__ . '/fachada.php';
session_start();

// debug (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2) Roteamento via PATH_INFO
switch ($request) {
    case '/':
    case '/index.php':
    case '/login':
    case '/index.php/login':
        include __DIR__ . '/pages/login.php';
        break;

    case '/registrar':
    case '/index.php/registrar':
        include __DIR__ . '/pages/registrar.php';
        break;

    case '/home':
    case '/index.php/home':
        include __DIR__ . '/pages/home.php';
        break;

    case '/usuario':
    case '/index.php/usuario':
        include __DIR__ . '/pages/usuario.php';
        break;

    case '/validaLogin':
    case '/index.php/validaLogin':
        include __DIR__ . '/validaLogin.php';
        break;

    case '/salvaUsuario':
    case '/index.php/salvaUsuario':
        include __DIR__ . '/salvaUsuario.php';
        break;

    case '/logout':
    case '/index.php/logout':
        include __DIR__ . '/logout.php';
        break;

    default:
        http_response_code(404);
        echo '<h1 style="text-align:center;margin-top:2rem;">404 — Página não encontrada</h1>';
        break;
}
