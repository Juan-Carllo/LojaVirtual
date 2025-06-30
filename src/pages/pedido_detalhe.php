<?php
// pages/pedido_detalhe.php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$usuarioDao = $factory->getUsuarioDao();
$produtoDao = $factory->getProdutoDao();

$id = (int)($_GET['pedido_id'] ?? 0);
$pedido = $pedidoDao->buscaPorId($id);
if (!$pedido) {
    header('Location: /index.php/pedidos'); exit;
}

$cliente = $usuarioDao->buscaPorId($pedido->getUsuarioId());
$itens   = $pedidoDao->buscaItens($id);
$total   = array_reduce($itens, fn($s,$it)=>$s+$it->getQuantidade()*$it->getPreco(),0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pedido #<?= $pedido->getId() ?> – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php require __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-6">Pedido #<?= $pedido->getId() ?></h1>

    <!-- Mestre -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente?->getNome() ?? '—', ENT_QUOTES) ?></p>
      <p><strong>Data do Pedido:</strong> <?= htmlspecialchars($pedido->getDataPedido(), ENT_QUOTES) ?></p>
      <p><strong>Situação:</strong> <?= htmlspecialchars($pedido->getSituacao(), ENT_QUOTES) ?></p>
      <?php if ($pedido->getSituacao()==='ENTREGUE'): ?>
        <p><strong>Data da Entrega:</strong> <?= htmlspecialchars($pedido->getDataEntrega(), ENT_QUOTES) ?></p>
      <?php endif; ?>
      <p class="mt-4"><strong>Total:</strong> R$ <?= number_format($total,2,',','.') ?></p>
    </div>

    <!-- Detalhe -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <table class="w-full mb-4">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-4 py-2">Foto</th>
            <th class="px-4 py-2">Produto</th>
            <th class="px-4 py-2">Qtd</th>
            <th class="px-4 py-2">Unit.</th>
            <th class="px-4 py-2">Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $it):
          $prod = $produtoDao->buscaPorId($it->getProdutoId());
          $subtotal = $it->getQuantidade()*$it->getPreco();
        ?>
          <tr class="border-t">
            <td class="px-4 py-2">
              <?php if ($prod->getImagem()): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($prod->getImagem()) ?>"
                     class="h-16 w-16 object-cover rounded"/>
              <?php endif; ?>
            </td>
            <td class="px-4 py-2"><?= htmlspecialchars($prod->getNome(), ENT_QUOTES) ?></td>
            <td class="px-4 py-2"><?= $it->getQuantidade() ?></td>
            <td class="px-4 py-2">R$ <?= number_format($it->getPreco(),2,',','.') ?></td>
            <td class="px-4 py-2">R$ <?= number_format($subtotal,2,',','.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Ações de fluxo -->
    <div class="bg-white p-4 rounded shadow">
      <form method="post" action="/index.php/alterarSituacao" class="space-x-2">
        <input type="hidden" name="pedido_id" value="<?= $pedido->getId() ?>">
        <?php switch($pedido->getSituacao()):
          case 'ABERTO': ?>
            <button name="acao" value="finalizar"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
              Realizar Pagamento
            </button>
            <button name="acao" value="cancelar"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
              Cancelar Pedido
            </button>
          <?php break;
          case 'FINALIZADO': ?>
            <button name="acao" value="entregar"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Marcar como Entregue
            </button>
          <?php break;
          default: ?>
            <!-- Pedido já entregue ou cancelado, sem ações -->
        <?php endswitch; ?>
      </form>
    </div>
  </main>
</body>
</html>
