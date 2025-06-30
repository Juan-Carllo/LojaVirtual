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
                 ' (nome, preco, quantidade, fornecedor_id, imagem) VALUES ' .
                 '(:nome, :preco, :quantidade, :fornecedor_id, :imagem)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome(),         PDO::PARAM_STR);
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade(),   PDO::PARAM_INT);
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(), PDO::PARAM_INT);
        $stmt->bindValue(':imagem',        $produto->getImagem(),       PDO::PARAM_LOB);

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
     * Altera um produto existente, incluindo imagem.
     */
    public function altera($produto) {
        $query = 'UPDATE ' . $this->table_name . ' SET
                      nome          = :nome,
                      preco         = :preco,
                      quantidade    = :quantidade,
                      fornecedor_id = :fornecedor_id,
                      imagem        = :imagem
                  WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nome',          $produto->getNome(),         PDO::PARAM_STR);
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade(),   PDO::PARAM_INT);
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(), PDO::PARAM_INT);
        $stmt->bindValue(':imagem',        $produto->getImagem(),       PDO::PARAM_LOB);
        $stmt->bindValue(':id',            $produto->getId(),           PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Busca um produto pelo ID, retornando também imagem.
     */
    public function buscaPorId($id) {
        $query = 'SELECT id, nome, preco, quantidade, fornecedor_id, imagem
                    FROM ' . $this->table_name . ' WHERE id = :id LIMIT 1';
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
            (int)$row['id'],
            $row['imagem']
        );
    }

    /**
     * Busca produtos com nome semelhante ao filtro.
     */
    public function buscaPorNome($nome) {
        $query = 'SELECT id, nome, preco, quantidade, fornecedor_id, imagem
                    FROM ' . $this->table_name .
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
                (int)$row['id'],
                $row['imagem']
            );
        }
        return $result;
    }

    /**
     * Busca todos os produtos, incluindo imagens.
     */
    public function buscaTodos() {
        $query = 'SELECT id, nome, preco, quantidade, fornecedor_id, imagem
                    FROM ' . $this->table_name . ' ORDER BY nome';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = new Produto(
                $row['nome'],
                (float)$row['preco'],
                (int)$row['quantidade'],
                (int)$row['fornecedor_id'],
                (int)$row['id'],
                $row['imagem']
            );
        }
        return $produtos;
    }

    /**
     * Atualiza a quantidade do produto pelo ID.
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
