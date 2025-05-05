<?php

include_once('/var/www/html/dao/DAO.php');
include_once('src/dao/UsuarioDao.php');

class PostgresUsuarioDao extends DAO implements UsuarioDao {

    private $table_name = 'usuario';

    public function insere($usuario, $endereco) {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO {$this->table_name} (login, senha, nome, tipo) 
                      VALUES (:login, :senha, :nome, :tipo)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":login", $usuario->getLogin());
            $stmt->bindParam(":senha", $usuario->getSenha());
            $stmt->bindParam(":nome",  $usuario->getNome());
            $stmt->bindParam(":tipo",  $usuario->getTipo());

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $usuarioId = $this->conn->lastInsertId();

            $queryEndereco = "INSERT INTO endereco (usuario_id, rua, numero, cidade, estado, cep)
                              VALUES (:usuario_id, :rua, :numero, :cidade, :estado, :cep)";
            $stmtEndereco = $this->conn->prepare($queryEndereco);
            $stmtEndereco->bindParam(":usuario_id", $usuarioId);
            $stmtEndereco->bindParam(":rua",        $endereco->getRua());
            $stmtEndereco->bindParam(":numero",     $endereco->getNumero());
            $stmtEndereco->bindParam(":cidade",     $endereco->getCidade());
            $stmtEndereco->bindParam(":estado",     $endereco->getEstado());
            $stmtEndereco->bindParam(":cep",        $endereco->getCep());

            if (!$stmtEndereco->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $this->conn->commit();
            return $usuarioId;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function remove($usuario) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $usuario->getId());

        return $stmt->execute();
    }

    public function altera($usuario, $endereco) {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE {$this->table_name} 
                      SET login = :login, senha = :senha, nome = :nome, tipo = :tipo 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":login", $usuario->getLogin());
            $stmt->bindParam(":senha", $usuario->getSenha());
            $stmt->bindParam(":nome",  $usuario->getNome());
            $stmt->bindParam(":tipo",  $usuario->getTipo());
            $stmt->bindParam(':id',    $usuario->getId());

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $queryEndereco = "UPDATE endereco 
                              SET rua = :rua, numero = :numero, cidade = :cidade, estado = :estado, cep = :cep 
                              WHERE usuario_id = :usuario_id";

            $stmtEndereco = $this->conn->prepare($queryEndereco);
            $stmtEndereco->bindParam(":usuario_id", $usuario->getId());
            $stmtEndereco->bindParam(":rua",        $endereco->getRua());
            $stmtEndereco->bindParam(":numero",     $endereco->getNumero());
            $stmtEndereco->bindParam(":cidade",     $endereco->getCidade());
            $stmtEndereco->bindParam(":estado",     $endereco->getEstado());
            $stmtEndereco->bindParam(":cep",        $endereco->getCep());

            if (!$stmtEndereco->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function buscaPorId($id) {
        $usuario = null;

        $query = "SELECT id, login, nome, senha, tipo FROM {$this->table_name} WHERE id = ? LIMIT 1 OFFSET 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $usuario = new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return $usuario;
    }

    public function buscaPorLogin($login) {
        $usuario = null;

        $query = "SELECT id, login, nome, senha, tipo FROM {$this->table_name} WHERE login = ? LIMIT 1 OFFSET 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $login);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $usuario = new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return $usuario;
    }

    public function buscaTodos() {
        $query = "SELECT id, login, senha, nome, tipo FROM {$this->table_name} ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return $usuarios;
    }
}
?>
