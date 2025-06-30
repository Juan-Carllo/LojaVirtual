<?php
// pages/finalizarPedido.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();

$userId = (int) $_SESSION['usuario_id'];
$carrinho = $_SESSION['carrinho'] ?? [];

// 1. Insere pedido
$pedido = new Pedido($userId, date('Y-m-d H:i:s'), null, 'FECHADO');
$pedidoId = $pedidoDao->insere($pedido);
if ($pedidoId < 0) {
    die("Erro ao criar pedido");
}

// 2. Para cada item do carrinho
foreach ($carrinho as $prodId => $qty) {
    $produto = $produtoDao->buscaPorId($prodId);
    if (!$produto) continue;

    // 2.1 Confere estoque
    if ($qty > $produto->getQuantidade()) {
        die("Você pediu $qty, mas só temos ".$produto->getQuantidade()." disponíveis.");
    }

    // 2.2 Insere item
    $item = new ItemPedido($pedidoId, $prodId, $qty, $produto->getPreco());
    $pedidoDao->insereItem($item);

    // 2.3 Subtrai do estoque
    $novoEst = $produto->getQuantidade() - $qty;
    $produtoDao->atualizarQuantidade($prodId, $novoEst);
}

// 3. Limpa carrinho e redireciona
unset($_SESSION['carrinho']);
header('Location: /index.php/pedidos');
exit;
