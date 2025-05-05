<?php
// pages/estoque.php
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
$dao = $factory->getProdutoDao();

// Busca via GET q
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $produtos = $dao->buscaPorNome($q);
} else {
    $produtos = $dao->buscaTodos();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin – Estoque</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="p-6 bg-gray-100">
    <h1 class="text-2xl font-bold mb-4">Estoque</h1>

    <!-- Barra de pesquisa -->
    <form method="GET" action="/index.php/estoque" class="mb-4 flex">
        <input
            name="q" type="text"
            placeholder="Pesquisar estoque..."
            value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
            class="flex-1 border border-gray-300 px-4 py-2 rounded-l focus:outline-none focus:ring-2 focus:ring-red-500"
        />
        <button
            type="submit"
            class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700"
        >Buscar</button>
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
                <td class="px-4 py-2"><?= htmlspecialchars($p->getNome(), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="px-4 py-2"><?= $p->getQuantidade() ?></td>
                <td class="px-4 py-2 space-x-2">
                    <a href="/index.php/estoque/editar?id=<?= $p->getId() ?>"
                       class="bg-indigo-500 hover:bg-indigo-600 text-white px-2 py-1 rounded"
                    >Editar Quantidade</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
