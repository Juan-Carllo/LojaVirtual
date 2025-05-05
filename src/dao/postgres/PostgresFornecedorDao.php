<?php
// dao/postgres/PostgresFornecedorDao.php

include_once '/var/www/html/dao/DAO.php';
include_once 'src/dao/FornecedorDao.php';

class PostgresFornecedorDao extends DAO implements FornecedorDao {

    /**
     * Nome da tabela no banco de dados
     */
    private $table_name = 'fornecedores';

    /**
     * Insere um novo fornecedor e retorna o ID ou -1 em caso de erro.
     */
    public function insere($fornecedor) {
        $sql = "INSERT INTO " . $this->table_name . " (nome, cnpj) VALUES (:nome, :cnpj)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $fornecedor->getNome());
        $stmt->bindValue(':cnpj', $fornecedor->getCnpj());

        return $stmt->execute()
            ? $this->conn->lastInsertId()
            : -1;
    }

    /**
     * Remove um fornecedor pelo ID.
     */
    public function remove($fornecedor) {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $fornecedor->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Atualiza nome e CNPJ de um fornecedor existente.
     */
    public function altera($fornecedor) {
        $sql = "UPDATE " . $this->table_name . " SET nome = :nome, cnpj = :cnpj WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $fornecedor->getNome());
        $stmt->bindValue(':cnpj', $fornecedor->getCnpj());
        $stmt->bindValue(':id', $fornecedor->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca um fornecedor pelo ID.
     */
    public function buscaPorId($id) {
        $sql = "SELECT id, nome, cnpj FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? new Fornecedor($row['id'], $row['nome'], $row['cnpj'])
            : null;
    }

    /**
     * Busca fornecedores cujo nome contém o termo informado.
     */
    public function buscaPorNome($nome) {
        $sql = "SELECT id, nome, cnpj FROM " . $this->table_name . " WHERE nome ILIKE :nome ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', "%{$nome}%");
        $stmt->execute();

        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor($row['id'], $row['nome'], $row['cnpj']);
        }
        return $fornecedores;
    }

    /**
     * Retorna todos os fornecedores.
     */
    public function buscaTodos() {
        $sql = "SELECT id, nome, cnpj FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor($row['id'], $row['nome'], $row['cnpj']);
        }
        return $fornecedores;
    }
}
?>
