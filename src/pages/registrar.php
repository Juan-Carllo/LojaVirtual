<?php
// pages/registrar.php

// 1) Ajuste do include da fachada (volta uma pasta)
require_once __DIR__ . '/../fachada.php';

// 2) Inicializa sessão, caso ainda não exista
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 3) Cria instância de usuário com tipo 'cliente'
$usuario = new Usuario(null, null, null, null, 'cliente');
// 3b) Cria instância de Endereco vazia
$endereco = new Endereco(null, '', '', '', '', '', '', '');

// 4) Captura erros na querystring
$erro_login = $_GET['erro_login'] ?? '';
$erro_nome  = $_GET['erro_nome']  ?? '';
$erro_senha = $_GET['erro_senha'] ?? '';

?>
<?php 
  $pageTitle = "Registro"; 
  // 5) Inclui header corretamente
  require_once __DIR__ . '/../components/header.php'; 
?>
<?php
  // 6) Inclui o componente do card (define renderCard)
  require_once __DIR__ . '/../components/card.php';

  // começa a capturar o html
  ob_start();
?>
<form action="../salvaUsuario.php" method="post" class="space-y-4">

  <!-- Login -->
  <div>
    <label for="login" class="block text-sm font-medium">Login</label>
    <input
      type="text"
      id="login"
      name="login"
      value="<?= htmlspecialchars($usuario->getLogin() ?? '') ?>"
      required
      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
    />
    <?php if ($erro_login): ?>
      <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erro_login) ?></p>
    <?php endif; ?>
  </div>

  <!-- Senha -->
  <div>
    <label for="senha" class="block text-sm font-medium">Senha</label>
    <input
      type="password"
      id="senha"
      name="senha"
      required
      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
    />
    <?php if ($erro_senha): ?>
      <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erro_senha) ?></p>
    <?php endif; ?>
  </div>

  <!-- Nome -->
  <div>
    <label for="nome" class="block text-sm font-medium">Nome</label>
    <input
      type="text"
      id="nome"
      name="nome"
      value="<?= htmlspecialchars($usuario->getNome() ?? '') ?>"
      required
      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
    />
    <?php if ($erro_nome): ?>
      <p class="text-red-600 text-sm mt-1"><?= htmlspecialchars($erro_nome) ?></p>
    <?php endif; ?>
  </div>

  <!-- Endereço -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label for="rua" class="block text-sm font-medium">Rua</label>
      <input
        type="text"
        id="rua"
        name="rua"
        value="<?= htmlspecialchars($endereco->getRua() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="numero" class="block text-sm font-medium">Número</label>
      <input
        type="text"
        id="numero"
        name="numero"
        value="<?= htmlspecialchars($endereco->getNumero() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="complemento" class="block text-sm font-medium">Complemento</label>
      <input
        type="text"
        id="complemento"
        name="complemento"
        value="<?= htmlspecialchars($endereco->getComplemento() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="bairro" class="block text-sm font-medium">Bairro</label>
      <input
        type="text"
        id="bairro"
        name="bairro"
        value="<?= htmlspecialchars($endereco->getBairro() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="cep" class="block text-sm font-medium">CEP</label>
      <input
        type="text"
        id="cep"
        name="cep"
        value="<?= htmlspecialchars($endereco->getCep() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="cidade" class="block text-sm font-medium">Cidade</label>
      <input
        type="text"
        id="cidade"
        name="cidade"
        value="<?= htmlspecialchars($endereco->getCidade() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    <div>
      <label for="estado" class="block text-sm font-medium">Estado</label>
      <input
        type="text"
        id="estado"
        name="estado"
        value="<?= htmlspecialchars($endereco->getEstado() ?? '') ?>"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
  </div>

  <!-- ID oculto para edição -->
  <input type="hidden" name="id" value="<?= htmlspecialchars($usuario->getId() ?? '') ?>" />

 <!-- Tipo oculto -->
<input type="hidden" name="tipo" value="cliente" />

<!-- Botões -->
<div class="flex justify-between items-center pt-4">
  <a href="../index.php" class="text-blue-600 hover:text-blue-800 hover:underline">Voltar</a>
  <button
    type="submit"
    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-md transition"
  >
    Registrar
  </button>
</div>


</form>

<?php
  $content = ob_get_clean();
  // 7) Renderiza o cartão com título e conteúdo
  renderCard('Registro', $content);
  // 8) Inclui o footer corretamente
  require_once __DIR__ . '/../components/footer.php';
?>
