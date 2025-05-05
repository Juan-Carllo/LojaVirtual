<?php
// pages/usuario.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
require_once __DIR__ . '/header.php';

$dao = $factory->getUsuarioDao();

// Exclusão inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $usuarioRem = $dao->buscaPorId((int)$_POST['delete_id']);
    if ($usuarioRem) {
        $dao->remove($usuarioRem);
    }
    header('Location: /index.php/usuario');
    exit;
}

// Erros de validação
$error_login = $_GET['error_login'] ?? '';
$error_nome  = $_GET['error_nome']  ?? '';
$error_senha = $_GET['error_senha'] ?? '';

$modalData = [
    'id'          => $_GET['id'] ?? '',
    'login'       => $_GET['login'] ?? '',
    'nome'        => $_GET['nome'] ?? '',
    'tipo'        => $_GET['tipo'] ?? 'cliente',
    'rua'         => $_GET['rua'] ?? '',
    'numero'      => $_GET['numero'] ?? '',
    'complemento' => $_GET['complemento'] ?? '',
    'bairro'      => $_GET['bairro'] ?? '',
    'cep'         => $_GET['cep'] ?? '',
    'cidade'      => $_GET['cidade'] ?? '',
    'estado'      => $_GET['estado'] ?? ''
];

$q = trim($_GET['q'] ?? '');
$usuarios = $dao->buscaTodos();
if ($q !== '') {
    $usuarios = array_filter($usuarios, function($u) use ($q) {
        return stripos($u->getLogin(), $q) !== false || stripos($u->getNome(), $q) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin – Usuários</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php require_once __DIR__ . '/header.php'; ?>

<main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Usuários</h1>

    <!-- Busca e Novo -->
    <div class="mb-4 flex items-center space-x-4">
        <form method="GET" action="/index.php/usuario" class="flex-1 flex">
            <input name="q" type="text" placeholder="Pesquisar usuários..."
                   value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
                   class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500" />
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">
                Buscar
            </button>
        </form>
        <button id="btnNovo" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Novo Usuário
        </button>
    </div>

    <!-- Tabela -->
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
                <td class="px-4 py-2"><?= htmlspecialchars($u->getNome(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($u->getTipo(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2 space-x-2">
                    <button class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded"
                        data-id="<?= $u->getId() ?>"
                        data-login="<?= htmlspecialchars($u->getLogin(), ENT_QUOTES) ?>"
                        data-nome="<?= htmlspecialchars($u->getNome(), ENT_QUOTES) ?>"
                        data-tipo="<?= $u->getTipo() ?>"
                        data-rua="<?= htmlspecialchars($u->getEndereco()->getRua() ?? '', ENT_QUOTES) ?>"
                        data-numero="<?= htmlspecialchars($u->getEndereco()->getNumero() ?? '', ENT_QUOTES) ?>"
                        data-complemento="<?= htmlspecialchars($u->getEndereco()->getComplemento() ?? '', ENT_QUOTES) ?>"
                        data-bairro="<?= htmlspecialchars($u->getEndereco()->getBairro() ?? '', ENT_QUOTES) ?>"
                        data-cep="<?= htmlspecialchars($u->getEndereco()->getCep() ?? '', ENT_QUOTES) ?>"
                        data-cidade="<?= htmlspecialchars($u->getEndereco()->getCidade() ?? '', ENT_QUOTES) ?>"
                        data-estado="<?= htmlspecialchars($u->getEndereco()->getEstado() ?? '', ENT_QUOTES) ?>">
                        Editar
                    </button>
                    <form method="post" action="/index.php/usuario" class="inline"
                          onsubmit="return confirm('Confirma a exclusão deste usuário?');">
                        <input type="hidden" name="delete_id" value="<?= $u->getId() ?>">
                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div id="userModal" class="fixed inset-0 flex items-center justify-center hidden">
        <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-md">
            <div class="flex justify-between mb-4">
                <h2 id="modalTitle" class="text-xl font-semibold">Novo Usuário</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form id="userForm" method="post" action="/index.php/salvaUsuario">
                <input type="hidden" name="id" id="userId" value="<?= htmlspecialchars($modalData['id'], ENT_QUOTES) ?>">

                <label class="block mb-1 text-sm">Login</label>
                <input name="login" id="login" value="<?= htmlspecialchars($modalData['login'], ENT_QUOTES) ?>" required
                       class="w-full border px-3 py-2 rounded mb-1 focus:ring-2 focus:ring-red-500" />
                <?php if ($error_login): ?><p class="text-red-600 text-sm mb-2"><?= $error_login ?></p><?php endif; ?>

                <label class="block mb-1 text-sm">Nome</label>
                <input name="nome" id="nome" value="<?= htmlspecialchars($modalData['nome'], ENT_QUOTES) ?>" required
                       class="w-full border px-3 py-2 rounded mb-1 focus:ring-2 focus:ring-red-500" />
                <?php if ($error_nome): ?><p class="text-red-600 text-sm mb-2"><?= $error_nome ?></p><?php endif; ?>

                <label class="block mb-1 text-sm">Senha (opcional)</label>
                <input type="password" name="senha" id="senha"
                       placeholder="Deixe em branco para manter a senha atual"
                       class="w-full border px-3 py-2 rounded mb-1 focus:ring-2 focus:ring-red-500" />
                <?php if ($error_senha): ?><p class="text-red-600 text-sm mb-2"><?= $error_senha ?></p><?php endif; ?>

                <label class="block mb-1 text-sm">Tipo</label>
                <select name="tipo" id="tipo" class="w-full border px-3 py-2 rounded mb-4">
                    <option value="cliente" <?= $modalData['tipo']==='cliente'?'selected':'' ?>>Cliente</option>
                    <option value="admin" <?= $modalData['tipo']==='admin'?'selected':'' ?>>Administrador</option>
                </select>

                <?php foreach (['rua','numero','complemento','bairro','cep','cidade','estado'] as $field): ?>
                    <label class="block mb-1 text-sm"><?= ucfirst($field) ?></label>
                    <input name="<?= $field ?>" id="<?= $field ?>" required
                           value="<?= htmlspecialchars($modalData[$field], ENT_QUOTES) ?>"
                           class="w-full border px-3 py-2 rounded mb-2 focus:ring-2 focus:ring-red-500" />
                <?php endforeach; ?>

                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- JS Modal -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('userModal');
    const overlay = document.getElementById('modalOverlay');
    const btnNovo = document.getElementById('btnNovo');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const editBtns = document.querySelectorAll('.editarBtn');
    const title = document.getElementById('modalTitle');
    const fields = ['userId','login','nome','senha','tipo','rua','numero','complemento','bairro','cep','cidade','estado'];

    function open(edit, data) {
        title.textContent = edit ? 'Editar Usuário' : 'Novo Usuário';
        fields.forEach(f => {
            const el = document.getElementById(f);
            if (!el) return;
            el.value = data[f] || '';
            if (f === 'senha') el.value = '';
        });
        modal.classList.remove('hidden');
    }

    function closeModalFn() {
        modal.classList.add('hidden');
    }

    btnNovo.addEventListener('click', () => open(false, {}));
    closeBtn.addEventListener('click', closeModalFn);
    cancelBtn.addEventListener('click', closeModalFn);
    overlay.addEventListener('click', closeModalFn);

    editBtns.forEach(btn => btn.addEventListener('click', () => {
        const data = {};
        fields.forEach(f => data[f] = btn.dataset[f] || (f==='userId'? btn.dataset.id : ''));
        open(true, data);
    }));

    <?php if($error_login || $error_nome || $error_senha): ?>
    open(<?= $modalData['id'] ? 'true' : 'false' ?>, <?= json_encode($modalData) ?>);
    <?php endif; ?>
});
</script>
</body>
</html>
