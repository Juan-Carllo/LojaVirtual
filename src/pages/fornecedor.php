<?php
// pages/fornecedor.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

require_once __DIR__ . '/../fachada.php';
$dao = $factory->getFornecedorDao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $rem = $dao->buscaPorId((int) $_POST['delete_id']);
        if ($rem) {
            $sucesso = $dao->remove($rem);
            if (!$sucesso) {
                header('Location: /index.php/fornecedor?erro=em_uso');
                exit;
            }
        }
        header('Location: /index.php/fornecedor');
        exit;
    }

    $id   = $_POST['id']   ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $error_nome = '';
    $error_cnpj = '';
    if (empty($nome)) $error_nome = 'Nome é obrigatório.';
    if (empty($cnpj)) $error_cnpj = 'CNPJ é obrigatório.';

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
<?php if (isset($_GET['erro']) && $_GET['erro'] === 'em_uso'): ?>
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
        Não é possível remover o fornecedor, pois ele está vinculado a um ou mais produtos.
    </div>
<?php endif; ?>

<h1 class="text-2xl font-bold mb-4">Fornecedores</h1>
<div class="mb-4 flex items-center space-x-4">
    <form method="GET" action="/index.php/fornecedor" class="flex-1 flex">
        <input name="q" type="text" placeholder="Pesquisar fornecedores…"
               value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
               class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500" />
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">Buscar</button>
    </form>
    <button id="btnNovo" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Novo Fornecedor</button>
</div>

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

<!-- Modal aqui (inalterado, omitido por brevidade) -->

</body>
</html>
