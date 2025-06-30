<?php
// pages/finalizarPedido.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();

// Se não estiver logado redireciona
if (empty($_SESSION['usuario_id'])) {
    header('Location: /index.php/login');
    exit;
}

$userId = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Cria o pedido COM status ABERTO
    $pedido = new Pedido(
        $userId,
        date('Y-m-d H:i:s'),
        null,           // data_entrega NULL
        'ABERTO'        // status inicial
    );
    $pedidoId = $pedidoDao->insere($pedido);

    if ($pedidoId < 0) {
        die('Erro ao criar pedido.');
    }

    // 2) Insere cada item, sem mexer no estoque agora
    foreach ($_SESSION['carrinho'] as $produtoId => $qty) {
        $p = $produtoDao->buscaPorId((int)$produtoId);
        if (!$p) continue;
        $item = new ItemPedido(
            $pedidoId,
            (int)$produtoId,
            (int)$qty,
            $p->getPreco()
        );
        $pedidoDao->insereItem($item);
    }

    // 3) Limpa o carrinho e redireciona para a lista de pedidos
    $_SESSION['carrinho'] = [];
    header('Location: /index.php/pedidos');
    exit;
}

// Se GET, apenas exibe o resumo e botão de “Finalizar Pedido”
$total = 0;
$itens = [];
foreach ($_SESSION['carrinho'] as $pid => $qty) {
    $p = $produtoDao->buscaPorId((int)$pid);
    if (!$p) continue;
    $subtotal = $p->getPreco() * $qty;
    $itens[]  = ['produto' => $p, 'qty' => $qty, 'subtotal' => $subtotal];
    $total   += $subtotal;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Finalizar Pedido – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php require __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Resumo do Pedido</h1>
    <?php if (empty($itens)): ?>
      <p>Seu carrinho está vazio.</p>
    <?php else: ?>
      <table class="w-full bg-white rounded shadow mb-6">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-4 py-2">Produto</th>
            <th class="px-4 py-2">Qtd</th>
            <th class="px-4 py-2">Unit.</th>
            <th class="px-4 py-2">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($itens as $it): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= htmlspecialchars($it['produto']->getNome(), ENT_QUOTES) ?></td>
              <td class="px-4 py-2"><?= $it['qty'] ?></td>
              <td class="px-4 py-2">R$ <?= number_format($it['produto']->getPreco(),2,',','.') ?></td>
              <td class="px-4 py-2">R$ <?= number_format($it['subtotal'],2,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="bg-white p-4 rounded shadow mb-6">
        <p class="text-right font-bold">Total: R$ <?= number_format($total,2,',','.') ?></p>
      </div>
      <form method="post">
        <button type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
          Finalizar Pedido
        </button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
