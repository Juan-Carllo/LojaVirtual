<?php
// src/dao/postgres/PostgresPedidoDao.php

require_once __DIR__.'/../DAO.php';
require_once __DIR__.'/../PedidoDao.php';
require_once __DIR__.'/../../model/Pedido.php';
require_once __DIR__.'/../../model/ItemPedido.php';

class PostgresPedidoDao extends DAO implements PedidoDao
{
    private string $pedidoTable = 'pedido';
    private string $itemTable   = 'item_pedido';

    /**
     * Insere um novo pedido e retorna seu ID, ou -1 em caso de erro.
     */
    public function insere(Pedido $pedido): int
    {
        $sql = "INSERT INTO {$this->pedidoTable}
                    (usuario_id, data_pedido, situacao)
                VALUES
                    (:uid, :dp, :sit)
                RETURNING id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':uid', $pedido->getUsuarioId(), PDO::PARAM_INT);
        $stmt->bindValue(':dp',  $pedido->getDataPedido(),  PDO::PARAM_STR);
        $stmt->bindValue(':sit', $pedido->getSituacao(),    PDO::PARAM_STR);

        if (!$stmt->execute()) {
            return -1;
        }
        return (int)$stmt->fetchColumn();
    }

    /**
     * Insere um item de pedido.
     */
    public function insereItem(ItemPedido $item): bool
    {
        $sql = "INSERT INTO {$this->itemTable}
                    (pedido_id, produto_id, quantidade, preco)
                VALUES
                    (:pid, :prid, :q, :pre)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':pid',  $item->getPedidoId(),   PDO::PARAM_INT);
        $stmt->bindValue(':prid', $item->getProdutoId(),  PDO::PARAM_INT);
        $stmt->bindValue(':q',    $item->getQuantidade(), PDO::PARAM_INT);
        $stmt->bindValue(':pre',  $item->getPreco());

        return $stmt->execute();
    }

    /**
     * Busca um pedido pelo ID (cabeçalho).
     */
    public function buscaPorId(int $id): ?Pedido
    {
        $sql = "SELECT id, usuario_id, data_pedido, data_entrega, situacao
                  FROM {$this->pedidoTable}
                 WHERE id = :id
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        return new Pedido(
            (int)$row['usuario_id'],
            $row['data_pedido'],
            $row['data_entrega'] ?? null,
            $row['situacao'],
            (int)$row['id']
        );
    }

    /**
     * Busca todos os itens de um pedido.
     *
     * @return ItemPedido[]
     */
    public function buscaItens(int $pedidoId): array
    {
        $sql = "SELECT id, pedido_id, produto_id, quantidade, preco
                  FROM {$this->itemTable}
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
     * Atualiza a situação de um pedido (e, se for ENTREGUE, define data_entrega).
     */
    public function atualizaSituacao(int $pedidoId, string $situacao): bool
    {
        $sql = "UPDATE {$this->pedidoTable}
                SET situacao     = :sit,
                    data_entrega = CASE WHEN :sit2 = 'ENTREGUE' THEN NOW() ELSE data_entrega END
                WHERE id = :pid";
        $stmt = $this->conn->prepare($sql);

        // bind dois placeholders distintos, mas com o mesmo valor
        $stmt->bindValue(':sit',  $situacao, PDO::PARAM_STR);
        $stmt->bindValue(':sit2', $situacao, PDO::PARAM_STR);
        $stmt->bindValue(':pid',  $pedidoId, PDO::PARAM_INT);

        return $stmt->execute();
    }


    /**
     * Retorna todos os pedidos (sem paginação).
     *
     * @return Pedido[]
     */
    public function buscaTodos(): array
    {
        $sql = "SELECT id, usuario_id, data_pedido, data_entrega, situacao
                  FROM {$this->pedidoTable}
              ORDER BY data_pedido DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $pedidos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pedidos[] = new Pedido(
                (int)$row['usuario_id'],
                $row['data_pedido'],
                $row['data_entrega'] ?? null,
                $row['situacao'],
                (int)$row['id']
            );
        }
        return $pedidos;
    }

    /**
     * Retorna uma “página” de pedidos para listagem Mestre.
     *
     * @return Pedido[]
     */
    public function buscaPagina(int $limit, int $offset): array
    {
        $sql = "SELECT id, usuario_id, data_pedido, data_entrega, situacao
                  FROM {$this->pedidoTable}
              ORDER BY data_pedido DESC
                 LIMIT :lim
                OFFSET :off";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $pedidos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pedidos[] = new Pedido(
                (int)$row['usuario_id'],
                $row['data_pedido'],
                $row['data_entrega'] ?? null,
                $row['situacao'],
                (int)$row['id']
            );
        }
        return $pedidos;
    }

    /**
     * Conta quantos pedidos existem (para paginação).
     */
    public function contaTodos(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->pedidoTable}";
        return (int)$this->conn->query($sql)->fetchColumn();
    }
}
