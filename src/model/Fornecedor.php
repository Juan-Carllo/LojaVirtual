<?php
class Fornecedor {
    private $id;
    private $nome;
    private $cnpj;

    public function __construct($nome = "", $cnpj = "", $id = null) {
        $this->nome = $nome;
        $this->cnpj = $cnpj;
        $this->id = $id;
    }

    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getCnpj() { return $this->cnpj; }

    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setCnpj($cnpj) { $this->cnpj = $cnpj; }
}
?>
