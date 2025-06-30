<?php
// pages/alterarSituacao.php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();

$id   = (int)($_POST['pedido_id'] ?? 0);
$acao = $_POST['acao'] ?? '';
if (!$id || !in_array($acao, ['finalizar','entregar','cancelar'])) {
    header('Location: /index.php/pedidos'); exit;
}

// carrega o pedido atual
$pedido = $pedidoDao->buscaPorId($id);
if (!$pedido) {
    header('Location: /index.php/pedidos'); exit;
}

switch ($acao) {
    case 'finalizar':
        if ($pedido->getSituacao() === 'ABERTO') {
            // 1) subtrai estoque
            foreach ($pedidoDao->buscaItens($id) as $item) {
                $prod = $produtoDao->buscaPorId($item->getProdutoId());
                $produtoDao->atualizarQuantidade(
                    $prod->getId(),
                    $prod->getQuantidade() - $item->getQuantidade()
                );
            }
            // 2) marca como FINALIZADO (sem data_entrega)
            $pedidoDao->atualizaSituacao($id, 'FINALIZADO');
        }
        break;

    case 'entregar':
        if ($pedido->getSituacao() === 'FINALIZADO') {
            // só agora grava data_entrega
            $pedidoDao->atualizaSituacao($id, 'ENTREGUE');
        }
        break;

    case 'cancelar':
        if ($pedido->getSituacao() === 'ABERTO') {
            // não mexe no estoque (nunca subtraímos)
            $pedidoDao->atualizaSituacao($id, 'CANCELADO');
        }
        break;
}

header('Location: /index.php/pedido_detalhe?pedido_id='.$id);
exit;
