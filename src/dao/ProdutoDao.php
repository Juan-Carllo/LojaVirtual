<?php
// src/dao/ProdutoDao.php

interface ProdutoDao {
    public function insere(Produto $produto): int;
    public function remove(Produto $produto): bool;
    public function altera(Produto $produto): bool;
    public function buscaPorId(int $id): ?Produto;
    public function buscaPorNome(string $nome): array;
    public function buscaTodos(): array;

    // já existentes para paginação, se você os tiver implementado:
    public function buscaPagina(int $limit, int $offset): array;
    public function contaTodos(): int;

    /**
     * Atualiza a quantidade do produto pelo ID.
     *
     * @param int $id
     * @param int $quantidade
     * @return bool
     */
    public function atualizarQuantidade(int $id, int $quantidade): bool;
}
