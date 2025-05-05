<?php
// pages/logout.php

// se não tiver seção, inicia nova
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();

// Redireciona para a rota de login via front-controller
header("Location: /index.php/login");
exit;
