<?php
// pages/alterarSituacao.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId     = (int) ($_POST['pedido_id'] ?? 0);
    $novaSituacao = $_POST['situacao'] ?? '';

    // Atualiza situação no pedido
    if ($pedidoDao->atualizaSituacao($pedidoId, $novaSituacao)) {

        // Busca itens para ajustar estoque
        $itens = $pedidoDao->buscaItens($pedidoId);

        if ($novaSituacao === 'FINALIZADO') {
            // Ao finalizar, subtrai do estoque
            foreach ($itens as $item) {
                $produto = $produtoDao->buscaPorId($item->getProdutoId());
                if ($produto) {
                    $novoEstoque = $produto->getQuantidade() - $item->getQuantidade();
                    // Garante não ficar negativo
                    $produtoDao->atualizarQuantidade(
                        $item->getProdutoId(),
                        max(0, $novoEstoque)
                    );
                }
            }
        } elseif ($novaSituacao === 'CANCELADO') {
            // Ao cancelar, repõe no estoque
            foreach ($itens as $item) {
                $produto = $produtoDao->buscaPorId($item->getProdutoId());
                if ($produto) {
                    $novoEstoque = $produto->getQuantidade() + $item->getQuantidade();
                    $produtoDao->atualizarQuantidade(
                        $item->getProdutoId(),
                        $novoEstoque
                    );
                }
            }
        }
    }
}

// Sempre volta para a listagem de pedidos
header('Location: /index.php/pedidos');
exit;
