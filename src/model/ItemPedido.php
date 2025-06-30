<?php
// model/ItemPedido.php

class ItemPedido {
    private $id;
    private $pedidoId;
    private $produtoId;
    private $quantidade;
    private $preco;    // preço unitário no momento do pedido

    /**
     * @param int|null $id
     * @param int      $pedidoId
     * @param int      $produtoId
     * @param int      $quantidade
     * @param float    $preco
     */
    public function __construct(
        $pedidoId = null,
        $produtoId = null,
        $quantidade = 0,
        $preco = 0.0,
        $id = null
    ) {
        $this->id         = $id;
        $this->pedidoId   = $pedidoId;
        $this->produtoId  = $produtoId;
        $this->quantidade = $quantidade;
        $this->preco      = $preco;
    }

    // Getters
    public function getId()         { return $this->id; }
    public function getPedidoId()   { return $this->pedidoId; }
    public function getProdutoId()  { return $this->produtoId; }
    public function getQuantidade() { return $this->quantidade; }
    public function getPreco()      { return $this->preco; }

    // Setters
    public function setId($id)                 { $this->id = $id; }
    public function setPedidoId($pedidoId)     { $this->pedidoId = $pedidoId; }
    public function setProdutoId($produtoId)   { $this->produtoId = $produtoId; }
    public function setQuantidade($quantidade) { $this->quantidade = $quantidade; }
    public function setPreco($preco)           { $this->preco = $preco; }
}
