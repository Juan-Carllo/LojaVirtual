<?php
// pages/logout.php

// Já iniciou sessão no index.php
session_unset();
session_destroy();

// Redireciona para a rota de login via front-controller
header("Location: /index.php/login");
exit;
