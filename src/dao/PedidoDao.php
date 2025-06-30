<?php

require_once __DIR__ . '/../fachada.php';

interface PedidoDao
{
    /**
     * Insere um novo pedido e retorna seu ID, ou -1 em caso de erro.
     *
     * @param Pedido $pedido
     * @return int
     */
    public function insere(Pedido $pedido): int;

    /**
     * Insere um item de pedido.
     *
     * @param ItemPedido $item
     * @return bool
     */
    public function insereItem(ItemPedido $item): bool;

    /**
     * Busca um pedido pelo ID (cabeçalho).
     *
     * @param int $id
     * @return Pedido|null
     */
    public function buscaPorId(int $id): ?Pedido;

    /**
     * Busca todos os itens de um pedido.
     *
     * @param int $pedidoId
     * @return ItemPedido[]
     */
    public function buscaItens(int $pedidoId): array;

    /**
     * Atualiza a situação de um pedido (e define data de entrega se for 'ENTREGUE').
     *
     * @param int    $pedidoId
     * @param string $situacao
     * @return bool
     */
    public function atualizaSituacao(int $pedidoId, string $situacao): bool;

    //--------------- paginação ---------------

    /**
     * Retorna uma “página” de pedidos, para listagem mestre.
     *
     * @param int $limit
     * @param int $offset
     * @return Pedido[]
     */
    public function buscaPagina(int $limit, int $offset): array;

    /**
     * Conta todos os pedidos (para calcular total de páginas).
     *
     * @return int
     */
    public function contaTodos(): int;

    /**
     * (Opcional) Retorna todos os pedidos sem paginação.
     *
     * @return Pedido[]
     */
    public function buscaTodos(): array;
}
