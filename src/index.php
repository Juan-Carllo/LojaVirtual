<?php

// captura apenas o path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// assets
if (preg_match('#^(/index\.php)?/assets/#', $request)) {
    $staticPath = preg_replace('#^/index\.php#', '', $request);
    $file = __DIR__ . $staticPath;
    if (file_exists($file) && !is_dir($file)) {
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

// inicia seção
session_start();
require_once __DIR__ . '/fachada.php';

// TODO remover quando não precisar de debug de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// roteamento
switch ($request) {
    // login / home público
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

    // área de administração
    case '/usuario':
    case '/index.php/usuario':
        include __DIR__ . '/pages/usuario.php';
        break;

    case '/produto':
    case '/index.php/produto':
        include __DIR__ . '/pages/produto.php';
        break;

    case '/fornecedor':
    case '/index.php/fornecedor':
        include __DIR__ . '/pages/fornecedor.php';
        break;

    case '/estoque':
    case '/index.php/estoque':
        include __DIR__ . '/pages/estoque.php';
        break;

    case '/finalizarPedido':
    case '/index.php/finalizarPedido':
        include __DIR__ . '/pages/finalizarPedido.php';
        break;

    case '/pedidos':
    case '/index.php/pedidos':
    include __DIR__ . '/pages/pedidos.php';
    break;

    case '/pedido_detalhe':
    case '/index.php/pedido_detalhe':
        include __DIR__ . '/pages/pedido_detalhe.php';
        break;

    case '/alterarSituacao':
    case '/index.php/alterarSituacao':
    include __DIR__ . '/pages/alterarSituacao.php';
    break;

    // autenticação / ações
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
    
  

    // 404
    default:
        http_response_code(404);
        echo '<h1 style="text-align:center;margin-top:2rem;">404 — Página não encontrada</h1>';
        break;
}
