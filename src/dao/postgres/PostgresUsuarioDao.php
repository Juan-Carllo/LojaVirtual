<?php

include_once('/var/www/html/dao/DAO.php');
include_once('src/dao/UsuarioDao.php');

class PostgresUsuarioDao extends DAO implements UsuarioDao
{
    private $table_name = 'usuario';

    public function insere($usuario, $endereco)
    {
        try {
            $this->conn->beginTransaction();

            // Inserir endereço
            $queryEndereco = "INSERT INTO endereco (rua, numero, complemento, bairro, cep, cidade, estado)
                              VALUES (:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)
                              RETURNING id";
            $stmtEndereco = $this->conn->prepare($queryEndereco);

            $rua = $endereco->getRua();
            $numero = $endereco->getNumero();
            $complemento = $endereco->getComplemento();
            $bairro = $endereco->getBairro();
            $cep = $endereco->getCep();
            $cidade = $endereco->getCidade();
            $estado = $endereco->getEstado();

            $stmtEndereco->bindParam(":rua", $rua);
            $stmtEndereco->bindParam(":numero", $numero);
            $stmtEndereco->bindParam(":complemento", $complemento);
            $stmtEndereco->bindParam(":bairro", $bairro);
            $stmtEndereco->bindParam(":cep", $cep);
            $stmtEndereco->bindParam(":cidade", $cidade);
            $stmtEndereco->bindParam(":estado", $estado);

            if (!$stmtEndereco->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $enderecoId = $stmtEndereco->fetchColumn();

            // Inserir usuário
            $query = "INSERT INTO {$this->table_name} (login, senha, nome, tipo, endereco_id) 
                      VALUES (:login, :senha, :nome, :tipo, :endereco_id)";
            $stmt = $this->conn->prepare($query);

            $login = $usuario->getLogin();
            $senha = $usuario->getSenha();
            $nome = $usuario->getNome();
            $tipo = $usuario->getTipo();

            $stmt->bindParam(":login", $login);
            $stmt->bindParam(":senha", $senha);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":tipo", $tipo);
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

    public function altera($usuario, $endereco)
    {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE {$this->table_name}
                      SET login = :login, senha = :senha, nome = :nome, tipo = :tipo
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $login = $usuario->getLogin();
            $senha = $usuario->getSenha();
            $nome = $usuario->getNome();
            $tipo = $usuario->getTipo();
            $id = $usuario->getId();

            $stmt->bindParam(":login", $login);
            $stmt->bindParam(":senha", $senha);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":tipo", $tipo);
            $stmt->bindParam(":id", $id);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $queryEnderecoId = "SELECT endereco_id FROM {$this->table_name} WHERE id = :id";
            $stmtEnderecoId = $this->conn->prepare($queryEnderecoId);
            $stmtEnderecoId->bindParam(":id", $id);
            $stmtEnderecoId->execute();
            $enderecoId = $stmtEnderecoId->fetchColumn();

            $queryEndereco = "UPDATE endereco 
                              SET rua = :rua, numero = :numero, complemento = :complemento,
                                  bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado
                              WHERE id = :id";
            $stmtEndereco = $this->conn->prepare($queryEndereco);

            $rua = $endereco->getRua();
            $numero = $endereco->getNumero();
            $complemento = $endereco->getComplemento();
            $bairro = $endereco->getBairro();
            $cep = $endereco->getCep();
            $cidade = $endereco->getCidade();
            $estado = $endereco->getEstado();

            $stmtEndereco->bindParam(":rua", $rua);
            $stmtEndereco->bindParam(":numero", $numero);
            $stmtEndereco->bindParam(":complemento", $complemento);
            $stmtEndereco->bindParam(":bairro", $bairro);
            $stmtEndereco->bindParam(":cep", $cep);
            $stmtEndereco->bindParam(":cidade", $cidade);
            $stmtEndereco->bindParam(":estado", $estado);
            $stmtEndereco->bindParam(":id", $enderecoId);

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

    public function remove($usuario)
    {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $id = $usuario->getId();
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function buscaPorId($id)
    {
        $query = "SELECT u.id, u.login, u.nome, u.senha, u.tipo,
                         e.id as endereco_id, e.rua, e.numero, e.complemento,
                         e.bairro, e.cep, e.cidade, e.estado
                  FROM {$this->table_name} u
                  LEFT JOIN endereco e ON u.endereco_id = e.id
                  WHERE u.id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $endereco = new Endereco(
                $row['endereco_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );

            return new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo'], $endereco);
        }

        return null;
    }

    public function buscaPorLogin($login)
    {
        $query = "SELECT id, login, nome, senha, tipo FROM {$this->table_name} WHERE login = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $login);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Usuario($row['id'], $row['login'], $row['senha'], $row['nome'], $row['tipo']);
        }

        return null;
    }

    public function buscaTodos()
    {
        $query = "SELECT u.id, u.login, u.senha, u.nome, u.tipo,
                         e.id as endereco_id, e.rua, e.numero, e.complemento,
                         e.bairro, e.cep, e.cidade, e.estado
                  FROM {$this->table_name} u
                  LEFT JOIN endereco e ON u.endereco_id = e.id
                  ORDER BY u.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $endereco = new Endereco(
                $row['endereco_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );

            $usuario = new Usuario(
                $row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo'],
                $endereco
            );

            $usuarios[] = $usuario;
        }

        return $usuarios;
    }
}
