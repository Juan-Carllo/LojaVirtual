<?php
// se não tiver seção, inicia nova
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$_SESSION['usuario_tipo'] = $_SESSION['usuario_tipo'] ?? 'cliente';
?>

<header class="bg-white shadow p-4 flex items-center justify-between">
    <div class="flex items-center flex-1 space-x-4">
        <img src="/assets/Amigos_do_Casa_logo.png" alt="Logo" class="h-12 w-auto" />
        <form method="GET" action="/index.php/home" class="flex flex-1">
            <input name="q" type="text" placeholder="Buscar produtos..."
                value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>"
                class="flex-1 border border-gray-300 rounded-l-full px-4 py-2 focus:ring-2 focus:ring-red-500" />
            <button type="submit" class="bg-red-600 text-white px-4 rounded-r-full">🔍</button>
        </form>
    </div>

    <div class="flex items-center space-x-6">
        <a href="/index.php/carrinho" class="relative text-gray-700 hover:text-red-600">
            🛒
            <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1">
                <?= count($_SESSION['carrinho'] ?? []) ?>
            </span>
        </a>
        <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
        <div class="relative inline-block text-left" id="dropdown-wrapper">
            <button id="dropdown-btn" type="button"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded">
                Olá, <?= htmlspecialchars($usuario_nome) ?> <span class="ml-1">▾</span>
            </button>
            <ul id="dropdown-menu"
                class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                <li><a href="/index.php/home" class="block px-4 py-2 hover:bg-gray-100">← Voltar para Home</a></li>
                <li><hr class="my-1 border-gray-200" /></li>
                <li><a href="/index.php/usuario" class="block px-4 py-2 hover:bg-gray-100">Usuários</a></li>
                <li><a href="/index.php/fornecedor" class="block px-4 py-2 hover:bg-gray-100">Fornecedores</a></li>
                <li><a href="/index.php/produto" class="block px-4 py-2 hover:bg-gray-100">Produtos</a></li>
                <li><a href="/index.php/estoque" class="block px-4 py-2 hover:bg-gray-100">Estoque</a></li>
                <li><hr class="my-1 border-gray-200" /></li>
                <li><a href="/../logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a></li>
            </ul>
        </div>
        <?php else: ?>
        <span class="text-sm">
            Olá, <?= htmlspecialchars($usuario_nome) ?> |
            <a href="/../logout.php" class="text-red-600 hover:underline">Logout</a>
        </span>
        <?php endif; ?>
    </div>
</header>

<script>
// javascript do dropdown de ações
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('dropdown-btn');
    const menu = document.getElementById('dropdown-menu');
    btn?.addEventListener('click', e => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });
    document.addEventListener('click', e => {
        if (!btn?.contains(e.target) && !menu?.contains(e.target)) {
            menu?.classList.add('hidden');
        }
    });
});
</script>
