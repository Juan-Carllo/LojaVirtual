<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../fachada.php';
$dao = $factory->getProdutoDao();

$id  = (int)($_POST['id']  ?? 0);
$qty = (int)($_POST['qty'] ?? 0);
if (!$id || $qty < 1) {
  echo json_encode(['success'=>false,'message'=>'Quantidade inválida.']);
  exit;
}

$p = $dao->buscaPorId($id);
if (!$p) {
  echo json_encode(['success'=>false,'message'=>'Produto não encontrado.']);
  exit;
}

if ($qty > $p->getQuantidade()) {
  echo json_encode([
    'success'=>false,
    'message'=>"Só temos {$p->getQuantidade()} em estoque."
  ]);
  exit;
}

// adiciona ou atualiza
$_SESSION['carrinho'][$id] = $qty;
echo json_encode(['success'=>true,'message'=>'Carrinho atualizado.']);
