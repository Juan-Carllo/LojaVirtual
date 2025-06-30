<?php
// pages/home.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';
$produtoDao = $factory->getProdutoDao();

$q = trim($_GET['q'] ?? '');
try {
    $produtos = $q
      ? $produtoDao->buscaPorNome($q)
      : $produtoDao->buscaTodos();
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

  <!-- carrinho: overlay & sidebar -->
  <div id="cartOverlay" class="fixed inset-0 bg-black opacity-50 hidden z-40"></div>
  <div id="cartSidebar"
       class="fixed top-0 right-0 h-full w-80 bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <div class="p-4 flex justify-between items-center border-b">
      <h2 class="text-xl font-semibold">Meu Carrinho</h2>
      <button id="closeCart" class="text-gray-600 hover:text-gray-800">✕</button>
    </div>
    <div id="cartItems" class="p-4 overflow-y-auto flex-1"></div>
    <div class="p-4 border-t">
      <div class="flex justify-between mb-4">
        <span class="font-bold">Total</span>
        <span id="cartTotal">R$ 0,00</span>
      </div>
      <button id="checkout"
              class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
        Finalizar Pedido
      </button>
    </div>
  </div>

  <main class="flex-1 p-6 pt-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($produtos as $p): ?>
        <div class="bg-white rounded shadow overflow-hidden flex flex-col">
          <?php if ($p->getImagem()): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($p->getImagem()) ?>"
                 alt="<?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>"
                 class="h-48 w-full object-cover"/>
          <?php else: ?>
            <img src="/assets/placeholder.png" alt="Sem imagem"
                 class="h-48 w-full object-cover"/>
          <?php endif; ?>
          <div class="p-4 flex-1 flex flex-col">
            <h3 class="text-gray-800 font-medium mb-2 flex-1">
              <?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>
            </h3>
            <p class="text-red-600 font-bold mb-2">
              R$ <?= number_format($p->getPreco(),2,',','.') ?>
            </p>
            <p class="mb-4">Estoque: <strong><?= $p->getQuantidade() ?></strong></p>
            <?php if ($p->getQuantidade() > 0): ?>
              <button class="addBtn mt-auto bg-red-600 hover:bg-red-700 text-white py-2 rounded"
                      data-id="<?= $p->getId() ?>">
                Adicionar
              </button>
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
  var $sb = $('#cartSidebar'),
      $ov = $('#cartOverlay'),
      $tg = $('#toggleCart'),
      $cl = $('#closeCart');

  function openCart(){
    $sb.removeClass('translate-x-full');
    $ov.removeClass('hidden');
  }
  function closeCart(){
    $sb.addClass('translate-x-full');
    $ov.addClass('hidden');
  }

  closeCart();
  $tg.on('click', openCart);
  $cl.on('click', closeCart);
  $ov.on('click', closeCart);

  function loadCart(){
    $.getJSON('/ajax/getCarrinho.php', function(res){
      $('#cartCount').text(res.count||0);
      $('#cartTotal').text('R$ '+(res.total||0).toFixed(2).replace('.',','));
      var $ct = $('#cartItems').empty();
      if (!res.items?.length) {
        return $ct.append('<p class="text-gray-600">Carrinho vazio.</p>');
      }
      res.items.forEach(function(it){
        $ct.append(
          `<div class="mb-4 border-b pb-2">
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
           </div>`
        );
      });
    });
  }

  // Adicionar — agora incrementando em 1 se já estiver no carrinho
  $(document).on('click','.addBtn', function(){
    var id = $(this).data('id');
    // tenta achar o input de qty para este id
    var $input = $(`.cartQty[data-id='${id}']`);
    var atual = $input.length ? parseInt($input.val(),10) : 0;
    var novaQty = atual + 1;
    $.post('/ajax/adicionaCarrinho.php',
      { id: id, qty: novaQty },
      function(res){
        if (!res.success) alert(res.message || 'Erro ao adicionar');
        loadCart();
        openCart();
      },
      'json'
    );
  });

  // Atualizar quantidade manual
  $(document).on('change','.cartQty', function(){
    var id = $(this).data('id'),
        qty = parseInt(this.value,10) || 1;
    $.post('/ajax/adicionaCarrinho.php',
      { id: id, qty: qty },
      function(res){
        if (!res.success) alert(res.message || 'Erro ao atualizar');
        loadCart();
      },
      'json'
    );
  });

  // Remover item
  $(document).on('click','.removeItem', function(){
    $.post('/ajax/removeDoCarrinho.php',
      { id: $(this).data('id') },
      function(){ loadCart() },
      'json'
    );
  });

  // Finalizar pedido
  $('#checkout').on('click', function(){
    location.href = '/index.php/finalizarPedido';
  });

  // Carga inicial
  loadCart();
});
</script>



</body>
</html>
