<?php
session_start();
header('Content-Type: application/json');
$id = (int)($_POST['id'] ?? 0);
unset($_SESSION['carrinho'][$id]);
echo json_encode(['success'=>true]);
