<?php
session_start();
require_once __DIR__ . '/../fachada.php';
$produtoDao = $factory->getProdutoDao();

// coleta e validação de dados
$id           = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome         = trim($_POST['nome'] ?? '');
$preco        = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
$quantidade   = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
$fornecedorId = filter_input(INPUT_POST, 'fornecedorId', FILTER_VALIDATE_INT);

// obtém arquivo de imagem
$imagem = null;
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
} elseif ($id) {
    // preserva imagem existente
    $antigo = $produtoDao->buscaPorId($id);
    $imagem  = $antigo?->getImagem();
}
// remove imagem se requisitado
if (!empty($_POST['remover_imagem'])) {
    $imagem = null;
}

// monta objeto Produto e persiste
if ($id) {
    $produto = $produtoDao->buscaPorId($id);
    if (!$produto) {
        http_response_code(404);
        echo 'Produto não encontrado.';
        exit;
    }
    $produto->setNome($nome);
    $produto->setPreco($preco);
    $produto->setQuantidade($quantidade);
    $produto->setFornecedorId($fornecedorId);
    $produto->setImagem($imagem);
    $ok = $produtoDao->altera($produto);
    if ($ok) {
        echo "Produto (ID={$id}) atualizado com sucesso.";
    } else {
        http_response_code(500);
        echo 'Falha ao atualizar produto.';
    }
} else {
    $produto = new Produto($nome, $preco, $quantidade, $fornecedorId, null, $imagem);
    $newId = $produtoDao->insere($produto);
    if ($newId > 0) {
        echo "Produto criado com sucesso (ID={$newId}).";
    } else {
        http_response_code(500);
        echo 'Falha ao criar produto.';
    }
}
?>
