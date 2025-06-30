<?php
// src/dao/ItemPedidoDao.php

interface ItemPedidoDao {
    /**
     * Insere um novo item de pedido e retorna seu ID, ou -1 em caso de erro
     * @param ItemPedido $item
     * @return int
     */
    public function insere(ItemPedido $item): int;

    /**
     * Remove um item de pedido
     * @param ItemPedido $item
     * @return bool
     */
    public function remove(ItemPedido $item): bool;

    /**
     * Altera um item de pedido existente
     * @param ItemPedido $item
     * @return bool
     */
    public function altera(ItemPedido $item): bool;

    /**
     * Busca um item de pedido pelo seu ID
     * @param int $id
     * @return ItemPedido|null
     */
    public function buscaPorId(int $id): ?ItemPedido;

    /**
     * Busca todos os itens de um pedido específico
     * @param int $pedidoId
     * @return ItemPedido[]
     */
    public function buscaPorPedidoId(int $pedidoId): array;

    /**
     * Retorna todos os itens de pedido cadastrados
     * @return ItemPedido[]
     */
    public function buscaTodos(): array;
}
