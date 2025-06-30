<?php
// pages/pedidos.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$usuarioDao = $factory->getUsuarioDao();

$userId   = $_SESSION['usuario_id']   ?? null;
$isAdmin  = ($_SESSION['usuario_tipo'] ?? '') === 'admin';

// paginação
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;

if ($isAdmin) {
    // administradores veem todos os pedidos
    $total   = $pedidoDao->contaTodos();
    $pedidos = $pedidoDao->buscaPagina($perPage, $offset);
} else {
    // clientes só veem seus próprios
    $all     = $pedidoDao->buscaTodos();
    $mine    = array_filter($all, fn($p) => $p->getUsuarioId() === $userId);
    $total   = count($mine);
    $pedidos = array_slice($mine, $offset, $perPage);
}

$totalPages = (int) ceil($total / $perPage);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pedidos – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <?php require __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Pedidos</h1>

    <table class="min-w-full bg-white rounded shadow overflow-hidden mb-4">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="px-4 py-2">#</th>
          <th class="px-4 py-2">Data</th>
          <th class="px-4 py-2">Cliente</th>
          <th class="px-4 py-2">Total</th>
          <th class="px-4 py-2">Situação</th>
          <th class="px-4 py-2">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pedidos as $p):
        // busca o nome do cliente
        $cli = $usuarioDao->buscaPorId($p->getUsuarioId());
        // calcula total do pedido
        $itens = $pedidoDao->buscaItens($p->getId());
        $totalPedido = array_reduce(
          $itens,
          fn($sum, $it) => $sum + $it->getQuantidade() * $it->getPreco(),
          0
        );
      ?>
        <tr>
          <td class="px-4 py-2"><?= $p->getId() ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($p->getDataPedido(), ENT_QUOTES) ?></td>
          <td class="px-4 py-2">
            <?= htmlspecialchars($cli?->getNome() ?? '—', ENT_QUOTES) ?>
          </td>
          <td class="px-4 py-2">
            R$ <?= number_format($totalPedido, 2, ',', '.') ?>
          </td>
          <td class="px-4 py-2"><?= htmlspecialchars($p->getSituacao(), ENT_QUOTES) ?></td>
          <td class="px-4 py-2">
            <a href="/index.php/pedido_detalhe?pedido_id=<?= $p->getId() ?>"
               class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
              Ver detalhes
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- paginação -->
    <div class="flex space-x-2">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="px-3 py-1 rounded <?= $i === $page
             ? 'bg-red-600 text-white'
             : 'bg-white border hover:bg-gray-200' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </main>

</body>
</html>
