<?php
// pages/home.php
// Sessão já iniciada em index.php (não precisa de session_start aqui)

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /index.php/login");
    exit;
}

$usuario_nome = htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8');

// Carrega a fachada e busca os produtos
require_once __DIR__ . '/../fachada.php';
$dao = $factory->getProdutoDao();

// Lógica de busca via GET
$q = trim($_GET['q'] ?? '');
$produtos = $q !== ''
    ? $dao->buscaPorNome($q)
    : $dao->buscaTodos();
?>
<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos do Casa – Produtos</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow p-4 flex items-center justify-between">
        <div class="flex items-center flex-1 space-x-4">
            <img src="/assets/Amigos_do_Casa_logo.png"
                 alt="Logo Amigos do Casa"
                 class="h-12 w-auto" />

            <form method="GET" action="/index.php/home" class="flex flex-1">
                <input
                    name="q"
                    type="text"
                    placeholder="Buscar produtos..."
                    value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                    class="flex-1 border border-gray-300 rounded-l-full px-4 py-2
                           focus:outline-none focus:ring-2 focus:ring-red-500"
                />
                <button
                    type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white rounded-r-full px-4"
                >🔍</button>
            </form>
        </div>

        <div class="flex items-center space-x-6">
            <!-- Carrinho -->
            <a href="/index.php/carrinho" class="relative text-gray-700 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h13L17 13
                             M7 13H5.4M17 13l1.5 7
                             M6 21a1 1 0 100-2 1 1 0 000 2
                             zm12 0a1 1 0 100-2 1 1 0 000 2z" />
                </svg>
                <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
                    <?= count($_SESSION['carrinho'] ?? []) ?>
                </span>
            </a>

            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
  <div class="relative inline-block text-left w-max group">
    <button type="button"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700
               hover:bg-gray-100 focus:outline-none rounded cursor-pointer"
    >
      Olá, <?= $usuario_nome ?>
      <span class="ml-1">▾</span>
    </button>

    <ul
      class="
        absolute right-0 top-full
        mt-1 w-48
        bg-white border border-gray-200 rounded-md shadow-lg z-50
        opacity-0 pointer-events-none
        group-hover:opacity-100 group-hover:pointer-events-auto
        transition-opacity
      "
    >
      <li><a href="/index.php/usuario"    class="block px-4 py-2 hover:bg-gray-100">Usuários</a></li>
      <li><a href="/index.php/fornecedor" class="block px-4 py-2 hover:bg-gray-100">Fornecedores</a></li>
      <li><a href="/index.php/produto"    class="block px-4 py-2 hover:bg-gray-100">Produtos</a></li>
      <li><a href="/index.php/estoque"    class="block px-4 py-2 hover:bg-gray-100">Estoque</a></li>
      <li><hr class="my-1 border-gray-200" /></li>
      <li><a href="/index.php/logout"     class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a></li>
    </ul>
  </div>
            <?php else: ?>
                <!-- Cliente normal -->
                <span class="text-sm">
                    Olá, <?= $usuario_nome ?> |
                    <a href="/index.php/logout"
                       class="text-red-600 hover:underline">Logout</a>
                </span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Grid de produtos -->
    <main class="flex-1 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($produtos as $p): ?>
                <div class="bg-white rounded shadow overflow-hidden flex flex-col">
                    <img
                        src="<?= htmlspecialchars($p->getImagemUrl() ?: '/assets/placeholder.png', ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($p->getNome(), ENT_QUOTES, 'UTF-8') ?>"
                        class="h-48 w-full object-cover"
                    />
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-gray-800 font-medium mb-2 flex-1">
                            <?= htmlspecialchars($p->getNome(), ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="text-red-600 font-bold mb-4">
                            R$ <?= number_format($p->getPreco(), 2, ',', '.') ?>
                        </p>
                        <a href="/index.php/adicionaCarrinho?produto_id=<?= urlencode($p->getId()) ?>"
                           class="mt-auto bg-red-600 hover:bg-red-700 text-white text-center py-2 rounded"
                        >
                            Adicionar ao Carrinho
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Script para delay de 300ms no fechamento do dropdown -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const menu      = document.querySelector('.group');
        const dropdown  = menu.querySelector('ul');
        let hideTimer;

        // Inicializa inline styles para neutralizar o group-hover
        dropdown.style.transition    = 'opacity 200ms ease';
        dropdown.style.opacity       = '0';
        dropdown.style.pointerEvents = 'none';

        // Show imediato ao entrar no container (botão ou lista)
        menu.addEventListener('mouseenter', () => {
          clearTimeout(hideTimer);
          dropdown.style.opacity       = '1';
          dropdown.style.pointerEvents = 'auto';
        });

        // Delay de 300ms para esconder após sair do container
        menu.addEventListener('mouseleave', () => {
          clearTimeout(hideTimer);
          hideTimer = setTimeout(() => {
            dropdown.style.opacity       = '0';
            dropdown.style.pointerEvents = 'none';
          }, 300);
        });
      });
    </script>

</body>
</html>
