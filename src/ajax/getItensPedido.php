<?php
// ajax/getItensPedido.php
session_start();
require_once __DIR__ . '/../fachada.php';

header('Content-Type: application/json');

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();

$pedidoId = (int)($_GET['pedido_id'] ?? 0);
$rawItens = $pedidoDao->buscaItens($pedidoId);

$out = [];
foreach ($rawItens as $it) {
    $p = $produtoDao->buscaPorId($it->getProdutoId());
    $out[] = [
      'nome'     => $p->getNome(),
      'quantidade'=> $it->getQuantidade(),
      'preco'    => $it->getPreco(),
      'subtotal' => $it->getQuantidade() * $it->getPreco(),
      'img'      => $p->getImagem()
                    ? 'data:image/jpeg;base64,' . base64_encode($p->getImagem())
                    : '/assets/placeholder.png'
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
