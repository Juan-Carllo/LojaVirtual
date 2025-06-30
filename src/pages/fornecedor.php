<?php
// pages/fornecedor.php

// 1) tratamento de sessão e permissão
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

// 2) carrega DAOs
require_once __DIR__ . '/../fachada.php';
$dao = $factory->getFornecedorDao();

// 3) POST — inserção, edição ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // a) exclusão
    if (isset($_POST['delete_id'])) {
        $rem = $dao->buscaPorId((int)$_POST['delete_id']);
        if ($rem) {
            $dao->remove($rem);
        }
        header('Location: /index.php/fornecedor');
        exit;
    }

    // b) inserção / atualização
    $id   = $_POST['id']   ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $error_nome = $error_cnpj = '';

    if ($nome === '') $error_nome = 'Nome é obrigatório.';
    if ($cnpj === '') $error_cnpj = 'CNPJ é obrigatório.';

    if ($error_nome || $error_cnpj) {
        // reabertura de modal com erro
        $qs = http_build_query([
            'error_nome' => $error_nome,
            'error_cnpj' => $error_cnpj,
            'id'         => $id,
            'nome'       => $nome,
            'cnpj'       => $cnpj,
            'q'          => $_GET['q']    ?? '',
            'page'       => $_GET['page'] ?? 1,
        ]);
        header("Location: /index.php/fornecedor?$qs");
        exit;
    }

    if ($id) {
        $forn = $dao->buscaPorId((int)$id);
        if ($forn) {
            $forn->setNome($nome);
            $forn->setCnpj($cnpj);
            $dao->altera($forn);
        }
    } else {
        $forn = new Fornecedor(null, $nome, $cnpj);
        $dao->insere($forn);
    }

    header('Location: /index.php/fornecedor');
    exit;
}

// 4) GET — listagem + paginação
$q       = trim($_GET['q']    ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($q !== '') {
    // busca por nome (sem paginação no DAO)
    $all           = $dao->buscaPorNome($q);
    $total         = count($all);
    $fornecedores  = array_slice($all, $offset, $perPage);
} else {
    // busca pagina
    $fornecedores = $dao->buscaPagina($perPage, $offset);
    $total        = $dao->contaTodos();
}

$totalPages = (int)ceil($total / $perPage);

// 5) dados para reabrir modal em caso de erro
$error_nome = $_GET['error_nome'] ?? '';
$error_cnpj = $_GET['error_cnpj'] ?? '';
$modalData  = [
    'id'   => $_GET['id']   ?? '',
    'nome' => $_GET['nome'] ?? '',
    'cnpj' => $_GET['cnpj'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – Fornecedores</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- >> só aqui incluímos o header dentro do body -->
  <?php require_once __DIR__ . '/header.php'; ?>

  <main class="flex-1 p-6">

    <h1 class="text-2xl font-bold mb-4">Fornecedores</h1>

    <!-- busca + novo -->
    <div class="mb-4 flex items-center space-x-4">
      <form method="GET" action="/index.php/fornecedor" class="flex-1 flex">
        <input
          name="q" type="text"
          placeholder="Pesquisar fornecedores…"
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
      >+ Novo Fornecedor</button>
    </div>

    <!-- tabela -->
    <table class="min-w-full bg-white rounded shadow overflow-hidden">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="px-4 py-2">Nome</th>
          <th class="px-4 py-2">CNPJ</th>
          <th class="px-4 py-2">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fornecedores as $f): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($f->getNome(), ENT_QUOTES) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($f->getCnpj(), ENT_QUOTES) ?></td>
          <td class="px-4 py-2 space-x-2">
            <button
              class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600"
              data-id="<?= $f->getId() ?>"
              data-nome="<?= htmlspecialchars($f->getNome(), ENT_QUOTES) ?>"
              data-cnpj="<?= htmlspecialchars($f->getCnpj(), ENT_QUOTES) ?>"
            >Editar</button>
            <form
              method="post"
              action="/index.php/fornecedor"
              class="inline"
              onsubmit="return confirm('Confirma exclusão?');"
            >
              <input type="hidden" name="delete_id" value="<?= $f->getId() ?>">
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

    <!-- paginação -->
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

    <!-- modal -->
    <div id="fornModal" class="fixed inset-0 flex items-center justify-center hidden">
      <div id="modalOverlay" class="absolute inset-0 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-sm">
        <div class="flex justify-between mb-4">
          <h2 id="modalTitle" class="text-xl font-semibold">Novo Fornecedor</h2>
          <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form
          id="fornForm"
          method="post"
          action="/index.php/fornecedor"
          class="space-y-4"
        >
          <input type="hidden" name="id" id="fornId" value="<?= htmlspecialchars($modalData['id'], ENT_QUOTES) ?>">

          <div>
            <label class="block text-sm">Nome</label>
            <input
              name="nome" id="fornNome" required
              value="<?= htmlspecialchars($modalData['nome'], ENT_QUOTES) ?>"
              class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500"
            >
            <?php if ($error_nome): ?>
              <p class="text-red-600 text-sm"><?= $error_nome ?></p>
            <?php endif; ?>
          </div>

          <div>
            <label class="block text-sm">CNPJ</label>
            <input
              name="cnpj" id="fornCnpj" required
              value="<?= htmlspecialchars($modalData['cnpj'], ENT_QUOTES) ?>"
              class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500"
            >
            <?php if ($error_cnpj): ?>
              <p class="text-red-600 text-sm"><?= $error_cnpj ?></p>
            <?php endif; ?>
          </div>

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

  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const modal     = document.getElementById('fornModal');
      const overlay   = document.getElementById('modalOverlay');
      const btnNovo   = document.getElementById('btnNovo');
      const closeBtn  = document.getElementById('closeModal');
      const cancelBtn = document.getElementById('cancelBtn');
      const editBtns  = document.querySelectorAll('.editarBtn');
      const titleEl   = document.getElementById('modalTitle');
      const form      = document.getElementById('fornForm');
      const idField   = document.getElementById('fornId');
      const nomeField = document.getElementById('fornNome');
      const cnpjField = document.getElementById('fornCnpj');

      function openModal(edit, data) {
        titleEl.textContent = edit ? 'Editar Fornecedor' : 'Novo Fornecedor';
        form.reset();
        idField.value   = data.id   || '';
        nomeField.value = data.nome || '';
        cnpjField.value = data.cnpj || '';
        modal.classList.remove('hidden');
      }
      function closeModal() {
        modal.classList.add('hidden');
      }

      btnNovo.addEventListener('click', () => openModal(false, {}));
      closeBtn.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);
      overlay.addEventListener('click', closeModal);

      editBtns.forEach(btn =>
        btn.addEventListener('click', () =>
          openModal(true, {
            id:   btn.dataset.id,
            nome: btn.dataset.nome,
            cnpj: btn.dataset.cnpj
          })
        )
      );

      // reabre modal em caso de erro de validação
      <?php if ($error_nome || $error_cnpj): ?>
      openModal(<?= $modalData['id'] ? 'true' : 'false' ?>, <?= json_encode($modalData) ?>);
      <?php endif; ?>
    });
  </script>
</body>
</html>
