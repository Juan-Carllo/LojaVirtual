<?php
// pages/salvaUsuario.php

// se não tiver seção, inicia uma 
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/fachada.php';

// Detecta se é edição
$isEdicao = !empty($_POST['id']);

// Verifica permissão de admin apenas para edição
if ($isEdicao && ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

$erro_login = $erro_nome = $erro_senha = "";
$erro_rua = $erro_numero = $erro_bairro = $erro_cep = $erro_cidade = $erro_estado = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $id     = $_POST["id"]     ?? null;
    $login  = trim($_POST["login"]  ?? '');
    $nome   = trim($_POST["nome"]   ?? '');
    $senha  = $_POST["senha"]       ?? '';
    $tipo   = $_POST["tipo"]        ?? 'cliente';

    $rua         = trim($_POST["rua"]         ?? '');
    $numero      = trim($_POST["numero"]      ?? '');
    $complemento = trim($_POST["complemento"] ?? '');
    $bairro      = trim($_POST["bairro"]      ?? '');
    $cep         = trim($_POST["cep"]         ?? '');
    $cidade      = trim($_POST["cidade"]      ?? '');
    $estado      = trim($_POST["estado"]      ?? '');

    $valido = true;

    // Validações do usuário
    if (empty($login)) {
        $erro_login = "Login é obrigatório!";
        $valido = false;
    }
    if (empty($nome)) {
        $erro_nome = "Nome é obrigatório!";
        $valido = false;
    }
    if (!$id && empty($senha)) {
        $erro_senha = "Senha é obrigatória!";
        $valido = false;
    }

    // Validações do endereço
    if (empty($rua)) {
        $erro_rua = "Rua é obrigatória!";
        $valido = false;
    }
    if (empty($numero)) {
        $erro_numero = "Número é obrigatório!";
        $valido = false;
    }
    if (empty($bairro)) {
        $erro_bairro = "Bairro é obrigatório!";
        $valido = false;
    }
    if (empty($cep)) {
        $erro_cep = "CEP é obrigatório!";
        $valido = false;
    }
    if (empty($cidade)) {
        $erro_cidade = "Cidade é obrigatória!";
        $valido = false;
    }
    if (empty($estado)) {
        $erro_estado = "Estado é obrigatório!";
        $valido = false;
    }

    $dao = $factory->getUsuarioDao();

    if ($valido) {
        $usuarioExistente = $dao->buscaPorLogin($login);
        if ($usuarioExistente && $usuarioExistente->getId() != $id) {
            $erro_login = "O login '$login' já está em uso.";
            $valido = false;
        }
    }

    if ($valido) {
        // encripta senha
        $senha_hash = $senha !== '' ? password_hash($senha, PASSWORD_DEFAULT) : null;

        if ($id) {
            // Editar
            $usuario = $dao->buscaPorId((int)$id);
            if (!$usuario) {
                header("Location: /index.php/usuario");
                exit;
            }

            $usuario->setLogin($login);
            $usuario->setNome($nome);
            $usuario->setTipo($tipo);
            if ($senha_hash) {
                $usuario->setSenha($senha_hash);
            }

            $endereco = $usuario->getEndereco();
            if (!$endereco) {
                $endereco = new Endereco(null, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado);
            } else {
                $endereco->setRua($rua);
                $endereco->setNumero($numero);
                $endereco->setComplemento($complemento);
                $endereco->setBairro($bairro);
                $endereco->setCep($cep);
                $endereco->setCidade($cidade);
                $endereco->setEstado($estado);
            }

            $usuario->setEndereco($endereco);
            $dao->altera($usuario, $endereco);
        } else {
            // Novo
            $endereco = new Endereco(null, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado);
            $usuario = new Usuario(null, $login, $senha_hash, $nome, $tipo, $endereco);
            $dao->insere($usuario, $endereco);
        }

        if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin') {
            header("Location: /index.php/usuario");
        } else {
            header("Location: /index.php/login");
        }
        exit;
    }

    // Em caso de erro
    $qs = http_build_query([
        'error_login'   => $erro_login,
        'error_nome'    => $erro_nome,
        'error_senha'   => $erro_senha,
        'error_rua'     => $erro_rua,
        'error_numero'  => $erro_numero,
        'error_bairro'  => $erro_bairro,
        'error_cep'     => $erro_cep,
        'error_cidade'  => $erro_cidade,
        'error_estado'  => $erro_estado,
        'id'            => $id,
        'login'         => $login,
        'nome'          => $nome,
        'tipo'          => $tipo,
        'rua'           => $rua,
        'numero'        => $numero,
        'complemento'   => $complemento,
        'bairro'        => $bairro,
        'cep'           => $cep,
        'cidade'        => $cidade,
        'estado'        => $estado,
    ]);

    $destino = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin') ? 'usuario' : 'registrar';
    header("Location: /index.php/$destino?$qs");
    exit;
}

header("Location: /index.php/home");
exit;
