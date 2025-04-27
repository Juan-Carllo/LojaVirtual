<?php
include_once "fachada.php";

$usuario = new Usuario(null, null, null, null);

$erro_login = isset($_GET['erro_login']) ? $_GET['erro_login'] : '';
$erro_nome = isset($_GET['erro_nome']) ? $_GET['erro_nome'] : '';
$erro_senha = isset($_GET['erro_senha']) ? $_GET['erro_senha'] : '';
?>

<html>
<head>
    <title>Cadastro de Usuário</title>
</head>
<body>

    <h1>Cadastro de Usuários</h1>

    <form action="salvaUsuario.php" method="post">

        <label for="login">Login:</label>
        <input type="text" value="<?= isset($usuario) ? htmlspecialchars($usuario->getLogin()) : '' ?>" name="login" required />
        <?php if ($erro_login): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_login) ?></span><br>
        <?php endif; ?>
        <br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required />
        <?php if ($erro_senha): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_senha) ?></span><br>
        <?php endif; ?>
        <br>

        <label for="nome">Nome:</label>
        <input type="text" value="<?= isset($usuario) ? htmlspecialchars($usuario->getNome()) : '' ?>" name="nome" required />
        <?php if ($erro_nome): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_nome) ?></span><br>
        <?php endif; ?>
        <br>

        <input type="hidden" name="id" value="<?= isset($usuario) ? $usuario->getId() : '' ?>" />

        <input type="submit" value="Salvar" />

    </form>

    <br>
    <a href="index.php">Voltar</a>

</body>
</html>
