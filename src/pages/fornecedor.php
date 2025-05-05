<?php
// pages/fornecedor.php
// Inicia sessão apenas se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica permissão de admin
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
$dao = $factory->getFornecedorDao();

// TRATAMENTO de POST: exclusão ou salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Exclusão inline
    if (isset($_POST['delete_id'])) {
        $rem = $dao->buscaPorId((int) $_POST['delete_id']);
        if ($rem) {
            $dao->remove($rem);
        }
        header('Location: /index.php/fornecedor');
        exit;
    }
    // Salvamento (novo ou edição)
    $id   = $_POST['id']   ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    // Validações
    $error_nome = '';
    $error_cnpj = '';
    if (empty($nome)) {
        $error_nome = 'Nome é obrigatório.';
    }
    if (empty($cnpj)) {
        $error_cnpj = 'CNPJ é obrigatório.';
    }
    if ($error_nome || $error_cnpj) {
        $qs = http_build_query([
            'error_nome' => $error_nome,
            'error_cnpj' => $error_cnpj,
            'id'         => $id,
            'nome'       => $nome,
            'cnpj'       => $cnpj,
            'q'          => $_GET['q'] ?? ''
        ]);
        header("Location: /index.php/fornecedor?$qs");
        exit;
    }
    // Carrega ou cria entidade
    if ($id) {
        $forn = $dao->buscaPorId((int)$id);
        if (!$forn) {
            header('Location: /index.php/fornecedor');
            exit;
        }
        $forn->setNome($nome);
        $forn->setCnpj($cnpj);
        $dao->altera($forn);
    } else {
        $forn = new Fornecedor(null, $nome, $cnpj);
        $dao->insere($forn);
    }
    header('Location: /index.php/fornecedor');
    exit;
}

// GET: listagem e reabertura do modal em caso de erro
$error_nome = $_GET['error_nome'] ?? '';
$error_cnpj = $_GET['error_cnpj'] ?? '';
$modalData = [
    'id'   => $_GET['id']   ?? '',
    'nome' => $_GET['nome'] ?? '',
    'cnpj' => $_GET['cnpj'] ?? ''
];
$q = trim($_GET['q'] ?? '');
$fornecedores = $dao->buscaTodos();
if ($q !== '') {
    $fornecedores = array_filter($fornecedores, function($f) use ($q) {
        return stripos($f->getNome(), $q) !== false || stripos($f->getCnpj(), $q) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin – Fornecedores</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">Fornecedores</h1>
    <!-- Busca e Novo -->
    <div class="mb-4 flex items-center space-x-4">
        <form method="GET" action="/index.php/fornecedor" class="flex-1 flex">
            <input name="q" type="text" placeholder="Pesquisar fornecedores…"
                   value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
                   class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500" />
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">Buscar</button>
        </form>
        <button id="btnNovo" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Novo Fornecedor</button>
    </div>
    <!-- Tabela -->
    <table class="min-w-full bg-white rounded shadow">
        <thead><tr class="bg-gray-200 text-left">
            <th class="px-4 py-2">Nome</th><th class="px-4 py-2">CNPJ</th><th class="px-4 py-2">Ações</th>
        </tr></thead>
        <tbody>
        <?php foreach ($fornecedores as $f): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($f->getNome(),ENT_QUOTES) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($f->getCnpj(),ENT_QUOTES) ?></td>
                <td class="px-4 py-2 space-x-2">
                    <button class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded"
                        data-id="<?= $f->getId() ?>"
                        data-nome="<?= htmlspecialchars($f->getNome(),ENT_QUOTES) ?>"
                        data-cnpj="<?= htmlspecialchars($f->getCnpj(),ENT_QUOTES) ?>">Editar</button>
                    <form method="post" action="/index.php/fornecedor" class="inline" onsubmit="return confirm('Confirma exclusão?');">
                        <input type="hidden" name="delete_id" value="<?= $f->getId() ?>">
                        <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Modal -->
    <div id="fornModal" class="fixed inset-0 flex items-center justify-center hidden">
        <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-sm">
            <div class="flex justify-between mb-4">
                <h2 id="modalTitle" class="text-xl font-semibold">Novo Fornecedor</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form id="fornForm" method="post" action="/index.php/fornecedor">
                <input type="hidden" name="id" id="fornId" value="<?= htmlspecialchars($modalData['id'],ENT_QUOTES) ?>">
                <label class="block mb-1 text-sm">Nome</label>
                <input name="nome" id="fornNome" required value="<?= htmlspecialchars($modalData['nome'],ENT_QUOTES) ?>"
                       class="w-full border px-3 py-2 rounded mb-2 focus:ring-2 focus:ring-red-500">
                <?php if ($error_nome): ?><p class="text-red-600 text-sm mb-2"><?= $error_nome ?></p><?php endif; ?>
                <label class="block mb-1 text-sm">CNPJ</label>
                <input name="cnpj" id="fornCnpj" required value="<?= htmlspecialchars($modalData['cnpj'],ENT_QUOTES) ?>"
                       class="w-full border px-3 py-2 rounded mb-4 focus:ring-2 focus:ring-red-500">
                <?php if ($error_cnpj): ?><p class="text-red-600 text-sm mb-2"><?= $error_cnpj ?></p><?php endif; ?>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',()=>{
        const modal=document.getElementById('fornModal');
        const overlay=document.getElementById('modalOverlay');
        const btnNovo=document.getElementById('btnNovo');
        const closeBtn=document.getElementById('closeModal');
        const cancelBtn=document.getElementById('cancelBtn');
        const editBtns=document.querySelectorAll('.editarBtn');
        const title=document.getElementById('modalTitle');
        const form=document.getElementById('fornForm');
        const inpId=document.getElementById('fornId');
        const inpNome=document.getElementById('fornNome');
        const inpCnpj=document.getElementById('fornCnpj');
        function openModal(edit,data){
            title.textContent=edit?'Editar Fornecedor':'Novo Fornecedor';
            form.reset(); inpId.value=data.id||'';
            inpNome.value=data.nome||''; inpCnpj.value=data.cnpj||'';
            modal.classList.remove('hidden');
        }
        function closeModal(){modal.classList.add('hidden');}
        btnNovo.addEventListener('click',()=>openModal(false,{}));
        closeBtn.addEventListener('click',closeModal);
        cancelBtn.addEventListener('click',closeModal);
        overlay.addEventListener('click',closeModal);
        editBtns.forEach(btn=>btn.addEventListener('click',()=>{
            openModal(true,{id:btn.dataset.id,nome:btn.dataset.nome,cnpj:btn.dataset.cnpj});
        }));
        // validação antes de enviar
        form.addEventListener('submit',e=>{
            if(inpNome.value.trim()===''||inpCnpj.value.trim()===''){
                e.preventDefault(); alert('Preencha nome e CNPJ');
            }
        });
        <?php if($error_nome||$error_cnpj):?>
        openModal(<?= $modalData['id']?'true':'false'?>,<?= json_encode($modalData)?>);
        <?php endif; ?>
    });
    </script>
</body>
</html>
