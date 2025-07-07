<?php
// pages/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_nome   = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_tipo   = $_SESSION['usuario_tipo'] ?? 'cliente';
$carrinho_count = count($_SESSION['carrinho'] ?? []);
$q              = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

<header class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
  <!-- Logo + nome -->
  <a href="/index.php/home" class="flex items-center space-x-4">
    <img src="/assets/Amigos_do_Casa_logo.png" alt="Logo" class="h-10 w-auto" />
    <span class="text-2xl font-bold text-gray-800">Amigos do Casa</span>
  </a>

  <!-- Navegação + Busca -->
  <div class="flex-1 mx-8 flex items-center space-x-8">
    <nav class="space-x-6">
      <?php if ($usuario_tipo === 'admin'): ?>
        <a href="/index.php/produto"     class="text-gray-700 hover:text-red-600">Produtos</a>
        <a href="/index.php/fornecedor"   class="text-gray-700 hover:text-red-600">Fornecedores</a>
        <a href="/index.php/usuario"      class="text-gray-700 hover:text-red-600">Usuários</a>
        <a href="/index.php/estoque"      class="text-gray-700 hover:text-red-600">Estoque</a>
      <?php else: ?>
        <a href="/index.php/produto"      class="text-gray-700 hover:text-red-600">Produtos</a>
      <?php endif; ?>
      <a href="/index.php/pedidos"       class="text-gray-700 hover:text-red-600">Pedidos</a>
    </nav>

    <form method="GET" action="/index.php/home" class="relative w-full max-w-md">
      <input
        name="q" value="<?= $q ?>"
        type="text" placeholder="Pesquisar produtos…"
        class="w-full pl-4 pr-12 py-2 border border-gray-300 rounded-full
               focus:outline-none focus:ring-2 focus:ring-red-600"
      />
      <button type="submit"
              class="absolute right-1 top-1/2 transform -translate-y-1/2
                     bg-red-600 hover:bg-red-700 p-2 rounded-full">
        🔍
      </button>
    </form>
  </div>

  <!-- Carrinho + Logout -->
  <div class="flex items-center space-x-6">
    <button id="toggleCart" class="relative text-gray-700 hover:text-red-600">
      🛒
      <span id="cartCount"
            class="absolute -top-1 -right-2 bg-red-600 text-white text-xs
                   rounded-full px-1"><?= $carrinho_count ?></span>
    </button>

    <span class="text-gray-700"><?= htmlspecialchars($usuario_nome) ?></span>
    <a href="/index.php/logout" class="text-red-600 hover:underline">Logout</a>
  </div>
</header>

<!-- Overlay e sidebar do carrinho (incluído em todo lugar) -->
<div id="cartOverlay" class="fixed inset-0 bg-black opacity-50 hidden z-40"></div>
<div id="cartSidebar"
     class="fixed top-0 right-0 h-full w-80 bg-white shadow-lg
            transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
  <div class="p-4 flex justify-between items-center border-b">
    <h2 class="text-xl font-semibold">Meu Carrinho</h2>
    <button id="closeCart" class="text-gray-600 hover:text-gray-800">✕</button>
  </div>
  <div id="cartItems" class="p-4 overflow-y-auto flex-1">
    <!-- itens carregados via AJAX -->
  </div>
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

<script>
$(function(){
  const $sb = $('#cartSidebar'),
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

  // bind para abrir/fechar
  $tg.on('click', openCart);
  $cl.on('click', closeCart);
  $ov.on('click', closeCart);

  // busca dados do carrinho
  function loadCart(){
    $.getJSON('/ajax/getCarrinho.php', res => {
      $('#cartCount').text(res.count||0);
      $('#cartTotal').text('R$ '+(res.total||0).toFixed(2).replace('.',','));
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
          </div>`);
      });
    });
  }

  // ações sobre os itens do carrinho
  $(document).on('click','.removeItem', function(){
    $.post('/ajax/removeDoCarrinho.php',
      { id: $(this).data('id') },
      loadCart,'json');
  });
  $(document).on('change','.cartQty', function(){
    const id = $(this).data('id'),
          qty = parseInt(this.value,10)||1;
    $.post('/ajax/adicionaCarrinho.php',
      { id, qty }, res => {
        if (!res.success) alert(res.message||'Erro');
        loadCart();
      }, 'json');
  });
  $('#checkout').on('click', ()=> window.location.href = '/index.php/finalizarPedido');

  // carrega ao iniciar
  loadCart();
});
</script>
