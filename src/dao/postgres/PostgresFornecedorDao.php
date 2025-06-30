<?php
// dao/postgres/PostgresFornecedorDao.php

include_once '/var/www/html/dao/DAO.php';
include_once '/var/www/html/dao/FornecedorDao.php';

class PostgresFornecedorDao extends DAO implements FornecedorDao {

    /**
     * Nome da tabela no banco de dados
     */
    private string $table_name = 'fornecedores';

    /**
     * Insere um novo fornecedor e retorna o ID ou -1 em caso de erro.
     */
    public function insere(Fornecedor $fornecedor): int {
        $sql = "INSERT INTO {$this->table_name} (nome, cnpj) VALUES (:nome, :cnpj)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $fornecedor->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':cnpj', $fornecedor->getCnpj(), PDO::PARAM_STR);

        return $stmt->execute()
            ? (int)$this->conn->lastInsertId()
            : -1;
    }

    /**
     * Remove um fornecedor pelo ID.
     */
    public function remove(Fornecedor $fornecedor): bool {
        // Verifica se há produtos vinculados ao fornecedor
        $verificaSql = "SELECT COUNT(*) FROM produto WHERE fornecedor_id = :id";
        $verificaStmt = $this->conn->prepare($verificaSql);
        $verificaStmt->bindValue(':id', $fornecedor->getId(), PDO::PARAM_INT);
        $verificaStmt->execute();
        $totalProdutos = (int)$verificaStmt->fetchColumn();

        if ($totalProdutos > 0) {
            // Não remove se houver produtos relacionados
            return false;
        }

        // Remove normalmente
        $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $fornecedor->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Atualiza nome e CNPJ de um fornecedor existente.
     */
    public function altera(Fornecedor $fornecedor): bool {
        $sql = "UPDATE {$this->table_name}
                   SET nome = :nome,
                       cnpj = :cnpj
                 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $fornecedor->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':cnpj', $fornecedor->getCnpj(), PDO::PARAM_STR);
        $stmt->bindValue(':id',   $fornecedor->getId(),   PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca um fornecedor pelo ID.
     */
    public function buscaPorId(int $id): ?Fornecedor {
        $sql = "SELECT id, nome, cnpj
                  FROM {$this->table_name}
                 WHERE id = :id
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? new Fornecedor((int)$row['id'], $row['nome'], $row['cnpj'])
            : null;
    }

    /**
     * Busca fornecedores cujo nome contém o termo informado.
     */
    public function buscaPorNome(string $nome): array {
        $sql = "SELECT id, nome, cnpj
                  FROM {$this->table_name}
                 WHERE nome ILIKE :nome
              ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', "%{$nome}%", PDO::PARAM_STR);
        $stmt->execute();

        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor(
                (int)$row['id'],
                $row['nome'],
                $row['cnpj']
            );
        }
        return $fornecedores;
    }

    /**
     * Retorna todos os fornecedores.
     */
    public function buscaTodos(): array {
        $sql = "SELECT id, nome, cnpj
                  FROM {$this->table_name}
              ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor(
                (int)$row['id'],
                $row['nome'],
                $row['cnpj']
            );
        }
        return $fornecedores;
    }

    /**
     * Retorna uma “página” de fornecedores.
     */
    public function buscaPagina(int $limit, int $offset): array {
        $sql = "SELECT id, nome, cnpj
                  FROM {$this->table_name}
              ORDER BY nome
              LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $fornecedores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fornecedores[] = new Fornecedor(
                (int)$row['id'],
                $row['nome'],
                $row['cnpj']
            );
        }
        return $fornecedores;
    }

    /**
     * Conta o total de fornecedores.
     */
    public function contaTodos(): int {
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}
