<?php
interface FornecedorDao {
    public function insere(Fornecedor $fornecedor);
    public function remove(Fornecedor $fornecedor);
    public function altera(Fornecedor $fornecedor);
    public function buscaPorId(int $id): ?Fornecedor;
    public function buscaTodos(): array;

    // PAGINAÇÃO
    /**
     * Retorna uma “página” de fornecedores.
     * @param int $limit
     * @param int $offset
     * @return Fornecedor[]
     */
    public function buscaPagina(int $limit, int $offset): array;

    /**
     * Conta o total de fornecedores.
     * @return int
     */
    public function contaTodos(): int;
}
