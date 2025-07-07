<?php
// pages/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
        rel="stylesheet">
</head>
<body class="bg-gray-100">

  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
      
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="/assets/Amigos_do_Casa_logo.png"
             alt="Logo Amigos do Casa"
             class="h-16 w-auto"/>
      </div>

      <!-- Erro de login -->
      <?php if (!empty($_SESSION['erro_login'])): ?>
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
          <?= htmlspecialchars($_SESSION['erro_login'], ENT_QUOTES) ?>
        </div>
        <?php unset($_SESSION['erro_login']); ?>
      <?php endif; ?>

      <!-- Formulário -->
      <form method="POST" action="/validaLogin.php" class="space-y-5">
        <div>
          <label for="login" class="block text-sm font-medium text-gray-700">Usuário</label>
          <input
            type="text"
            id="login"
            name="login"
            required
            placeholder="Digite seu usuário"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                   focus:ring-red-500 focus:border-red-500"
          />
        </div>
        <div>
          <label for="senha" class="block text-sm font-medium text-gray-700">Senha</label>
          <input
            type="password"
            id="senha"
            name="senha"
            required
            placeholder="••••••••"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm
                   focus:ring-red-500 focus:border-red-500"
          />
        </div>
        <button
          type="submit"
          class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white
                 font-semibold rounded-md transition"
        >
          Entrar
        </button>
      </form>

      <!-- Link registrar -->
      <p class="mt-6 text-center text-sm text-gray-600">
        Ainda não possui conta?
        <a href="/index.php/registrar" class="text-red-600 hover:underline">
          Registre-se aqui
        </a>
      </p>
    </div>
  </div>

</body>
</html>
