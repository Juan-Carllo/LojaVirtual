<?php
// src/dao/postgres/PostgresItemPedidoDao.php

require_once __DIR__.'/../DAO.php';
require_once __DIR__ . '/../ItemPedidoDao.php';
require_once __DIR__ . '/../../model/ItemPedido.php';

class PostgresItemPedidoDao extends DAO implements ItemPedidoDao
{
    /** @var string */
    private string $table = 'item_pedido';

    /**
     * Insere um novo item de pedido e retorna seu ID, ou -1 em caso de erro.
     */
    public function insere(ItemPedido $item): int
    {
        $sql = "INSERT INTO {$this->table}
                    (pedido_id, produto_id, quantidade, preco)
                VALUES
                    (:pid, :prid, :q, :pre)
                RETURNING id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':pid',  $item->getPedidoId(),   PDO::PARAM_INT);
        $stmt->bindValue(':prid', $item->getProdutoId(),  PDO::PARAM_INT);
        $stmt->bindValue(':q',    $item->getQuantidade(), PDO::PARAM_INT);
        $stmt->bindValue(':pre',  $item->getPreco());
        if (!$stmt->execute()) {
            return -1;
        }
        return (int)$stmt->fetchColumn();
    }

    /**
     * Remove um item de pedido.
     */
    public function remove(ItemPedido $item): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $item->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Altera um item de pedido existente.
     */
    public function altera(ItemPedido $item): bool
    {
        $sql = "UPDATE {$this->table}
                   SET quantidade = :q,
                       preco      = :pre
                 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':q',   $item->getQuantidade(), PDO::PARAM_INT);
        $stmt->bindValue(':pre', $item->getPreco());
        $stmt->bindValue(':id',  $item->getId(),         PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca um item de pedido pelo seu ID.
     */
    public function buscaPorId(int $id): ?ItemPedido
    {
        $sql = "SELECT id, pedido_id, produto_id, quantidade, preco
                  FROM {$this->table}
                 WHERE id = :id
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new ItemPedido(
            (int)$row['pedido_id'],
            (int)$row['produto_id'],
            (int)$row['quantidade'],
            (float)$row['preco'],
            (int)$row['id']
        );
    }

    /**
     * Busca todos os itens de um pedido específico.
     *
     * @return ItemPedido[]
     */
    public function buscaPorPedidoId(int $pedidoId): array
    {
        $sql = "SELECT id, pedido_id, produto_id, quantidade, preco
                  FROM {$this->table}
                 WHERE pedido_id = :pid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':pid', $pedidoId, PDO::PARAM_INT);
        $stmt->execute();

        $itens = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $itens[] = new ItemPedido(
                (int)$row['pedido_id'],
                (int)$row['produto_id'],
                (int)$row['quantidade'],
                (float)$row['preco'],
                (int)$row['id']
            );
        }
        return $itens;
    }

    /**
     * Busca todos os itens de pedido cadastrados.
     *
     * @return ItemPedido[]
     */
    public function buscaTodos(): array
    {
        $sql = "SELECT id, pedido_id, produto_id, quantidade, preco
                  FROM {$this->table}
              ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $itens = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $itens[] = new ItemPedido(
                (int)$row['pedido_id'],
                (int)$row['produto_id'],
                (int)$row['quantidade'],
                (float)$row['preco'],
                (int)$row['id']
            );
        }
        return $itens;
    }
}
