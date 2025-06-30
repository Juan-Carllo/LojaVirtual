<?php
interface UsuarioDao {
    public function insere(Usuario $usuario);
    public function remove(Usuario $usuario);
    public function altera(Usuario $usuario);
    public function buscaPorId(int $id): ?Usuario;
    public function buscaPorLogin(string $login): ?Usuario;
    public function buscaTodos(): array;

    // PAGINAÇÃO
    /**
     * Retorna uma “página” de usuários.
     * @param int $limit
     * @param int $offset
     * @return Usuario[]
     */
    public function buscaPagina(int $limit, int $offset): array;

    /**
     * Conta o total de usuários.
     * @return int
     */
    public function contaTodos(): int;
}
