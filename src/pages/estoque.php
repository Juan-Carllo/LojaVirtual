<?php
// pages/estoque.php

// Se não tiver seção, inicia nova
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
require_once __DIR__ . '/header.php';

$dao = $factory->getProdutoDao();

// Atualização via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['quantidade'])) {
    $id         = (int)$_POST['id'];
    $quantidade = (int)$_POST['quantidade'];

    $produto = $dao->buscaPorId($id);
    if ($produto) {
        $produto->setQuantidade($quantidade);
        $dao->altera($produto);
    }

    header('Location: /index.php/estoque?q=' . urlencode($_GET['q'] ?? '') . '&page=' . ($_GET['page'] ?? 1));
    exit;
}

// GET – busca, paginação e listagem
$q       = trim($_GET['q'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($q !== '') {
    // filtra todos e faz slice
    $all       = $dao->buscaPorNome($q);
    $total     = count($all);
    $produtos  = array_slice($all, $offset, $perPage);
} else {
    // busca paginada diretamente no DAO
    $produtos  = $dao->buscaPagina($perPage, $offset);
    $total     = $dao->contaTodos();
}

$totalPages = (int)ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin – Estoque</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Estoque</h1>

    <!-- Barra de pesquisa -->
    <form method="GET" action="/index.php/estoque" class="mb-4 flex">
        <input
            name="q"
            type="text"
            placeholder="Pesquisar estoque..."
            value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
            class="flex-1 border border-gray-300 px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500"
        >
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">
            Buscar
        </button>
    </form>

    <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead>
        <tr class="bg-gray-200 text-left">
            <th class="px-4 py-2">Produto</th>
            <th class="px-4 py-2">Quantidade Atual</th>
            <th class="px-4 py-2">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= $p->getQuantidade() ?></td>
                <td class="px-4 py-2 space-x-2">
                    <button
                        class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600"
                        data-id="<?= $p->getId() ?>"
                        data-nome="<?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>"
                        data-quantidade="<?= $p->getQuantidade() ?>"
                    >
                        Editar
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-4 flex justify-center space-x-2">
            <?php if ($page > 1): ?>
                <a
                    href="?q=<?= urlencode($q) ?>&page=<?= $page - 1 ?>"
                    class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300"
                >‹ Anterior</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a
                    href="?q=<?= urlencode($q) ?>&page=<?= $p ?>"
                    class="px-3 py-1 rounded <?= $p === $page ? 'bg-red-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>"
                ><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a
                    href="?q=<?= urlencode($q) ?>&page=<?= $page + 1 ?>"
                    class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300"
                >Próxima ›</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<!-- Modal -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center hidden">
    <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-sm">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-semibold">Editar Estoque</h2>
            <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form method="post" id="editForm" action="/index.php/estoque">
            <input type="hidden" name="id" id="produtoId">
            <label class="block mb-1 text-sm">Produto</label>
            <input id="produtoNome" disabled class="w-full border px-3 py-2 rounded mb-2 bg-gray-100">
            <label class="block mb-1 text-sm">Nova Quantidade</label>
            <input
                type="number"
                name="quantidade"
                id="produtoQtd"
                required
                min="0"
                class="w-full border px-3 py-2 rounded mb-4 focus:ring-2 focus:ring-red-500"
            >
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// javascript do modal de edição
document.addEventListener('DOMContentLoaded', () => {
    const modal      = document.getElementById('editModal');
    const overlay    = document.getElementById('modalOverlay');
    const closeBtn   = document.getElementById('closeModal');
    const cancelBtn  = document.getElementById('cancelBtn');
    const editBtns   = document.querySelectorAll('.editarBtn');
    const inputId    = document.getElementById('produtoId');
    const inputNome  = document.getElementById('produtoNome');
    const inputQtd   = document.getElementById('produtoQtd');

    function openModal(data) {
        inputId.value   = data.id;
        inputNome.value = data.nome;
        inputQtd.value  = data.quantidade;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                id:         btn.dataset.id,
                nome:       btn.dataset.nome,
                quantidade: btn.dataset.quantidade
            });
        });
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);
});
</script>
</body>
</html>
