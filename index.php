<?php
include_once "fachada.php";

session_start();
?>

<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Projeto Loja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
            padding-top: 100px;
        }
        form {
            background-color: white;
            padding: 20px;
            margin: auto;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<h2>Login no Sistema</h2>

<?php if (isset($_SESSION['erro_login'])): ?>
    <p class="error-message">
        <?= htmlspecialchars($_SESSION['erro_login']); ?>
    </p>
    <?php 
    unset($_SESSION['erro_login']);
    ?>
<?php endif; ?>

<form method="post" action="validaLogin.php">
    <label for="login">Login:</label><br>
    <input type="text" id="login" name="login" required><br><br>

    <label for="senha">Senha:</label><br>
    <input type="password" id="senha" name="senha" required><br><br>

    <input type="submit" value="Entrar">
</form>

<br>
<a href="CriarUsuario.php">Criar nova conta</a>
<br>
<br>

<a href="CriarUsuario.php">Editar Conta já existente</a>

</body>
</html>
