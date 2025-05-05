<?php
// src/dao/Fornecedor.php

class Fornecedor {
    private $id;
    private $nome;
    private $cnpj;

    /**
     * @param int|null $id
     * @param string $nome
     * @param string $cnpj
     */
    public function __construct($id = null, $nome = "", $cnpj = "") {
        $this->id   = $id;
        $this->nome = $nome;
        $this->cnpj = $cnpj;
    }

    public function getId() {
        return $this->id;
    }
    public function getNome() {
        return $this->nome;
    }
    public function getCnpj() {
        return $this->cnpj;
    }

    public function setId($id) {
        $this->id = $id;
    }
    public function setNome($nome) {
        $this->nome = $nome;
    }
    public function setCnpj($cnpj) {
        $this->cnpj = $cnpj;
    }
}
