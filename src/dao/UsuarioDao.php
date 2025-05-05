<?php

interface UsuarioDao {

    public function insere($usuario, $endereco);
    public function remove($usuario);
    public function altera($usuario, $endereco);
    public function buscaPorId($id);
    public function buscaPorLogin($login);
    public function buscaTodos();
}
?>
