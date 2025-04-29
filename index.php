<?php
include_once "fachada.php";

session_start();
?>

<?php 


// Get the requested URL path
$request = $_SERVER['REQUEST_URI'];

// Remove query string (like ?id=123)
$request = parse_url($request, PHP_URL_PATH);

// Simple router
switch ($request) {
    case '/':
      include 'pages/login.php'; 
      break;

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
