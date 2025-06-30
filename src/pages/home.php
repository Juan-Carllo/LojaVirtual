<?php

// Se não tiver seção, inicia uma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../fachada.php';
$produtoDao = $factory->getProdutoDao();

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$_SESSION['usuario_tipo'] = $_SESSION['usuario_tipo'] ?? 'cliente';
$_SESSION['carrinho'] = $_SESSION['carrinho'] ?? [];

$q = $_GET['q'] ?? '';

try {
    $produtos = $q ? $produtoDao->buscaPorNome($q) : $produtoDao->buscaTodos();
} catch (Exception $e) {
    echo "<p class='text-red-600'>Erro ao buscar produtos: " . $e->getMessage() . "</p>";
    $produtos = [];
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

<?php require_once __DIR__ . '/header.php'; ?>

<main class="flex-1 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($produtos as $p): ?>
        <div class="bg-white rounded shadow overflow-hidden flex flex-col">
            <?php if ($p->getImagem()): ?>
                <img
                    src="data:image/jpeg;base64,<?= base64_encode($p->getImagem()) ?>"
                    alt="<?= htmlspecialchars($p->getNome(), ENT_QUOTES, 'UTF-8') ?>"
                    class="h-48 w-full object-cover"
                />
            <?php else: ?>
                <img
                    src="/assets/placeholder.png"
                    alt="Sem imagem"
                    class="h-48 w-full object-cover"
                />
            <?php endif; ?>
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="text-gray-800 font-medium mb-2 flex-1">
                    <?= htmlspecialchars($p->getNome(), ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="text-red-600 font-bold mb-4">
                    R$ <?= number_format($p->getPreco(), 2, ',', '.') ?>
                </p>
                <a href="adicionaCarrinho.php?produto_id=<?= urlencode($p->getId()) ?>"
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
