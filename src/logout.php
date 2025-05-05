<?php

session_start();
session_unset();
session_destroy();

// Redireciona para a página inicial
header("Location: index.php");
exit;
?>
