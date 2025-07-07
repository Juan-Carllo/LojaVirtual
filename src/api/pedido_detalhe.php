<?php
function handlePedidoDetalheApi(int $pedidoId): void {
    header('Content-Type: application/json');

    require_once __DIR__ . '/../fachada.php';

    $pedidoDao  = $factory->getPedidoDao();
    $usuarioDao = $factory->getUsuarioDao();
    $produtoDao = $factory->getProdutoDao();

    $pedido = $pedidoDao->buscaPorId($pedidoId);
    if (!$pedido) {
        http_response_code(404);
        echo json_encode(['error' => 'Pedido not found']);
        return;
    }

    $cliente = $usuarioDao->buscaPorId($pedido->getUsuarioId());
    $itens   = $pedidoDao->buscaItens($pedidoId);

    $total = array_reduce($itens, fn($s, $it) => $s + $it->getQuantidade() * $it->getPreco(), 0);

    $response = [
        'pedido' => [
            'id'           => $pedido->getId(),
            'data_pedido'  => $pedido->getDataPedido(),
            'situacao'     => $pedido->getSituacao(),
            'data_entrega' => $pedido->getSituacao() === 'ENTREGUE' ? $pedido->getDataEntrega() : null,
            'total'        => number_format($total, 2, '.', ''),
        ],
        'cliente' => [
            'id'   => $cliente?->getId(),
            'nome' => $cliente?->getNome(),
        ],
        'itens' => array_map(function ($item) use ($produtoDao) {
            $produto = $produtoDao->buscaPorId($item->getProdutoId());
            return [
                'produto_id' => $produto->getId(),
                'nome'       => $produto->getNome(),
                'quantidade' => $item->getQuantidade(),
                'preco'      => number_format($item->getPreco(), 2, '.', ''),
                'subtotal'   => number_format($item->getQuantidade() * $item->getPreco(), 2, '.', ''),
                'imagem'     => $produto->getImagem() ? base64_encode($produto->getImagem()) : null,
            ];
        }, $itens)
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
