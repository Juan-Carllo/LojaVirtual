<?php
// pages/produto.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}
require_once __DIR__ . '/../fachada.php';
$produtoDao    = $factory->getProdutoDao();
$fornecedorDao = $factory->getFornecedorDao();

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $p = $produtoDao->buscaPorId((int) $_POST['delete_id']);
        if ($p) $produtoDao->remove($p);
        header('Location: /index.php/produto'); exit;
    }
    $id           = $_POST['id']           ?? null;
    $nome         = trim($_POST['nome']         ?? '');
    $preco        = trim($_POST['preco']        ?? '');
    $quantidade   = trim($_POST['quantidade']   ?? '');
    $fornecedorId = $_POST['fornecedorId']      ?? null;
    $error_nome       = '';
    $error_preco      = '';
    $error_quantidade = '';
    $error_fornecedor = '';
    if (empty($nome)) {
        $error_nome = 'Nome é obrigatório.';
    }
    if (!is_numeric($preco) || $preco <= 0) {
        $error_preco = 'Preço deve ser numérico e > 0.';
    }
    if (!filter_var($quantidade, FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]])) {
        $error_quantidade = 'Quantidade deve ser inteiro >= 0.';
    }
    if (empty($fornecedorId) || !$fornecedorDao->buscaPorId((int)$fornecedorId)) {
        $error_fornecedor = 'Fornecedor inválido.';
    }
    if ($error_nome || $error_preco || $error_quantidade || $error_fornecedor) {
        $qs = http_build_query([
            'error_nome'       => $error_nome,
            'error_preco'      => $error_preco,
            'error_quantidade' => $error_quantidade,
            'error_fornecedor' => $error_fornecedor,
            'id'               => $id,
            'nome'             => $nome,
            'preco'            => $preco,
            'quantidade'       => $quantidade,
            'fornecedorId'     => $fornecedorId,
            'q'                => $_GET['q'] ?? ''
        ]);
        header("Location: /index.php/produto?$qs"); exit;
    }
    if ($id) {
        $produto = $produtoDao->buscaPorId((int)$id);
        if (!$produto) { header('Location: /index.php/produto'); exit; }
    } else {
        $produto = new Produto();
    }
    $produto->setNome($nome);
    $produto->setPreco((float)$preco);
    $produto->setQuantidade((int)$quantidade);
    $produto->setFornecedorId((int)$fornecedorId);
    if ($id) {
        $produtoDao->altera($produto);
    } else {
        $produtoDao->insere($produto);
    }
    header('Location: /index.php/produto'); exit;
}

