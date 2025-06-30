<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../fachada.php';

$dao = $factory->getProdutoDao();
$cart = $_SESSION['carrinho'] ?? [];
$items = [];
$total = 0;

foreach ($cart as $id => $qty) {
    $p = $dao->buscaPorId((int)$id);
    if (!$p) continue;
    $line = $p->getPreco() * $qty;
    $items[] = [
      'id'    => $id,
      'nome'  => $p->getNome(),
      'preco' => $p->getPreco(),
      'qty'   => $qty,
      'max'   => $p->getQuantidade(),
      'line'  => $line
    ];
    $total += $line;
}

echo json_encode([
  'items' => $items,
  'total' => $total,
  'count' => array_sum($cart)
]);
