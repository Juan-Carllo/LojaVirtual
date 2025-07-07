<?php
function handlePedidosApi(int $page = 1, int $perPage = 10, ?string $clienteNome = null): void {
    header('Content-Type: application/json');

    require_once __DIR__ . '/../fachada.php';

    $pedidoDao  = $factory->getPedidoDao();
    $usuarioDao = $factory->getUsuarioDao();

    $user  = $usuarioDao->buscaPorLogin($clienteNome);
    $userId  = $user->getId();

    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;

    $all = $pedidoDao->buscaTodos();
    $mine = array_filter($all, fn($p) => $p->getUsuarioId() === $userId);
    $total = count($mine);
    $pedidos = array_slice($mine, $offset, $perPage);

    $totalPages = (int) ceil($total / $perPage);

    $result = [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
        'pedidos' => []
    ];

    foreach ($pedidos as $p) {
        $cli = $usuarioDao->buscaPorId($p->getUsuarioId());
        $itens = $pedidoDao->buscaItens($p->getId());
        $totalPedido = array_reduce(
            $itens,
            fn($sum, $it) => $sum + $it->getQuantidade() * $it->getPreco(),
            0
        );

        $result['pedidos'][] = [
            'id' => $p->getId(),
            'data_pedido' => $p->getDataPedido(),
            'cliente' => $cli?->getNome() ?? null,
            'total' => number_format($totalPedido, 2, '.', ''),
            'situacao' => $p->getSituacao()
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
