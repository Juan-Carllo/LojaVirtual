<?php
include_once "fachada.php";

$erro_login = $erro_nome = $erro_senha = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"] ?? null;
    $nome  = $_POST["nome"]  ?? null;
    $senha = $_POST["senha"] ?? null;
    $id    = $_POST["id"]    ?? null;

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

    // só busca o DAO depois das validações básicas
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
        $tipo       = 'cliente';  // <-- quinto parâmetro padrão

        if ($id) {
            $usuario = $dao->buscaPorId($id);
            $usuario->setLogin($login);
            $usuario->setNome($nome);
            $usuario->setSenha($senha_hash);
            $dao->altera($usuario);
        } else {
            // aqui passamos o 'tipo' como quinto argumento
            $usuario = new Usuario(null, $login, $senha_hash, $nome, $tipo);
            $dao->insere($usuario);
        }

        header("Location: index.php");
        exit;
    } else {
        // monta a query string de erros
        $qs = http_build_query([
            'erro_login' => $erro_login,
            'erro_nome'  => $erro_nome,
            'erro_senha' => $erro_senha
        ]);
        header("Location: criarUsuario.php?$qs");
        exit;
    }
}
?>
