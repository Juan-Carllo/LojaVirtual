<?php
// pages/finalizarPedido.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../fachada.php';

$pedidoDao  = $factory->getPedidoDao();
$produtoDao = $factory->getProdutoDao();
$conn       = $factory->getConnection();

// só cliente logado pode finalizar
if (empty($_SESSION['usuario_id'])) {
    header('Location: /index.php/login');
    exit;
}

$userId   = (int)$_SESSION['usuario_id'];
$carrinho = $_SESSION['carrinho'] ?? [];

// se carrinho vazio, volta pra home com erro
if (empty($carrinho)) {
    $_SESSION['erro_pedido'] = 'Seu carrinho está vazio.';
    header('Location: /index.php/home');
    exit;
}

// se vier do botão "Finalizar Pedido"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) validar estoque
    foreach ($carrinho as $pid => $qtdDesejada) {
        $p = $produtoDao->buscaPorId((int)$pid);
        if (!$p) {
            $_SESSION['erro_pedido'] = "Produto #{$pid} não encontrado.";
            header('Location: /index.php/home');
            exit;
        }
        if ($qtdDesejada > $p->getQuantidade()) {
            $_SESSION['erro_pedido'] =
              "Estoque insuficiente para “{$p->getNome()}”. Disponível: {$p->getQuantidade()}.";
            header('Location: /index.php/home');
            exit;
        }
    }

    try {
        // 2) inicia transação
        $conn->beginTransaction();

        // 3) insere pedido com situação "NOVO"
        $pedido = new Pedido(
            usuarioId:   $userId,
            dataPedido:  date('Y-m-d H:i:s'),
            dataEntrega: null,
            situacao:    'NOVO'
        );
        $pedidoId = $pedidoDao->insere($pedido);
        if ($pedidoId < 0) {
            throw new Exception('Falha ao criar pedido.');
        }

        // 4) insere itens e decrementa estoque
        foreach ($carrinho as $pid => $qty) {
            $p = $produtoDao->buscaPorId((int)$pid);
            // insere item
            $item = new ItemPedido(
                pedidoId:   $pedidoId,
                produtoId:  $pid,
                quantidade: $qty,
                preco:      $p->getPreco()
            );
            if (!$pedidoDao->insereItem($item)) {
                throw new Exception("Falha ao inserir item do pedido: {$p->getNome()}.");
            }
            // atualiza estoque
            $novaQtd = $p->getQuantidade() - $qty;
            if (!$produtoDao->atualizarQuantidade($pid, $novaQtd)) {
                throw new Exception("Falha ao atualizar estoque do produto #{$pid}.");
            }
        }

        // 5) commit e limpa carrinho
        $conn->commit();
        unset($_SESSION['carrinho']);

        // redireciona para listagem de pedidos
        header('Location: /index.php/pedidos');
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['erro_pedido'] = "Erro ao finalizar pedido: " . $e->getMessage();
        header('Location: /index.php/home');
        exit;
    }
}

// GET — exibe o resumo e o botão
// monta itens e total
$total = 0;
$itens = [];
foreach ($carrinho as $pid => $qty) {
    $p = $produtoDao->buscaPorId((int)$pid);
    if (!$p) continue;
    $subtotal = $p->getPreco() * $qty;
    $itens[]  = ['produto' => $p, 'qty' => $qty, 'subtotal' => $subtotal];
    $total   += $subtotal;
}

// recupera mensagem de erro, se houver
$erro = $_SESSION['erro_pedido'] ?? null;
unset($_SESSION['erro_pedido']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Finalizar Pedido – Amigos do Casa</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php require __DIR__ . '/header.php'; ?>
  <main class="flex-1 p-6">
    <h1 class="text-2xl font-bold mb-4">Resumo do Pedido</h1>

    <?php if ($erro): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <?= htmlspecialchars($erro, ENT_QUOTES) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
      <p>Seu carrinho está vazio.</p>
    <?php else: ?>
      <table class="w-full bg-white rounded shadow mb-6">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-4 py-2">Produto</th>
            <th class="px-4 py-2">Qtd</th>
            <th class="px-4 py-2">Unit.</th>
            <th class="px-4 py-2">Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($itens as $it): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($it['produto']->getNome(), ENT_QUOTES) ?></td>
            <td class="px-4 py-2"><?= $it['qty'] ?></td>
            <td class="px-4 py-2">R$ <?= number_format($it['produto']->getPreco(),2,',','.') ?></td>
            <td class="px-4 py-2">R$ <?= number_format($it['subtotal'],2,',','.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="bg-white p-4 rounded shadow mb-6">
        <p class="text-right font-bold">
          Total: R$ <?= number_format($total,2,',','.') ?>
        </p>
      </div>

      <form method="post">
        <button type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
          Finalizar Pedido
        </button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
