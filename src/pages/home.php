<?php
// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simula nome do usuário (caso não esteja logado)
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

// Simula tipo de usuário
$_SESSION['usuario_tipo'] = $_SESSION['usuario_tipo'] ?? 'cliente'; // ou 'admin'

// Simula carrinho
$_SESSION['carrinho'] = $_SESSION['carrinho'] ?? [];

// Pega busca (se tiver)
$q = $_GET['q'] ?? '';

// Simula produtos (você pode trocar por dados reais do banco)
$produtos = [
    (object)[
        'id' => 1,
        'nome' => 'Cadeira de Madeira',
        'preco' => 199.99,
        'imagem' => '/assets/cadeira.jpg'
    ],
    (object)[
        'id' => 2,
        'nome' => 'Mesa de Jantar',
        'preco' => 499.90,
        'imagem' => '/assets/mesa.jpg'
    ],
    (object)[
        'id' => 3,
        'nome' => 'Sofá Retrátil',
        'preco' => 899.90,
        'imagem' => ''
    ],
];

// Filtro por busca
if ($q) {
    $produtos = array_filter($produtos, function ($p) use ($q) {
        return stripos($p->nome, $q) !== false;
    });
}
?>

<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos do Casa – Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow p-4 flex items-center justify-between">
    <div class="flex items-center flex-1 space-x-4">
        <img src="/assets/Amigos_do_Casa_logo.png" alt="Logo Amigos do Casa" class="h-12 w-auto" />

        <form method="GET" action="home.php" class="flex flex-1">
            <input name="q" type="text" placeholder="Buscar produtos..."
                   value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                   class="flex-1 border border-gray-300 rounded-l-full px-4 py-2
                          focus:outline-none focus:ring-2 focus:ring-red-500" />
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-r-full px-4">🔍</button>
        </form>
    </div>

    <div class="flex items-center space-x-6">
        <a href="carrinho.php" class="relative text-gray-700 hover:text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h13L17 13
                         M6 21a1 1 0 100-2 1 1 0 000 2M18 21a1 1 0 100-2 1 1 0 000 2" />
            </svg>
            <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
                <?= count($_SESSION['carrinho']) ?>
            </span>
        </a>

        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
            <div class="relative inline-block text-left" id="dropdown-wrapper">
                <button id="dropdown-btn" type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700
                               hover:bg-gray-100 focus:outline-none rounded cursor-pointer">
                    Olá, <?= htmlspecialchars($usuario_nome, ENT_QUOTES, 'UTF-8') ?> <span class="ml-1">▾</span>
                </button>

                <ul id="dropdown-menu"
                    class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                    <li><a href="/index.php/usuario"    class="block px-4 py-2 hover:bg-gray-100">Usuários</a></li>
                    <li><a href="/index.php/fornecedor" class="block px-4 py-2 hover:bg-gray-100">Fornecedores</a></li>
                    <li><a href="/index.php/produto"    class="block px-4 py-2 hover:bg-gray-100">Produtos</a></li>
                    <li><a href="/index.php/estoque"    class="block px-4 py-2 hover:bg-gray-100">Estoque</a></li>
                    <li><hr class="my-1 border-gray-200" /></li>
                    <li><a href="/../logout.php"     class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <span class="text-sm">
                Olá, <?= htmlspecialchars($usuario_nome, ENT_QUOTES, 'UTF-8') ?> |
                <a href="/../logout.php" class="text-red-600 hover:underline">Logout</a>
            </span>
        <?php endif; ?>
    </div>
</header>

<main class="flex-1 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($produtos as $p): ?>
        <div class="bg-white rounded shadow overflow-hidden flex flex-col">
            <img src="<?= htmlspecialchars($p->imagem ?: '/assets/placeholder.png', ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($p->nome, ENT_QUOTES, 'UTF-8') ?>"
                 class="h-48 w-full object-cover" />
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="text-gray-800 font-medium mb-2 flex-1">
                    <?= htmlspecialchars($p->nome, ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="text-red-600 font-bold mb-4">
                    R$ <?= number_format($p->preco, 2, ',', '.') ?>
                </p>
                <a href="adicionaCarrinho.php?produto_id=<?= urlencode($p->id) ?>"
                   class="mt-auto bg-red-600 hover:bg-red-700 text-white text-center py-2 rounded">
                    Adicionar ao Carrinho
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Dropdown Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('dropdown-btn');
        const menu = document.getElementById('dropdown-menu');

        btn?.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!btn?.contains(e.target) && !menu?.contains(e.target)) {
                menu?.classList.add('hidden');
            }
        });
    });
</script>

</body>
</html>
