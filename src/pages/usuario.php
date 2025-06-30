<?php
// pages/usuario.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
$dao = $factory->getUsuarioDao();

// Exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $u = $dao->buscaPorId((int)$_POST['delete_id']);
    if ($u) {
        $dao->remove($u);
    }
    header('Location: /index.php/usuario');
    exit;
}

require_once __DIR__ . '/header.php';

// parâmetros de busca e paginação
$q       = trim($_GET['q'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($q !== '') {
    // busca completa e filtra pelo termo
    $all = $dao->buscaTodos();
    $filtered = array_filter($all, function($u) use ($q) {
        return stripos($u->getLogin(), $q) !== false
            || stripos($u->getNome(),  $q) !== false;
    });
    $total    = count($filtered);
    $usuarios = array_slice($filtered, $offset, $perPage);
} else {
    // busca paginada
    $usuarios = $dao->buscaPagina($perPage, $offset);
    $total    = $dao->contaTodos();
}

$totalPages = (int)ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin – Usuários</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<main class="flex-1 p-6">

    <h1 class="text-2xl font-bold mb-4">Usuários</h1>

    <div class="mb-4 flex items-center space-x-4">
        <form method="GET" action="/index.php/usuario" class="flex-1 flex">
            <input
                name="q"
                type="text"
                placeholder="Pesquisar usuários..."
                value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
                class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500"
            />
            <button
                type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700"
            >Buscar</button>
        </form>
        <button
            id="btnNovo"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
        >+ Novo Usuário</button>
    </div>

    <table class="min-w-full bg-white rounded shadow">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="px-4 py-2">Login</th>
                <th class="px-4 py-2">Nome</th>
                <th class="px-4 py-2">Tipo</th>
                <th class="px-4 py-2">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($u->getLogin(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($u->getNome(),  ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($u->getTipo(),  ENT_QUOTES) ?></td>
                <td class="px-4 py-2 space-x-2">
                    <button
                        class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600"
                        data-id="<?= $u->getId() ?>"
                        data-login="<?= htmlspecialchars($u->getLogin(),  ENT_QUOTES) ?>"
                        data-nome  ="<?= htmlspecialchars($u->getNome(),   ENT_QUOTES) ?>"
                        data-tipo  ="<?= $u->getTipo() ?>"
                        data-rua   ="<?= htmlspecialchars($u->getEndereco()?->getRua()        ?? '', ENT_QUOTES) ?>"
                        data-numero="<?= htmlspecialchars($u->getEndereco()?->getNumero()     ?? '', ENT_QUOTES) ?>"
                        data-complemento="<?= htmlspecialchars($u->getEndereco()?->getComplemento() ?? '', ENT_QUOTES) ?>"
                        data-bairro="<?= htmlspecialchars($u->getEndereco()?->getBairro()     ?? '', ENT_QUOTES) ?>"
                        data-cep   ="<?= htmlspecialchars($u->getEndereco()?->getCep()         ?? '', ENT_QUOTES) ?>"
                        data-cidade="<?= htmlspecialchars($u->getEndereco()?->getCidade()     ?? '', ENT_QUOTES) ?>"
                        data-estado="<?= htmlspecialchars($u->getEndereco()?->getEstado()     ?? '', ENT_QUOTES) ?>"
                    >Editar</button>

                    <form
                        method="post"
                        action="/index.php/usuario"
                        class="inline"
                        onsubmit="return confirm('Confirma a exclusão?');"
                    >
                        <input type="hidden" name="delete_id" value="<?= $u->getId() ?>">
                        <button
                            type="submit"
                            class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                        >Excluir</button>
                    </form>
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

    <!-- Modal de cadastro/edição -->
    <div id="userModal" class="fixed inset-0 flex items-center justify-center hidden">
        <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-md">
            <div class="flex justify-between mb-4">
                <h2 id="modalTitle" class="text-xl font-semibold">Novo Usuário</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form id="userForm" method="post" action="/index.php/salvaUsuario" class="space-y-3">
                <input type="hidden" name="id" id="userId">

                <div>
                    <label class="block text-sm">Login</label>
                    <input name="login" id="login" required
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm">Nome</label>
                    <input name="nome" id="nome" required
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm">Senha (opcional)</label>
                    <input type="password" name="senha" id="senha" placeholder="Deixe vazio para manter atual"
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm">Tipo</label>
                    <select name="tipo" id="tipo" class="w-full border px-3 py-2 rounded">
                        <option value="cliente">Cliente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <?php foreach (['rua','numero','complemento','bairro','cep','cidade','estado'] as $f): ?>
                <div>
                    <label class="block text-sm"><?= ucfirst($f) ?></label>
                    <input name="<?= $f ?>" id="<?= $f ?>" required
                        class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
                </div>
                <?php endforeach; ?>

                <div class="flex justify-end space-x-2 mt-4">
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

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal      = document.getElementById('userModal');
    const overlay    = document.getElementById('modalOverlay');
    const btnNovo    = document.getElementById('btnNovo');
    const closeBtn   = document.getElementById('closeModal');
    const cancelBtn  = document.getElementById('cancelBtn');
    const editBtns   = document.querySelectorAll('.editarBtn');
    const title      = document.getElementById('modalTitle');
    const form       = document.getElementById('userForm');
    const fields     = ['userId','login','nome','senha','tipo','rua','numero','complemento','bairro','cep','cidade','estado'];

    function openModal(edit, data) {
        title.textContent = edit ? 'Editar Usuário' : 'Novo Usuário';
        form.reset();
        fields.forEach(f => {
            const el = document.getElementById(f);
            if (!el) return;
            el.value = data[f] ?? '';
            if (f === 'senha') el.value = '';
        });
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    btnNovo.addEventListener('click', () => openModal(false, {}));
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const data = { userId: btn.dataset.id };
            fields.slice(1).forEach(f => {
                data[f] = btn.dataset[f.replace(/([A-Z])/g, '-$1').toLowerCase()] || '';
            });
            openModal(true, data);
        });
    });
});
</script>
</body>
</html>
