<?php

//TODO ADICIONAR METODOS PRA IMAGEM, E INSERÇÃO NO FORM

require_once '/var/www/html/dao/DAO.php';
require_once '/var/www/html/dao/ProdutoDao.php';
require_once '/var/www/html/model/Produto.php';

class PostgresProdutoDao extends DAO implements ProdutoDao {

    /**
     * Nome da tabela no banco de dados
     */
    private $table_name = 'produto';

    /**
     * Insere um novo produto e retorna o ID ou -1 em caso de erro.
     */
    public function insere($produto) {
        $query = 'INSERT INTO ' . $this->table_name .
                 ' (nome, preco, quantidade, fornecedor_id) VALUES ' .
                 '(:nome, :preco, :quantidade, :fornecedor_id)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome());
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade());
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId());

        return $stmt->execute()
            ? $this->conn->lastInsertId()
            : -1;
    }

    /**
     * Remove um produto pelo ID.
     */
    public function remove($produto) {
        $query = 'DELETE FROM ' . $this->table_name . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $produto->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Altera um produto
     */
    public function altera($produto) {
        $query = 'UPDATE ' . $this->table_name .
                 ' SET nome = :nome, preco = :preco, quantidade = :quantidade, ' .
                 'fornecedor_id = :fornecedor_id WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome());
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade());
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId());
        $stmt->bindValue(':id',            $produto->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }


    /**
     * Busca um produto pelo ID
     */
    public function buscaPorId($id) {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new Produto(
            $row['nome'],
            (float)$row['preco'],
            (int)$row['quantidade'],
            (int)$row['fornecedor_id'],
            (int)$row['id']
        );
    }

    /**
     * Busca produtos com nome semelhante ao filtro
     */
    public function buscaPorNome($nome) {
        $query = 'SELECT * FROM ' . $this->table_name .
                 ' WHERE nome ILIKE :nome ORDER BY nome';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome', "%{$nome}%", PDO::PARAM_STR);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Produto(
                $row['nome'],
                (float)$row['preco'],
                (int)$row['quantidade'],
                (int)$row['fornecedor_id'],
                (int)$row['id']
            );
        }
        return $result;
    }

    /**
     * Busca todos os produtos
     */
    public function buscaTodos() {
        $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY nome';
        $stmt = $this->conn->query($query);

        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = new Produto(
                $row['nome'],
                (float)$row['preco'],
                (int)$row['quantidade'],
                (int)$row['fornecedor_id'],
                (int)$row['id']
            );
        }
        return $produtos;
    }

    /**
     * Atualiza a quantidade do produto pelo ID 
     */
    public function atualizarQuantidade($id, $quantidade) {
        $query = 'UPDATE ' . $this->table_name . ' SET quantidade = :quantidade WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id',         $id,         PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
