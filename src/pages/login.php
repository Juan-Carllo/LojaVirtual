<?php 
  $pageTitle = "Login"; 
  include_once 'components/header.php'; 
?>

<?php if (isset($_SESSION['erro_login'])): ?>
    <p class="error-message">
        <?= htmlspecialchars($_SESSION['erro_login']); ?>
    </p>
    <?php 
    unset($_SESSION['erro_login']);
    ?>
<?php endif; ?>

<?php
include 'components/card.php';

// começa a capturar o html para inserir no card
ob_start();
?>

      <form method="post" action="validaLogin.php" class="space-y-4">
        <div> 
          <label for="login" class="block mb-1 font-medium">Login</label>
          <input 
            type="text"
            id="login"
            name="login"
            required
            class="w-full border border-gray-300 rounded-md shadow-sm p-1"
          >
        </div>
        <div>
          <label for="senha" class="block mb-1 font-medium">Senha</label>
          <input 
            type="password"
            id="senha"
            name="senha"
            required
            class="w-full border border-gray-300 rounded-md shadow-sm p-1"
          >
        </div>

        <button 
          type="submit" 
          value="Entrar"
          class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition"
        > 
          Login
        </button>
      </form>
      <a 
        href="usuario"
        class="mt-2 text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
      >Registrar</a>
<?php
$content = ob_get_clean(); // 

renderCard('Login', $content);
?>

<?php include_once 'components/footer.php'; ?>
