<?php
// src/dao/FornecedorDao.php

interface FornecedorDao {
    /**
     * Insere um novo fornecedor e retorna seu ID ou -1 em caso de erro.
     */
    public function insere(Fornecedor $fornecedor): int;

    /**
     * Remove um fornecedor existente, devolve true em caso de sucesso.
     */
    public function remove(Fornecedor $fornecedor): bool;

    /**
     * Altera um fornecedor existente, devolve true em caso de sucesso.
     */
    public function altera(Fornecedor $fornecedor): bool;

    /**
     * Busca um fornecedor pelo ID.
     * @return Fornecedor|null
     */
    public function buscaPorId(int $id): ?Fornecedor;

    /**
     * Retorna todos os fornecedores (sem paginação).
     * @return Fornecedor[]
     */
    public function buscaTodos(): array;

    /**
     * Pesquisa fornecedores cujo nome ou CNPJ contenham o filtro.
     * @param string $filtro
     * @return Fornecedor[]
     */
    public function buscaPorNome(string $filtro): array;

    // ----------------- paginação -----------------

    /**
     * Retorna uma “página” de fornecedores.
     * @param int $limit
     * @param int $offset
     * @return Fornecedor[]
     */
    public function buscaPagina(int $limit, int $offset): array;

    /**
     * Conta o total de fornecedores (para calcular páginas).
     * @return int
     */
    public function contaTodos(): int;
}
