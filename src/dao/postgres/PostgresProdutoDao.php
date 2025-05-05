<?php
// dao/postgres/PostgresProdutoDao.php

// Carrega a classe base DAO e a interface ProdutoDao
require_once '/var/www/html/dao/DAO.php';
require_once '/var/www/html/dao/ProdutoDao.php';
// Carrega o modelo Produto
require_once '/var/www/html/model/Produto.php';

class PostgresProdutoDao extends DAO implements ProdutoDao {
    // Nome da tabela conforme script do banco
    private $table_name = 'produto';

    public function insere($produto) {
        $query = 'INSERT INTO ' . $this->table_name .
                 ' (nome, preco, quantidade, fornecedor_id) VALUES ' .
                 '(:nome, :preco, :quantidade, :fornecedor_id)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome());
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade());
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId());

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return -1;
    }

    public function remove($produto) {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $produto->getId());
        return $stmt->execute();
    }

    public function altera($produto) {
        $query = 'UPDATE ' . $this->table_name .
                 ' SET nome = :nome, preco = :preco, quantidade = :quantidade, ' .
                 'fornecedor_id = :fornecedor_id WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome());
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade());
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId());
        $stmt->bindValue(':id',            $produto->getId());
        return $stmt->execute();
    }

    public function buscaPorId($id) {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Produto(
                $row['id'],
                $row['nome'],
                $row['preco'],
                $row['quantidade'],
                $row['fornecedor_id']
            );
        }
        return null;
    }

    // Método de busca por nome adicionado
    public function buscaPorNome($nome) {
        $query = 'SELECT * FROM ' . $this->table_name .
                 ' WHERE nome ILIKE :nome ORDER BY nome';
        $stmt = $this->conn->prepare($query);
        $like = "%{$nome}%";
        $stmt->bindValue(':nome', $like, PDO::PARAM_STR);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Produto(
                $row['id'],
                $row['nome'],
                $row['preco'],
                $row['quantidade'],
                $row['fornecedor_id']
            );
        }
        return $result;
    }

    public function buscaTodos() {
        $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY nome';
        $stmt = $this->conn->query($query);
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = new Produto(
                $row['id'],
                $row['nome'],
                $row['preco'],
                $row['quantidade'],
                $row['fornecedor_id']
            );
        }
        return $produtos;
    }

    public function atualizarQuantidade($id, $quantidade) {
        $query = 'UPDATE ' . $this->table_name . ' SET quantidade = :quantidade WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id',         $id,         PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
