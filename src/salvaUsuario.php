<?php
include_once "fachada.php";

$erro_login = $erro_nome = $erro_senha = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = isset($_POST["login"]) ? $_POST["login"] : null;
    $nome = isset($_POST["nome"]) ? $_POST["nome"] : null;
    $senha = isset($_POST["senha"]) ? $_POST["senha"] : null;
    $id = isset($_POST["id"]) ? $_POST["id"] : null;

    $valido = true;

    if (empty($login)) {
        $erro_login = "Login é obrigatório!";
        $valido = false;
    }

    if (empty($nome)) {
        $erro_nome = "Nome é obrigatório!";
        $valido = false;
    }

    if (empty($senha)) {
        $erro_senha = "Senha é obrigatória!";
        $valido = false;
    }

    if ($valido) {
        $dao = $factory->getUsuarioDao();
        $usuarioExistente = $dao->buscaPorLogin($login);

        if ($usuarioExistente && $usuarioExistente->getId() != $id) {
            $erro_login = "Erro: O login '$login' já está em uso. Escolha outro.";
            $valido = false;
        }
    }

    if ($valido) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        if ($id) {
            $usuario = $dao->buscaPorId($id);
            $usuario->setLogin($login);
            $usuario->setNome($nome);
            $usuario->setSenha($senha_hash);
            $dao->altera($usuario);
        } else {
            $usuario = new Usuario(null, $login, $senha_hash, $nome);
            $dao->insere($usuario);
        }

        header("Location: index.php");
        exit;
    } else {
        header("Location: criarUsuario.php?erro_login=$erro_login&erro_nome=$erro_nome&erro_senha=$erro_senha");
        exit;
    }
}
?>
