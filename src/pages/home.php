<?php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: validaLogin.php");
    exit;
}

$usuario_nome = htmlspecialchars($_SESSION['usuario_nome']);
$usuario_tipo = $_SESSION['usuario_tipo'] ?? 'cliente';
$pageTitle = "Dashboard";
?>
<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-200 flex">
    <div class="w-full flex flex-col">
        <!-- Header estilizado com Tailwind -->
        <header class="bg-green-600 text-white p-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold">Loja Virtual - Dashboard</h1>
            <div class="text-sm">
                Bem-vindo, <?php echo $usuario_nome; ?> |
                <a href="../logout.php" class="underline">Logout</a>
            </div>
        </header>
        <div class="flex flex-1">
            <!-- Sidebar -->
            <nav class="w-56 bg-white border-r border-gray-300 p-4 h-screen overflow-auto">
                <?php if ($usuario_tipo !== 'cliente'): ?>
                    <div class="menu-section mb-6">
                        <h3 class="text-lg font-medium text-green-600 mb-2">Cadastros</h3>
                        <ul class="space-y-1">
                            <li><a href="usuario.php" class="text-gray-700 hover:text-green-600">Usuários</a></li>
                            <li><a href="fornecedor.php" class="text-gray-700 hover:text-green-600">Fornecedores</a></li>
                            <li><a href="produto.php" class="text-gray-700 hover:text-green-600">Produtos</a></li>
                            <li><a href="estoque.php" class="text-gray-700 hover:text-green-600">Estoque</a></li>
                        </ul>
                    </div>
                    <div class="menu-section mb-6">
                        <h3 class="text-lg font-medium text-green-600 mb-2">Pedidos</h3>
                        <ul class="space-y-1">
                            <li><a href="consultar_pedidos.php" class="text-gray-700 hover:text-green-600">Consultar Pedidos</a></li>
                        </ul>
                    </div>
                    <div class="menu-section mb-6">
                        <h3 class="text-lg font-medium text-green-600 mb-2">Relatórios</h3>
                        <ul class="space-y-1">
                            <li><a href="relatorio_pedidos.php" class="text-gray-700 hover:text-green-600">Relatório de Pedidos</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                <!-- Seção Loja disponível para todos os usuários -->
                <div class="menu-section">
                    <h3 class="text-lg font-medium text-green-600 mb-2">Loja</h3>
                    <ul class="space-y-1">
                        <li><a href="produto.php" class="text-gray-700 hover:text-green-600">Ver Produtos</a></li>
                        <li><a href="carrinho.php" class="text-gray-700 hover:text-green-600">Carrinho de Compras</a></li>
                    </ul>
                </div>
            </nav>
            <!-- Main content -->
            <main class="flex-1 p-6 overflow-auto">
                <h2 class="text-2xl font-semibold text-green-600 mb-4">Visão Geral</h2>
                <p class="text-gray-700">Use o menu lateral para navegar pelas funcionalidades do sistema.</p>
                <!-- Aqui você pode adicionar gráficos, estatísticas ou widgets -->
            </main>
        </div>
    </div>
</body>
</html>
