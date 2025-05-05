<?php
// pages/home.php
// Sessão já iniciada no index.php

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /index.php/login");
    exit;
}

$usuario_nome = htmlspecialchars($_SESSION['usuario_nome']);

// Carrega a fachada e busca os produtos
require_once __DIR__ . '/../fachada.php';
$dao = $factory->getProdutoDao();

// Lógica de busca via GET q
if (!empty($_GET['q'])) {
    $busca = trim($_GET['q']);
    $produtos = $dao->buscaPorNome($busca);
} else {
    $produtos = $dao->buscaTodos();
}
?>
<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos do Casa - Produtos</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Header com logo no canto esquerdo e busca -->
    <header class="bg-white shadow p-4 flex items-center justify-between">
        <div class="flex items-center flex-1 space-x-4">
            <!-- Logo Amigos do Casa -->
            <img src="/assets/Amigos_do_Casa_logo.png"
                 alt="Logo Amigos do Casa"
                 class="h-12 w-auto" />

            <!-- Formulário de busca -->
            <form method="GET" action="/index.php/home" class="flex flex-1">
                <input
                    name="q"
                    type="text"
                    placeholder="Buscar produtos..."
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    class="flex-1 border border-gray-300 rounded-l-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                />
                <button
                    type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white rounded-r-full px-4"
                >🔍</button>
            </form>
        </div>
        <!-- Carrinho e usuário -->
        <div class="flex items-center space-x-6">
            <a href="/index.php/carrinho" class="relative text-gray-700 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h13L17 13M7 13H5.4M17 13l1.5 7M6 21a1 1 0 100-2 1 1 0 000 2zm12 0a1 1 0 100-2 1 1 0 000 2z" />
                </svg>
                <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1"><?= count($_SESSION['carrinho'] ?? []) ?></span>
            </a>
            <span class="text-sm">
                Olá, <?= $usuario_nome ?> |
                <a href="/index.php/logout" class="text-red-600 hover:underline">Logout</a>
            </span>
        </div>
    </header>

    <!-- Grid de produtos -->
    <main class="flex-1 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($produtos as $p): ?>
                <div class="bg-white rounded shadow overflow-hidden flex flex-col">
                    <img
                        src="<?= htmlspecialchars($p->getImagemUrl() ?: '/assets/placeholder.png') ?>"
                        alt="<?= htmlspecialchars($p->getNome()) ?>"
                        class="h-48 w-full object-cover"
                    />
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-gray-800 font-medium mb-2 flex-1"><?= htmlspecialchars($p->getNome()) ?></h3>
                        <p class="text-red-600 font-bold mb-4">R$ <?= number_format($p->getPreco(), 2, ',', '.') ?></p>
                        <a href="/index.php/adicionaCarrinho?produto_id=<?= urlencode($p->getId()) ?>"
                           class="mt-auto bg-red-600 hover:bg-red-700 text-white text-center py-2 rounded">
                            Adicionar ao Carrinho
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>