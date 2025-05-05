<?php

include_once('/var/www/html/dao/DAO.php');
include_once('src/dao/UsuarioDao.php');

class PostgresUsuarioDao extends DAO implements UsuarioDao {

    private $table_name = 'usuario';

    public function insere($usuario, $endereco) {
        try {
            $this->conn->beginTransaction();

            // 1. Insere endereço
            $queryEndereco = "INSERT INTO endereco (rua, numero, complemento, bairro, cep, cidade, estado)
                              VALUES (:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)
                              RETURNING id";
            $stmtEndereco = $this->conn->prepare($queryEndereco);
            $stmtEndereco->bindParam(":rua",         $endereco->getRua());
            $stmtEndereco->bindParam(":numero",      $endereco->getNumero());
            $stmtEndereco->bindParam(":complemento", $endereco->getComplemento());
            $stmtEndereco->bindParam(":bairro",      $endereco->getBairro());
            $stmtEndereco->bindParam(":cep",         $endereco->getCep());
            $stmtEndereco->bindParam(":cidade",      $endereco->getCidade());
            $stmtEndereco->bindParam(":estado",      $endereco->getEstado());

            if (!$stmtEndereco->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $enderecoId = $stmtEndereco->fetchColumn();

            // 2. Insere usuário com o endereco_id
            $query = "INSERT INTO {$this->table_name} (login, senha, nome, tipo, endereco_id) 
                      VALUES (:login, :senha, :nome, :tipo, :endereco_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":login",       $usuario->getLogin());
            $stmt->bindParam(":senha",       $usuario->getSenha());
            $stmt->bindParam(":nome",        $usuario->getNome());
            $stmt->bindParam(":tipo",        $usuario->getTipo());
            $stmt->bindParam(":endereco_id", $enderecoId);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $usuarioId = $this->conn->lastInsertId();

            $this->conn->commit();
            return $usuarioId;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function altera($usuario, $endereco) {
        try {
            $this->conn->beginTransaction();

            // Atualiza usuário
            $query = "UPDATE {$this->table_name}
                      SET login = :login, senha = :senha, nome = :nome, tipo = :tipo
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":login", $usuario->getLogin());
            $stmt->bindParam(":senha", $usuario->getSenha());
            $stmt->bindParam(":nome",  $usuario->getNome());
            $stmt->bindParam(":tipo",  $usuario->getTipo());
            $stmt->bindParam(":id",    $usuario->getId());

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Pega endereço_id do usuário
            $queryEnderecoId = "SELECT endereco_id FROM {$this->table_name} WHERE id = :id";
            $stmtEnderecoId = $this->conn->prepare($queryEnderecoId);
            $stmtEnderecoId->bindParam(":id", $usuario->getId());
            $stmtEnderecoId->execute();
            $enderecoId = $stmtEnderecoId->fetchColumn();

            // Atualiza endereço
            $queryEndereco = "UPDATE endereco 
                              SET rua = :rua, numero = :numero, complemento = :complemento,
                                  bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado
                              WHERE id = :id";
            $stmtEndereco = $this->conn->prepare($queryEndereco);
            $stmtEndereco->bindParam(":rua",         $endereco->getRua());
            $stmtEndereco->bindParam(":numero",      $endereco->getNumero());
            $stmtEndereco->bindParam(":complemento", $endereco->getComplemento());
            $stmtEndereco->bindParam(":bairro",      $endereco->getBairro());
            $stmtEndereco->bindParam(":cep",         $endereco->getCep());
            $stmtEndereco->bindParam(":cidade",      $endereco->getCidade());
            $stmtEndereco->bindParam(":estado",      $endereco->getEstado());
            $stmtEndereco->bindParam(":id",          $enderecoId);

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

    public function remove($usuario) {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $usuario->getId());
        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT id, login, nome, senha, tipo FROM {$this->table_name} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return null;
    }

    public function buscaPorLogin($login) {
        $query = "SELECT id, login, nome, senha, tipo FROM {$this->table_name} WHERE login = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $login);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return null;
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
