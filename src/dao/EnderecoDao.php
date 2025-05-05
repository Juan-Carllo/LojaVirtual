<?php
// src/dao/EnderecoDao.php

interface EnderecoDao {
    /**
     * Insere um novo endereço e retorna seu ID ou -1 em caso de falha
     * @param Endereco $endereco
     * @return int
     */
    public function insere($endereco);
    public function remove($endereco);
    public function altera($endereco);
    public function buscaPorId($id);
    public function buscaTodos();
}
?>
