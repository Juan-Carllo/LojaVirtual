<?php
// pages/produto.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home"); exit;
}
require_once __DIR__ . '/../fachada.php';
require_once __DIR__ . '/header.php'; 
$produtoDao    = $factory->getProdutoDao();
$fornecedorDao = $factory->getFornecedorDao();

// Exclusão via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $p = $produtoDao->buscaPorId((int)$_POST['delete_id']);
    if ($p) $produtoDao->remove($p);
    header('Location: /index.php/produto'); exit;
}

// Busca e listagem
$q = trim($_GET['q'] ?? '');
$produtos     = $q !== ''
    ? $produtoDao->buscaPorNome($q)
    : $produtoDao->buscaTodos();
$fornecedores = $fornecedorDao->buscaTodos();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin – Produtos</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<main class="flex-1 p-6">
  <h1 class="text-2xl font-bold mb-4">Produtos</h1>

  <div class="mb-4 flex items-center space-x-4">
    <form method="GET" action="/index.php/produto" class="flex-1 flex">
      <input name="q" type="text" placeholder="Pesquisar produtos…" value="<?=htmlspecialchars($q, ENT_QUOTES)?>"
        class="flex-1 border px-4 py-2 rounded-l focus:ring-2 focus:ring-red-500" />
      <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-r hover:bg-red-700">Buscar</button>
    </form>
    <button id="btnNovo" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Novo Produto</button>
  </div>

  <div id="msg" class="mb-4 text-center text-green-600"></div>

  <table class="min-w-full bg-white rounded shadow">
    <thead>
      <tr class="bg-gray-200 text-left">
        <th class="px-4 py-2">Imagem</th>
        <th class="px-4 py-2">Nome</th>
        <th class="px-4 py-2">Preço</th>
        <th class="px-4 py-2">Qtd</th>
        <th class="px-4 py-2">Fornecedor</th>
        <th class="px-4 py-2">Ações</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($produtos as $p): ?>
      <?php $fn = $fornecedorDao->buscaPorId($p->getFornecedorId()); ?>
      <tr class="border-t">
        <td class="px-4 py-2">
          <?php if ($p->getImagem()): ?>
            <img src="data:image/jpeg;base64,<?=base64_encode($p->getImagem())?>" width="60" class="rounded">
          <?php else: ?>—<?php endif; ?>
        </td>
        <td class="px-4 py-2"><?=htmlspecialchars($p->getNome(),ENT_QUOTES)?></td>
        <td class="px-4 py-2">R$ <?=number_format($p->getPreco(),2,',','.')?></td>
        <td class="px-4 py-2"><?=$p->getQuantidade()?></td>
        <td class="px-4 py-2"><?=htmlspecialchars($fn ? $fn->getNome() : '',ENT_QUOTES)?></td>
        <td class="px-4 py-2 space-x-2">
          <button class="editarBtn bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600"
              data-id="<?=$p->getId()?>"
              data-nome="<?=htmlspecialchars($p->getNome(),ENT_QUOTES)?>"
              data-preco="<?=$p->getPreco()?>"
              data-quantidade="<?=$p->getQuantidade()?>"
              data-fornecedor-id="<?=$p->getFornecedorId()?>">
            Editar
          </button>
          <form method="post" action="/index.php/produto" class="inline" onsubmit="return confirm('Confirma exclusão?');">
            <input type="hidden" name="delete_id" value="<?=$p->getId()?>">
            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Excluir</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Modal -->
  <div id="prodModal" class="fixed inset-0 flex items-center justify-center hidden">
    <div id="modalOverlay" class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative bg-white rounded shadow-lg p-6 z-10 w-full max-w-lg">

      <div class="flex justify-between mb-4">
        <h2 id="modalTitle" class="text-xl font-semibold">Novo Produto</h2>
        <button id="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <form id="uploadForm" method="post" action="/ajax/produto_upload_ajax.php" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" id="prodId">

        <div>
          <label class="block text-sm">Nome</label>
          <input type="text" name="nome" id="prodNome" required class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
        </div>

        <div>
          <label class="block text-sm">Preço</label>
          <input type="text" name="preco" id="prodPreco" required class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
        </div>

        <div>
          <label class="block text-sm">Quantidade</label>
          <input type="number" name="quantidade" id="prodQtd" min="0" required class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
        </div>

        <div>
          <label class="block text-sm">Fornecedor</label>
          <select name="fornecedorId" id="prodFornecedorId" required class="w-full border px-3 py-2 rounded focus:ring-2 focus:ring-red-500">
            <option value="">Selecione...</option>
            <?php foreach ($fornecedores as $f): ?>
              <option value="<?=$f->getId()?>" <?=isset($modalData['fornecedorId']) && $modalData['fornecedorId']==$f->getId()?'selected':''?>>
                <?=htmlspecialchars($f->getNome(),ENT_QUOTES)?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm">Imagem Atual</label>
          <div id="currentImage" class="mb-2"></div>
          <label class="inline-flex items-center">
            <input type="checkbox" name="remover_imagem" id="removerImagem" class="mr-2">
            Remover imagem
          </label>
        </div>

        <div>
          <label class="block text-sm">Imagem do Produto</label>
          <input type="file" name="imagem" id="prodImagem" accept="image/*" class="w-full border p-2 rounded">
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
$(function() {
  const modal   = $('#prodModal');
  const overlay = $('#modalOverlay');
  const form    = $('#uploadForm');
  const msg     = $('#msg');

  // Preview de imagem no file input
  $('#prodImagem').on('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      $('#currentImage').html(`<img src="${e.target.result}" width="100" class="rounded">`);
    };
    reader.readAsDataURL(file);
  });

  function openModal(edit, data) {
    $('#modalTitle').text(edit ? 'Editar Produto' : 'Novo Produto');
    form[0].reset(); msg.text('');
    $('#prodId').val(data.id || '');
    $('#prodNome').val(data.nome || '');
    $('#prodPreco').val(data.preco || '');
    $('#prodQtd').val(data.quantidade || '');
    $('#prodFornecedorId').val(data.fornecedorId || '');
    if (edit && data.imagem) {
      $('#currentImage').html(`<img src="${data.imagem}" width="100" class="rounded">`);
      $('#removerImagem').prop('checked', false);
    } else {
      $('#currentImage').empty();
    }
    modal.removeClass('hidden');
  }

  function closeModal() {
    modal.addClass('hidden');
  }

  // Novo produto
  $('#btnNovo').on('click', () => openModal(false, {}));

  // Fechar modal
  $('#closeModal, #cancelBtn').on('click', closeModal);
  overlay.on('click', closeModal);

  // Editar produto
  $('.editarBtn').on('click', function() {
    const btn = $(this);
    const row = btn.closest('tr');
    openModal(true, {
      id:            btn.data('id'),
      nome:          btn.data('nome'),
      preco:         btn.data('preco'),
      quantidade:    btn.data('quantidade'),
      fornecedorId:  btn.data('fornecedor-id'),
      imagem:        row.find('td').eq(0).find('img').attr('src') || ''
    });
  });

  // Envio AJAX
  form.on('submit', function(e) {
    e.preventDefault();
    msg.text('Salvando...');
    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: new FormData(this),
      contentType: false,
      processData: false,
      success: function(res) {
        msg.text(res);
        setTimeout(() => location.reload(), 800);
      },
      error: function(xhr) {
        msg.text('Erro: ' + xhr.responseText);
      }
    });
  });
});
</script>
</body>
</html>
