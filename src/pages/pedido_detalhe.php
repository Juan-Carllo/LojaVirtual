<?php
// pages/pedido_detalhe.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao   = $factory->getPedidoDao();
$produtoDao  = $factory->getProdutoDao();
$usuarioDao  = $factory->getUsuarioDao();
$conn         = $factory->getConnection();

$pedidoId = (int)($_GET['pedido_id'] ?? 0);
$pedido   = $pedidoDao->buscaPorId($pedidoId);
if (!$pedido) {
    header('Location: /index.php/pedidos');
    exit;
}

// processa a ação (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'CANCELADO') {
        // devolve estoque
        foreach ($pedidoDao->buscaItens($pedidoId) as $it) {
            $p = $produtoDao->buscaPorId($it->getProdutoId());
            $novaQtd = $p->getQuantidade() + $it->getQuantidade();
            $produtoDao->atualizarQuantidade($p->getId(), $novaQtd);
        }
    }
    // atualiza situação (NOVO→ENTREGUE ou NOVO→CANCELADO)
    $pedidoDao->atualizaSituacao($pedidoId, $acao);
    header("Location: /index.php/pedido_detalhe?pedido_id={$pedidoId}");
    exit;
}

// prepara dados para exibição
$cliente = $usuarioDao->buscaPorId($pedido->getUsuarioId());
$itens   = $pedidoDao->buscaItens($pedidoId);
$total   = array_reduce(
    $itens,
    fn($sum, $it) => $sum + $it->getQuantidade() * $it->getPreco(),
    0
);

// mapeia imagens para o carrossel
$slidesMap = [];
foreach ($itens as $item) {
    $prod = $produtoDao->buscaPorId($item->getProdutoId());
    $src  = '';
    foreach (['jpg','jpeg','png','webp'] as $ext) {
        $rel = "/assets/images/{$prod->getId()}.{$ext}";
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $rel)) {
            $src = $rel;
            break;
        }
    }
    if (!$src && $prod->getImagem()) {
        $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $prod->getImagem());
        $b64  = base64_encode($prod->getImagem());
        $src  = "data:{$mime};base64,{$b64}";
    }
    if ($src) {
        $slidesMap[$prod->getId()] = $src;
    }
}
$slides = array_values($slidesMap);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedido #<?= $pedido->getId() ?> – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <?php require __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-6">
      Pedido #<span class="text-red-600"><?= $pedido->getId() ?></span>
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- ESQUERDA: resumo + itens -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Resumo -->
        <div class="bg-white p-6 rounded shadow">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p><strong>Cliente:</strong> <?= htmlspecialchars($cliente->getNome(), ENT_QUOTES) ?></p>
              <p><strong>Data:</strong> <?= htmlspecialchars($pedido->getDataPedido(), ENT_QUOTES) ?></p>
            </div>
            <div>
              <p><strong>Situação:</strong> <?= htmlspecialchars($pedido->getSituacao(), ENT_QUOTES) ?></p>
              <p><strong>Total:</strong> R$ <?= number_format($total, 2, ',', '.') ?></p>
            </div>
          </div>
        </div>

        <!-- Itens -->
        <div class="bg-white rounded shadow overflow-auto">
          <table class="min-w-full">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2">Foto</th>
                <th class="px-4 py-2">Descrição</th>
                <th class="px-4 py-2">Qtd</th>
                <th class="px-4 py-2">Unit.</th>
                <th class="px-4 py-2">Subtotal</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($itens as $item):
              $prod   = $produtoDao->buscaPorId($item->getProdutoId());
              $sub    = $item->getQuantidade() * $item->getPreco();
              $imgSrc = $slidesMap[$prod->getId()] ?? '/assets/placeholder.png';
            ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2">
                  <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES) ?>"
                       class="h-12 w-12 object-contain rounded" alt="">
                </td>
                <td class="px-4 py-2"><?= htmlspecialchars($prod->getNome(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= $item->getQuantidade() ?></td>
                <td class="px-4 py-2">R$ <?= number_format($item->getPreco(), 2, ',', '.') ?></td>
                <td class="px-4 py-2">R$ <?= number_format($sub, 2, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- DIREITA: carrossel -->
      <?php if (count($slides) > 0): ?>
      <div class="w-full h-80 bg-gray-100 rounded shadow overflow-hidden relative">
        <?php foreach ($slides as $i => $url): ?>
          <div class="absolute inset-0 transition-opacity duration-700 <?= $i===0?'opacity-100':'opacity-0' ?>">
            <img src="<?= htmlspecialchars($url, ENT_QUOTES) ?>"
                 class="w-full h-full object-contain p-4"
                 alt="Slide <?= $i+1 ?>" />
          </div>
        <?php endforeach; ?>
      </div>
      <script>
      (function(){
        const slides = document.querySelectorAll('.relative > div');
        let idx = 0;
        setInterval(()=>{
          slides[idx].classList.replace('opacity-100','opacity-0');
          idx = (idx+1)%slides.length;
          slides[idx].classList.replace('opacity-0','opacity-100');
        }, 3000);
      })();
      </script>
      <?php endif; ?>

    </div>

    <!-- BOTÕES DE AÇÃO (apenas se estado for NOVO) -->
    <?php if ($pedido->getSituacao() === 'NOVO'): ?>
    <div class="mt-6 flex space-x-4">
      <form method="post">
        <input type="hidden" name="acao" value="ENTREGUE">
        <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Marcar como Entregue
        </button>
      </form>
      <form method="post">
        <input type="hidden" name="acao" value="CANCELADO">
        <button type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
          Cancelar Pedido
        </button>
      </form>
    </div>
    <?php endif; ?>

  </main>
</body>
</html>
