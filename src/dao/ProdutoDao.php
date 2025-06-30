<?php
interface ProdutoDao {
    public function insere(Produto $produto);
    public function remove(Produto $produto);
    public function altera(Produto $produto);
    public function buscaPorId(int $id): ?Produto;
    public function buscaPorNome(string $nome): array;
    public function buscaTodos(): array;

    // PAGINAÇÃO
    /**
     * Retorna uma “página” de produtos.
     * @param int $limit  — quantos itens por página
     * @param int $offset — quantos itens pular
     * @return Produto[]
     */
    public function buscaPagina(int $limit, int $offset): array;

    /**
     * Conta o total de produtos (para calcular número de páginas).
     * @return int
     */
    public function contaTodos(): int;
}
