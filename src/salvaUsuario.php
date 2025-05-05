<?php
// pages/salvaUsuario.php
// Verifica permissão de admin
if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: /index.php/home");
    exit;
}

include_once __DIR__ . '/fachada.php';

// Inicializa erros
$erro_login = $erro_nome = $erro_senha = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // 1) Dados de usuário
    $id     = $_POST["id"]     ?? null;
    $login  = trim($_POST["login"]  ?? '');
    $nome   = trim($_POST["nome"]   ?? '');
    $senha  = $_POST["senha"]  ?? '';
    $tipo   = $_POST["tipo"]   ?? 'cliente';

    // 2) Dados de endereço
    $rua         = trim($_POST["rua"]         ?? '');
    $numero      = trim($_POST["numero"]      ?? '');
    $complemento = trim($_POST["complemento"] ?? '');
    $bairro      = trim($_POST["bairro"]      ?? '');
    $cep         = trim($_POST["cep"]         ?? '');
    $cidade      = trim($_POST["cidade"]      ?? '');
    $estado      = trim($_POST["estado"]      ?? '');

    $valido = true;

    // Validações básicas
    if (empty($login)) {
        $erro_login = "Login é obrigatório!";
        $valido = false;
    }
    if (empty($nome)) {
        $erro_nome = "Nome é obrigatório!";
        $valido = false;
    }
    // senha opcional: só erro se criando novo
    if (!$id && empty($senha)) {
        $erro_senha = "Senha é obrigatória!";
        $valido = false;
    }

    if ($valido) {
        $dao = $factory->getUsuarioDao();
        $usuarioExistente = $dao->buscaPorLogin($login);
        if ($usuarioExistente && $usuarioExistente->getId() != $id) {
            $erro_login = "Erro: O login '$login' já está em uso.";
            $valido = false;
        }
    }

    if ($valido) {
        // Hash da senha se informada
        $senha_hash = $senha !== '' ? password_hash($senha, PASSWORD_DEFAULT) : null;

        // Edição ou criação
        if ($id) {
            // Busca usuário existente
            $usuario  = $dao->buscaPorId((int)$id);
            if (!$usuario) {
                header("Location: /index.php/usuario");
                exit;
            }

            // Atualiza dados do usuário
            $usuario->setLogin($login);
            $usuario->setNome($nome);
            $usuario->setTipo($tipo);            // seta o tipo
            if ($senha_hash) {
                $usuario->setSenha($senha_hash);
            }

            // Endereço existente ou novo
            $endereco = $usuario->getEndereco();
            if (!$endereco) {
                $endereco = new Endereco(
                    null,
                    $rua, $numero, $complemento,
                    $bairro, $cep, $cidade, $estado
                );
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

            // Persiste alteração
            $dao->altera($usuario, $endereco);
        } else {
            // Cria novo endereço
            $endereco = new Endereco(
                null,
                $rua, $numero, $complemento,
                $bairro, $cep, $cidade, $estado
            );
            // Cria novo usuário
            $usuario = new Usuario(
                null,
                $login,
                $senha_hash,
                $nome,
                $tipo,
                $endereco
            );
            // Persiste novo
            $dao->insere($usuario, $endereco);
        }

        header("Location: /index.php/usuario");
        exit;
    }

    // Em caso de erro, redireciona reabrindo modal
    $qs = http_build_query([
        'error_login'   => $erro_login,
        'error_nome'    => $erro_nome,
        'error_senha'   => $erro_senha,
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
    header("Location: /index.php/usuario?$qs");
    exit;
}

// Se não for POST, retorna ao usuário
header("Location: /index.php/usuario");
exit;
