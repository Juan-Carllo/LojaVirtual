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

        <label for="login">Login:</label>
        <input type="text"
               value="<?= htmlspecialchars($usuario->getLogin() ?? '') ?>"
               name="login" required />
        <?php if ($erro_login): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_login) ?></span><br>
        <?php endif; ?>
        <br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required />
        <?php if ($erro_senha): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_senha) ?></span><br>
        <?php endif; ?>
        <br>

        <label for="nome">Nome:</label>
        <input type="text"
               value="<?= htmlspecialchars($usuario->getNome() ?? '') ?>"
               name="nome" required />
        <?php if ($erro_nome): ?>
            <span style="color: red;"><?= htmlspecialchars($erro_nome) ?></span><br>
        <?php endif; ?>
        <br>

        <input type="hidden" name="id" value="<?= htmlspecialchars($usuario->getId() ?? '') ?>" />

        <input type="submit" value="Salvar" />

    </form>

    <br>
    <a href="../index.php">Voltar</a>
<?php
  $content = ob_get_clean();

  // 7) Renderiza o cartão com título e conteúdo
  renderCard('Registro', $content);

  // 8) Inclui o footer corretamente
  require_once __DIR__ . '/../components/footer.php';
?>
