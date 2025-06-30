<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario_nome   = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_tipo   = $_SESSION['usuario_tipo'] ?? 'cliente';
$carrinho_count = count($_SESSION['carrinho'] ?? []);
$q              = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES);
?>
<header class="bg-white shadow p-4 flex items-center justify-between sticky top-0 z-50">
  <!-- Logo + nome clicáveis -->
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
        <a href="/index.php/produto"     class="text-gray-700 hover:text-red-600">Produtos</a>
      <?php endif; ?>
      <!-- Link de Pedidos para todos -->
      <a href="/index.php/pedidos"       class="text-gray-700 hover:text-red-600">Pedidos</a>
    </nav>

    <form method="GET" action="/index.php/home" class="relative w-full max-w-md">
      <input
        name="q"
        value="<?= $q ?>"
        type="text"
        placeholder="Pesquisar produtos…"
        class="w-full pl-4 pr-12 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-red-600"
      />
      <button
        type="submit"
        class="absolute right-1 top-1/2 transform -translate-y-1/2 bg-red-600 hover:bg-red-700 p-2 rounded-full"
      >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5 text-white"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111.5 4.5a7.5 7.5 0 015.15 12.15z"/>
        </svg>
      </button>
    </form>
  </div>

  <!-- Carrinho + Logout -->
  <div class="flex items-center space-x-6">
    <button id="toggleCart" class="relative text-gray-700 hover:text-red-600">
      <svg xmlns="http://www.w3.org/2000/svg"
           class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m5-9v9m4-9v9m5-9l2 9"/>
      </svg>
      <span id="cartCount"
            class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
        <?= $carrinho_count ?>
      </span>
    </button>

    <span class="text-gray-700"><?= htmlspecialchars($usuario_nome) ?></span>
    <a href="/index.php/logout" class="text-red-600 hover:underline">Logout</a>
  </div>
</header>
