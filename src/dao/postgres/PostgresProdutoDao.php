<?php


require_once __DIR__ . '/../DAO.php';
require_once __DIR__ . '/../ProdutoDao.php';
require_once __DIR__ . '/../../model/Produto.php';
class PostgresProdutoDao extends DAO implements ProdutoDao {

    /**
     * Nome da tabela no banco de dados
     */
    private string $table_name = 'produto';

    /**
     * Insere um novo produto e retorna o ID ou -1 em caso de erro.
     */
    public function insere(Produto $produto): int {
        $sql = "INSERT INTO {$this->table_name}
                    (nome, preco, quantidade, fornecedor_id, imagem)
                VALUES
                    (:nome, :preco, :quantidade, :fornecedor_id, :imagem)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome',          $produto->getNome(),            PDO::PARAM_STR);
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade(),      PDO::PARAM_INT);
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(),    PDO::PARAM_INT);
        $stmt->bindValue(':imagem',        $produto->getImagem(),          PDO::PARAM_LOB);

        return $stmt->execute()
            ? (int)$this->conn->lastInsertId()
            : -1;
    }

    /**
     * Remove um produto pelo ID.
     */
    public function remove(Produto $produto): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table_name} WHERE id = :id"
        );
        $stmt->bindValue(':id', $produto->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Altera um produto existente, incluindo imagem.
     */
    public function altera(Produto $produto): bool {
        $sql = "UPDATE {$this->table_name} SET
                    nome          = :nome,
                    preco         = :preco,
                    quantidade    = :quantidade,
                    fornecedor_id = :fornecedor_id,
                    imagem        = :imagem
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome',          $produto->getNome(),         PDO::PARAM_STR);
        $stmt->bindValue(':preco',         $produto->getPreco());
        $stmt->bindValue(':quantidade',    $produto->getQuantidade(),   PDO::PARAM_INT);
        $stmt->bindValue(':fornecedor_id', $produto->getFornecedorId(), PDO::PARAM_INT);
        $stmt->bindValue(':imagem',        $produto->getImagem(),       PDO::PARAM_LOB);
        $stmt->bindValue(':id',            $produto->getId(),           PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca um produto pelo ID, retornando também imagem (string binária).
     */
    public function buscaPorId(int $id): ?Produto {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, preco, quantidade, fornecedor_id, imagem
               FROM {$this->table_name}
              WHERE id = :id
              LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        if (is_resource($row['imagem'])) {
            $row['imagem'] = stream_get_contents($row['imagem']);
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
     * Busca todos os produtos, incluindo imagens.
     */
    public function buscaTodos(): array {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, preco, quantidade, fornecedor_id, imagem
               FROM {$this->table_name}
           ORDER BY nome"
        );
        $stmt->execute();
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_resource($row['imagem'])) {
                $row['imagem'] = stream_get_contents($row['imagem']);
            }
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
     * Busca produtos com nome semelhante ao filtro, incluindo imagens.
     */
    public function buscaPorNome(string $nome): array {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, preco, quantidade, fornecedor_id, imagem
               FROM {$this->table_name}
              WHERE nome ILIKE :nome
           ORDER BY nome"
        );
        $stmt->bindValue(':nome', "%{$nome}%", PDO::PARAM_STR);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_resource($row['imagem'])) {
                $row['imagem'] = stream_get_contents($row['imagem']);
            }
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
     * Atualiza a quantidade do produto pelo ID.
     */
    public function atualizarQuantidade(int $id, int $quantidade): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table_name}
                SET quantidade = :quantidade
              WHERE id = :id"
        );
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id',         $id,         PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Retorna uma “página” de produtos, incluindo imagens.
     */
    public function buscaPagina(int $limit, int $offset): array {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, preco, quantidade, fornecedor_id, imagem
               FROM {$this->table_name}
           ORDER BY nome
              LIMIT :limit
             OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_resource($row['imagem'])) {
                $row['imagem'] = stream_get_contents($row['imagem']);
            }
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
     * Conta quantos produtos existem (para cálculo de páginas).
     */
    public function contaTodos(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}
