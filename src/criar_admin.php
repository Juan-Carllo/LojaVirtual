<?php
include_once "fachada.php";

// Gera o hash da senha
$senha = "admin";
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Cria o usuário com os 4 argumentos exigidos
$usuario = new Usuario(null, "admin", $hash, "Administrador");

// Acessa o DAO
$dao = $factory->getUsuarioDao();

// Remove se já existir
$existente = $dao->buscaPorLogin("admin");
if ($existente) {
    $dao->remove($existente);
}

// Insere novo
$dao->insere($usuario);

echo "✅ Usuário admin criado com sucesso!\n";
