<?php
include_once('/var/www/html/dao/DAO.php');
include_once('src/dao/UsuarioDao.php');
include_once('src/dao/EnderecoDao.php');
include_once('/var/www/html/model/Usuario.php');
include_once('/var/www/html/model/Endereco.php');
include_once('/var/www/html/dao/postgres/PostgresEnderecoDao.php');

class PostgresUsuarioDao extends DAO implements UsuarioDao {
    private $table_name = 'usuario';
    /** @var EnderecoDao */
    private $enderecoDao;

    public function __construct(PDO $connection) {
        parent::__construct($connection);
        $this->enderecoDao = new PostgresEnderecoDao($connection);
    }

    public function insere($usuario, $endereco) {
        // 1) insere o endereco e pega o id
        $endId = $this->enderecoDao->insere($endereco);
        if ($endId < 0) {
            return -1;
        }
        $usuario->setEnderecoId($endId);

        // 2) insere o usuario referenciando endereco
        $query = "INSERT INTO {$this->table_name} " .
                 "(login, senha, nome, tipo, endereco_id) VALUES " .
                 "(:login, :senha, :nome, :tipo, :endereco_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':login', $usuario->getLogin());
        $stmt->bindValue(':senha', $usuario->getSenha());
        $stmt->bindValue(':nome',  $usuario->getNome());
        $stmt->bindValue(':tipo',  $usuario->getTipo());
        $stmt->bindValue(':endereco_id', $endId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return -1;
    }

    public function remove($usuario) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);
        $ok = $stmt->execute();
        return $ok;
    }

    public function altera($usuario, $endereco) {
        // 1) atualiza o endereco
        $this->enderecoDao->altera($endereco);

        // 2) atualiza o usuario
        $query = "UPDATE {$this->table_name} SET " .
                 "login = :login, senha = :senha, nome = :nome, tipo = :tipo " .
                 "WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':login', $usuario->getLogin());
        $stmt->bindValue(':senha', $usuario->getSenha());
        $stmt->bindValue(':nome',  $usuario->getNome());
        $stmt->bindValue(':tipo',  $usuario->getTipo());
        $stmt->bindValue(':id',    $usuario->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT u.*, e.id AS e_id, e.rua, e.numero, e.complemento, e.bairro, e.cep, e.cidade, e.estado " .
                 "FROM {$this->table_name} u " .
                 "LEFT JOIN endereco e ON u.endereco_id = e.id " .
                 "WHERE u.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $end = new Endereco(
                $row['e_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
            $user = new Usuario(
                $row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo']
            );
            $user->setEndereco($end);
            return $user;
        }
        return null;
    }

    public function buscaPorLogin($login) {
        $query = "SELECT u.*, e.id AS e_id, e.rua, e.numero, e.complemento, e.bairro, e.cep, e.cidade, e.estado " .
                 "FROM {$this->table_name} u " .
                 "LEFT JOIN endereco e ON u.endereco_id = e.id " .
                 "WHERE u.login = :login";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $end = new Endereco(
                $row['e_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
            $user = new Usuario(
                $row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo']
            );
            $user->setEndereco($end);
            return $user;
        }
        return null;
    }

    public function buscaTodos() {
        $query = "SELECT u.*, e.id AS e_id, e.rua, e.numero, e.complemento, e.bairro, e.cep, e.cidade, e.estado " .
                 "FROM {$this->table_name} u " .
                 "LEFT JOIN endereco e ON u.endereco_id = e.id ORDER BY u.id";
        $stmt = $this->conn->query($query);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $end = new Endereco(
                $row['e_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
            $user = new Usuario(
                $row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo']
            );
            $user->setEndereco($end);
            $users[] = $user;
        }
        return $users;
    }
}
?>
