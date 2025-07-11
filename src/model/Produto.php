<?php
class Produto {
    private $id;
    private $nome;
    private $preco;
    private $quantidade;
    private $fornecedorId;
    private $imagem; // Novo atributo

    // constructor
    public function __construct($nome = "", $preco = 0.0, $quantidade = 0, $fornecedorId = null, $id = null, $imagem = null) {
        $this->nome = $nome;
        $this->preco = $preco;
        $this->quantidade = $quantidade;
        $this->fornecedorId = $fornecedorId;
        $this->id = $id;
        $this->imagem = $imagem;
    }

    // getters e setters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getPreco() { return $this->preco; }
    public function getQuantidade() { return $this->quantidade; }
    public function getFornecedorId() { return $this->fornecedorId; }
    public function getImagem() { return $this->imagem; }

    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setPreco($preco) { $this->preco = $preco; }
    public function setQuantidade($quantidade) { $this->quantidade = $quantidade; }
    public function setFornecedorId($fornecedorId) { $this->fornecedorId = $fornecedorId; }
    public function setImagem($imagem) { $this->imagem = $imagem; }
}
?>
