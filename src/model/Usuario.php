<?php
// model/Usuario.php

class Usuario {
    private $id;
    private $login;
    private $senha;
    private $nome;
    private $tipo;
    private $enderecoId;
    private $endereco; // Instância de Endereco

    /**
     * @param int|null       $id
     * @param string         $login
     * @param string         $senha
     * @param string         $nome
     * @param string         $tipo
     * @param Endereco|null  $endereco
     */
    public function __construct($id, $login, $senha, $nome, $tipo, $endereco = null) {
        $this->id = $id;
        $this->login = $login;
        $this->senha = $senha;
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->endereco = $endereco;
        $this->enderecoId = $endereco ? $endereco->getId() : null;
    }

    // getters e setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function getEnderecoId() {
        return $this->enderecoId;
    }

    public function setEnderecoId($enderecoId) {
        $this->enderecoId = $enderecoId;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
        $this->enderecoId = $endereco ? $endereco->getId() : null;
    }
}
?>
