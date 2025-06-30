<?php
// pages/pedido_detalhe.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoId = isset($_GET['pedido_id']) ? (int) $_GET['pedido_id'] : null;
if (!$pedidoId) {
    echo "<p class='p-6 text-red-600'>Pedido não informado.</p>";
    exit;
}

$pedidoDao  = $factory->getPedidoDao();
$usuarioDao = $factory->getUsuarioDao();
$produtoDao = $factory->getProdutoDao();

$pedido = $pedidoDao->buscaPorId($pedidoId);
if (!$pedido) {
    echo "<p class='p-6 text-red-600'>Pedido não encontrado.</p>";
    exit;
}

// carrega itens e cliente
$itens   = $pedidoDao->buscaItens($pedidoId);
$cliente = $usuarioDao->buscaPorId($pedido->getUsuarioId());

// detecta se há pelo menos uma imagem
$hasImages = false;
foreach ($itens as $it) {
    $prod = $produtoDao->buscaPorId($it->getProdutoId());
    if ($prod && $prod->getImagem()) {
        $hasImages = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pedido #<?= $pedido->getId() ?> – Detalhe</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

  <?php require __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Pedido #<?= $pedido->getId() ?></h1>

    <div class="mb-6 space-y-1">
      <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente?->getNome() ?? '—') ?></p>
      <p><strong>Data do pedido:</strong> <?= htmlspecialchars($pedido->getDataPedido()) ?></p>
      <p><strong>Situação:</strong> <?= htmlspecialchars($pedido->getSituacao()) ?></p>
      <?php if ($pedido->getDataEntrega()): ?>
        <p><strong>Data de entrega:</strong> <?= htmlspecialchars($pedido->getDataEntrega()) ?></p>
      <?php endif; ?>
    </div>

    <?php if ($pedido->getSituacao() === 'NOVO'): ?>
      <form method="post" action="/index.php/alterarSituacao" class="mb-6 flex space-x-4">
        <input type="hidden" name="id" value="<?= $pedido->getId() ?>">
        <button name="situacao" value="ENTREGUE"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Marcar como entregue
        </button>
        <button name="situacao" value="CANCELADO"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
          Cancelar pedido
        </button>
      </form>
    <?php endif; ?>

    <?php if ($hasImages): ?>
      <div class="mb-6 flex space-x-4 overflow-x-auto">
        <?php foreach ($itens as $it):
          $prod = $produtoDao->buscaPorId($it->getProdutoId());
          if (!$prod || !$prod->getImagem()) continue;
          $src = 'data:image/jpeg;base64,' . base64_encode($prod->getImagem());
        ?>
          <img src="<?= $src ?>"
               alt="<?= htmlspecialchars($prod->getNome()) ?>"
               class="h-24 w-24 object-cover rounded">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <table class="table-fixed w-full bg-white rounded shadow overflow-hidden">
      <colgroup>
        <col class="w-2/5">
        <col class="w-1/5">
        <col class="w-1/5">
        <col class="w-1/5">
      </colgroup>
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 text-left">Produto</th>
          <th class="px-4 py-2 text-center">Qtd</th>
          <th class="px-4 py-2 text-right">Unitário</th>
          <th class="px-4 py-2 text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($itens as $it):
          $prod     = $produtoDao->buscaPorId($it->getProdutoId());
          $nome     = $prod?->getNome() ?? '—';
          $unit     = $it->getPreco();
          $subtotal = $it->getQuantidade() * $unit;
        ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($nome) ?></td>
          <td class="px-4 py-2 text-center"><?= $it->getQuantidade() ?></td>
          <td class="px-4 py-2 text-right">
            R$ <?= number_format($unit, 2, ',', '.') ?>
          </td>
          <td class="px-4 py-2 text-right">
            R$ <?= number_format($subtotal, 2, ',', '.') ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

</body>
</html>