// GET
$error_nome       = $_GET['error_nome']       ?? '';
$error_preco      = $_GET['error_preco']      ?? '';
$error_quantidade = $_GET['error_quantidade'] ?? '';
$error_fornecedor = $_GET['error_fornecedor'] ?? '';
$modalData = [
    'id'           => $_GET['id']           ?? '',
    'nome'         => $_GET['nome']         ?? '',
    'preco'        => $_GET['preco']        ?? '',
    'quantidade'   => $_GET['quantidade']   ?? '',
    'fornecedorId' => $_GET['fornecedorId'] ?? ''
];
$q = trim($_GET['q'] ?? '');
$produtos    = $q !== '' ? $produtoDao->buscaPorNome($q) : $produtoDao->buscaTodos();
$fornecedores = $fornecedorDao->buscaTodos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin – Produtos</title><script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script></head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">Produtos</h1>
    <div class="mb-4 flex items-center space-x-4">
        <form method="GET" action="/index.php/produto" class="flex-1 flex">
            <input name="q" type="text" placeholder="Pesquisar produtos…" value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500" />
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">Buscar</button>
        </form>
        <button id="btnNovo" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Novo Produto</button>
    </div>
    <table class="min-w-full bg-white rounded shadow">
        <thead><tr class="bg-gray-200 text-left"><th class="px-4 py-2">Nome</th><th class="px-4 py-2">Preço</th><th class="px-4 py-2">Qtd</th><th class="px-4 py-2">Fornecedor</th><th class="px-4 py-2">Ações</th></tr></thead>
        <tbody>
        <?php foreach ($produtos as $p): ?>
            <?php $fn = $fornecedorDao->buscaPorId($p->getFornecedorId()); $fnome = $fn ? $fn->getNome() : ''; ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?php 
                    $val = $p->getPreco();
                    if (is_numeric($val)) {
                        echo 'R$ ' . number_format((float)$val, 2, ',', '.');
                    } else {
                        echo 'R$ ' . htmlspecialchars($val, ENT_QUOTES);
                    }
                ?></td>
                <td class="px-4 py-2"><?= $p->getQuantidade() ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($fnome, ENT_QUOTES) ?></td>
                <td class="px-4 py-2 space-x-2">
                    <button class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded" data-id="<?= $p->getId() ?>" data-nome="<?= htmlspecialchars($p->getNome(), ENT_QUOTES) ?>" data-preco="<?= $p->getPreco() ?>" data-quantidade="<?= $p->getQuantidade() ?>" data-fornecedor-id="<?= $p->getFornecedorId() ?>">Editar</button>
                    <form method="post" action="/index.php/produto" class="inline" onsubmit="return confirm('Confirma exclusão?');"><input type="hidden" name="delete_id" value="<?= $p->getId() ?>"><button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Excluir</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div id="prodModal" class="fixed inset-0 flex items-center justify-center hidden">
    <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-lg">
        <div class="flex justify-between mb-4">
            <h2 id="modalTitle" class="text-xl font-semibold">Novo Produto</h2>
            <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <form id="prodForm" method="post" action="/index.php/produto">
            <input type="hidden" name="id" id="prodId" value="<?= htmlspecialchars($modalData['id'], ENT_QUOTES) ?>">

            <label class="block mb-1 text-sm">Nome</label>
            <input name="nome" id="prodNome" required value="<?= htmlspecialchars($modalData['nome'], ENT_QUOTES) ?>" class="w-full border px-3 py-2 rounded mb-2 focus:ring-2 focus:ring-red-500">
            <?php if ($error_nome): ?>
                <p class="text-red-600 text-sm mb-2"><?= $error_nome ?></p>
            <?php endif; ?>

            <label class="block mb-1 text-sm">Preço</label>
            <input name="preco" id="prodPreco" required value="<?= htmlspecialchars($modalData['preco'], ENT_QUOTES) ?>" class="w-full border px-3 py-2 rounded mb-2 focus:ring-2 focus:ring-red-500">
            <?php if ($error_preco): ?>
                <p class="text-red-600 text-sm mb-2"><?= $error_preco ?></p>
            <?php endif; ?>

            <label class="block mb-1 text-sm">Quantidade</label>
            <input name="quantidade" id="prodQtd" required value="<?= htmlspecialchars($modalData['quantidade'], ENT_QUOTES) ?>" class="w-full border px-3 py-2 rounded mb-2 focus:ring-2 focus:ring-red-500">
            <?php if ($error_quantidade): ?>
                <p class="text-red-600 text-sm mb-2"><?= $error_quantidade ?></p>
            <?php endif; ?>

            <label class="block mb-1 text-sm">Fornecedor</label>
            <select name="fornecedorId" id="prodFornecedorId" required class="w-full border px-3 py-2 rounded mb-4 focus:ring-2 focus:ring-red-500">
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= $f->getId() ?>" <?= $modalData['fornecedorId'] == $f->getId() ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f->getNome(), ENT_QUOTES) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($error_fornecedor): ?>
                <p class="text-red-600 text-sm mb-2"><?= $error_fornecedor ?></p>
            <?php endif; ?>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
</div>
    <script>
    document.addEventListener('DOMContentLoaded',()=>{
        const modal = document.getElementById('prodModal');
        const overlay = document.getElementById('modalOverlay');
        const btnNovo = document.getElementById('btnNovo');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const editBtns = document.querySelectorAll('.editarBtn');
        const title = document.getElementById('modalTitle');
        const inpId = document.getElementById('prodId');
        const inpNome = document.getElementById('prodNome');
        const inpPreco = document.getElementById('prodPreco');
        const inpQtd = document.getElementById('prodQtd');
        const selForn = document.getElementById('prodFornecedorId');
        const form = document.getElementById('prodForm');
        function openModal(edit,data){title.textContent=edit?'Editar Produto':'Novo Produto';form.reset();inpId.value=data.id||'';inpNome.value=data.nome||'';inpPreco.value=data.preco||'';inpQtd.value=data.quantidade||'';selForn.value=data.fornecedorId||'';modal.classList.remove('hidden');}
        function closeModal(){modal.classList.add('hidden');}
        btnNovo.addEventListener('click',()=>openModal(false,{}));
        closeBtn.addEventListener('click',closeModal);
        cancelBtn.addEventListener('click',closeModal);
        overlay.addEventListener('click',closeModal);
        editBtns.forEach(btn => btn.addEventListener('click', () => openModal(true, {
            id: btn.dataset.id,
            nome: btn.dataset.nome,
            preco: btn.dataset.preco,
            quantidade: btn.dataset.quantidade,
            fornecedorId: btn.dataset.fornecedorId
        })) );
        form.addEventListener('submit',e=>{if(inpNome.value.trim()===''||inpPreco.value.trim()===''||inpQtd.value.trim()===''||selForn.value===''){e.preventDefault();alert('Preencha todos os campos');}});
        <?php if($error_nome||$error_preco||$error_quantidade||$error_fornecedor): ?>
        openModal(true,<?= json_encode($modalData) ?>);
        <?php endif; ?>
    });
    </script>
</body>
</html>
