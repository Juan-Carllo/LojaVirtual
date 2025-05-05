<?php
session_start(); // ✅ Sessão deve vir ANTES de tudo

if (!isset($_SESSION['usuario_id'])) {
    header("Location: validaLogin.php");
    exit;
}

$usuario_nome = $_SESSION['usuario_nome'];
?>

<!DOCTYPE HTML>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<title>Bem-vindo - Projeto Loja</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			text-align: center;
			padding-top: 50px;
			background-color: #f4f4f4;
		}
		h2 {
			color: #4CAF50;
		}
		.logout-btn {
			background-color: #f44336;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}
		.logout-btn:hover {
			background-color: #e60000;
		}
	</style>
</head>

<body>

<h2>Bem-vindo, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>
<p>Você está logado com sucesso no sistema.</p>

<form method="post" action="logout.php">
	<input type="submit" class="logout-btn" value="Logout">
</form>

</body>
</html>
