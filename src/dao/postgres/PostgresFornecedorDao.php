<?php
// dao/postgres/PostgresFornecedorDao.php

include_once '/var/www/html/dao/DAO.php';
include_once 'src/dao/FornecedorDao.php';

class PostgresFornecedorDao extends DAO implements FornecedorDao {

    private $table_name = 'fornecedores';

    public function insere($fornecedor) {
        $query = "INSERT INTO " . $this->table_name . " (nome, contato) 
                  VALUES (:nome, :contato)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome',    $fornecedor->getNome());
        $stmt->bindParam(':contato', $fornecedor->getContato());

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            return -1;
        }
    }

    public function remove($fornecedor) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $fornecedor->getId());
        return $stmt->execute();
    }

    public function altera($fornecedor) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, contato = :contato 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome',    $fornecedor->getNome());
        $stmt->bindParam(':contato', $fornecedor->getContato());
        $stmt->bindParam(':id',      $fornecedor->getId());
        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = "SELECT id, nome, contato 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 1 OFFSET 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Fornecedor($row['id'], $row['nome'], $row['contato']);
        }
        return null;
    }

    public function buscaPorNome($nome) {
        $query = "SELECT id, nome, contato 
                  FROM " . $this->table_name . " 
                  WHERE nome ILIKE ? ORDER BY nome";
        $param = "%{$nome}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $param);
        $stmt->execute();
        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor($row['id'], $row['nome'], $row['contato']);
        }
        return $fornecedores;
    }

    public function buscaTodos() {
        $query = "SELECT id, nome, contato 
                  FROM " . $this->table_name . " 
                  ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor($row['id'], $row['nome'], $row['contato']);
        }
        return $fornecedores;
    }
}
?>
