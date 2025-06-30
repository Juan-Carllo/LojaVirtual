<?php
// dao/postgres/PostgresEnderecoDao.php

class PostgresEnderecoDao extends DAO implements EnderecoDao {

    /**
     * Nome da tabela no banco de dados
     */
    private $table_name = 'endereco';


    /**
     * Insere um novo endereço e retorna o ID ou -1 em caso de erro.
     */
    public function insere($endereco) {
        $query = 'INSERT INTO ' . $this->table_name .
                 ' (rua, numero, complemento, bairro, cep, cidade, estado) VALUES ' .
                 '(:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':rua',          $endereco->getRua());
        $stmt->bindValue(':numero',       $endereco->getNumero());
        $stmt->bindValue(':complemento',  $endereco->getComplemento());
        $stmt->bindValue(':bairro',       $endereco->getBairro());
        $stmt->bindValue(':cep',          $endereco->getCep());
        $stmt->bindValue(':cidade',       $endereco->getCidade());
        $stmt->bindValue(':estado',       $endereco->getEstado());

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return -1;
    }

    /**
     * Remove um endereço pelo ID.
     */
    public function remove($endereco) {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $endereco->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     *  Altera um endereço
     */
    public function altera($endereco) {
        $query = 'UPDATE ' . $this->table_name .
                 ' SET rua = :rua, numero = :numero, complemento = :complemento, ' .
                 'bairro = :bairro, cep = :cep, cidade = :cidade, estado = :estado ' .
                 'WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':rua',          $endereco->getRua());
        $stmt->bindValue(':numero',       $endereco->getNumero());
        $stmt->bindValue(':complemento',  $endereco->getComplemento());
        $stmt->bindValue(':bairro',       $endereco->getBairro());
        $stmt->bindValue(':cep',          $endereco->getCep());
        $stmt->bindValue(':cidade',       $endereco->getCidade());
        $stmt->bindValue(':estado',       $endereco->getEstado());
        $stmt->bindValue(':id',           $endereco->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca um endereço pelo ID.
     */
    public function buscaPorId($id) {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Endereco(
                $row['id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
        }
        return null;
    }

    /**
     * Retorna todos os endereços.
     */
    public function buscaTodos() {
        $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY id';
        $stmt = $this->conn->query($query);
        $enderecos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $enderecos[] = new Endereco(
                $row['id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
        }
        return $enderecos;
    }
}
?>
