<?php
// pages/home.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../fachada.php';
$produtoDao = $factory->getProdutoDao();

$q = trim($_GET['q'] ?? '');

try {
    if ($q !== '') {
        if (ctype_digit($q)) {
            // busca combinada por ID e nome
            $byId   = $produtoDao->buscaPorId((int)$q);
            $byName = $produtoDao->buscaPorNome($q);
            $tmp    = [];
            if ($byId) {
                $tmp[$byId->getId()] = $byId;
            }
            foreach ($byName as $p) {
                $tmp[$p->getId()] = $p;
            }
            $produtos = array_values($tmp);
        } else {
            // apenas por nome
            $produtos = $produtoDao->buscaPorNome($q);
        }
    } else {
        // sem filtro, traz todos
        $produtos = $produtoDao->buscaTodos();
    }
} catch (Exception $e) {
    echo "<p class='text-red-600'>Erro: {$e->getMessage()}</p>";
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Amigos do Casa – Produtos</title>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

  <?php require_once __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6 pt-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($produtos as $p): ?>
        <?php
          // determina src da imagem // PNG/JPG/WebP
          $src = '/assets/placeholder.png';
          foreach (['jpg','jpeg','png','webp'] as $ext) {
              $rel = "/assets/images/{$p->getId()}.{$ext}";
              if (file_exists($_SERVER['DOCUMENT_ROOT'] . $rel)) {
                  $src = $rel;
                  break;
              }
          }
          // senão, se houver blob, converte
          if ($src === '/assets/placeholder.png' && $p->getImagem()) {
              $finfo  = finfo_open(FILEINFO_MIME_TYPE);
              $mime   = finfo_buffer($finfo, $p->getImagem());
              finfo_close($finfo);
              $base64 = base64_encode($p->getImagem());
              $src    = "data:{$mime};base64,{$base64}";
          }
        ?>
        <div class="bg-white rounded shadow overflow-hidden flex flex-col">
          <div class="h-48 bg-gray-100 flex items-center justify-center p-2">
            <img
              src="<?= htmlspecialchars($src, ENT_QUOTES) ?>"
              alt="<?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>"
              class="max-h-full max-w-full object-contain"
            />
          </div>
          <div class="p-4 flex-1 flex flex-col">
            <h3 class="text-gray-800 font-medium mb-2 flex-1">
              <?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>
            </h3>
            <p class="text-red-600 font-bold mb-2">
              R$ <?= number_format($p->getPreco(), 2, ',', '.') ?>
            </p>
            <p class="mb-4">Estoque: <strong><?= $p->getQuantidade() ?></strong></p>
            <?php if ($p->getQuantidade() > 0): ?>
              <button
                class="addBtn mt-auto bg-red-600 hover:bg-red-700 text-white py-2 rounded"
                data-id="<?= $p->getId() ?>"
              >Adicionar</button>
            <?php else: ?>
              <span class="mt-auto text-gray-500">Indisponível</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

<script>
$(function(){
  const $sb = $('#cartSidebar'),
        $ov = $('#cartOverlay'),
        $tg = $('#toggleCart'),
        $cl = $('#closeCart');

  function openCart(){ $sb.removeClass('translate-x-full'); $ov.removeClass('hidden'); }
  function closeCart(){ $sb.addClass('translate-x-full'); $ov.addClass('hidden'); }

  closeCart();
  $tg.on('click', openCart);
  $cl.on('click', closeCart);
  $ov.on('click', closeCart);

  function loadCart(){
    $.getJSON('/ajax/getCarrinho.php', res => {
      $('#cartCount').text(res.count || 0);
      $('#cartTotal').text('R$ ' + (res.total||0).toFixed(2).replace('.',','));
      const $ct = $('#cartItems').empty();
      if (!res.items?.length) {
        return $ct.append('<p class="text-gray-600">Carrinho vazio.</p>');
      }
      res.items.forEach(it => {
        $ct.append(`
          <div class="mb-4 border-b pb-2">
            <div class="flex justify-between">
              <span>${it.nome}</span>
              <button class="removeItem text-red-600" data-id="${it.id}">✕</button>
            </div>
            <div class="mt-2 flex items-center space-x-2">
              <input type="number" min="1" max="${it.max}"
                     class="cartQty border px-2 py-1 w-16"
                     data-id="${it.id}" value="${it.qty}">
              <span>R$ ${it.line.toFixed(2).replace('.',',')}</span>
            </div>
          </div>
        `);
      });
    });
  }

  // adicionar / incrementar
  $(document).on('click','.addBtn', function(){
    const id = $(this).data('id'),
          $in = $(`.cartQty[data-id='${id}']`),
          cur = $in.length ? parseInt($in.val(),10) : 0,
          nxt = cur + 1;
    $.post('/ajax/adicionaCarrinho.php',{ id, qty: nxt }, res => {
      if (!res.success) alert(res.message||'Erro');
      loadCart(); openCart();
    }, 'json');
  });

  // alterar qty
  $(document).on('change','.cartQty', function(){
    const id = $(this).data('id'),
          qty = parseInt(this.value,10) || 1;
    $.post('/ajax/adicionaCarrinho.php',{ id, qty }, res => {
      if (!res.success) alert(res.message||'Erro');
      loadCart();
    }, 'json');
  });

  // remover item
  $(document).on('click','.removeItem', function(){
    $.post('/ajax/removeDoCarrinho.php',{ id: $(this).data('id') }, loadCart, 'json');
  });

  // finalizar pedido
  $('#checkout').on('click', ()=> location.href = '/index.php/finalizarPedido');

  // carga inicial
  loadCart();
});
</script>

</body>
</html>
