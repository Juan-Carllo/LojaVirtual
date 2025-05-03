<?php
include_once "fachada.php";

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$login = $_POST['login'];
$senha = $_POST['senha'];  // Senha real oficial

$dao = $factory->getUsuarioDao();
$usuario = $dao->buscaPorLogin($login);

if ($usuario) {
    if (password_verify($senha, $usuario->getSenha())) {
        
        $_SESSION['usuario_nome'] = $usuario->getNome();
        $_SESSION['usuario_id'] = $usuario->getId();
        header("Location: home.php");
        exit;
    } else {
        
        $_SESSION['erro_login'] = 'Senha inválida.';
        header("Location: index.php");  
        exit;
    }
} else {
    $_SESSION['erro_login'] = 'Usuário não encontrado.';
    header("Location: index.php");  
    exit;
}


?>
