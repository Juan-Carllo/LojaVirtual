<?php
// model/Pedido.php

class Pedido {
    private $id;
    private $usuarioId;
    private $dataPedido;
    private $dataEntrega;
    private $situacao;

    /** @var ItemPedido[] */
    private $itens = [];   // ← coleção de itens

    public function __construct(
        $usuarioId = null,
        $dataPedido = "",
        $dataEntrega = null,
        $situacao = "NOVO",
        $id = null
    ) {
        $this->id          = $id;
        $this->usuarioId   = $usuarioId;
        $this->dataPedido  = $dataPedido;
        $this->dataEntrega = $dataEntrega;
        $this->situacao    = $situacao;
    }

    // Getters
    public function getId()         { return $this->id; }
    public function getUsuarioId()  { return $this->usuarioId; }
    public function getDataPedido() { return $this->dataPedido; }
    public function getDataEntrega(){ return $this->dataEntrega; }
    public function getSituacao()   { return $this->situacao; }

    // Setters
    public function setId($id)                   { $this->id = $id; }
    public function setUsuarioId($usuarioId)     { $this->usuarioId = $usuarioId; }
    public function setDataPedido($dataPedido)   { $this->dataPedido = $dataPedido; }
    public function setDataEntrega($dataEntrega) { $this->dataEntrega = $dataEntrega; }
    public function setSituacao($situacao)       { $this->situacao = $situacao; }
}
