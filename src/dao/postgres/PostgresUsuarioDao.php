<?php
// src/dao/postgres/PostgresUsuarioDao.php

require_once __DIR__ . '/../DAO.php';

// 2) interface que implementa
require_once __DIR__ . '/../UsuarioDao.php';

// 3) modelos de domínio
require_once __DIR__ . '/../../model/Usuario.php';
require_once __DIR__ . '/../../model/Endereco.php';
class PostgresUsuarioDao extends DAO implements UsuarioDao
{
    /**
     * Nome da tabela no banco de dados
     */
    private $table_name = 'usuario';

    /**
     * Insere um novo usuário (com endereço em $usuario->getEndereco())
     * Retorna o ID inserido ou -1 em caso de erro.
     */
    public function insere(Usuario $usuario)
    {
        $endereco = $usuario->getEndereco();

        try {
            $this->conn->beginTransaction();

            // 1) Inserir endereço
            $sqlEnd = "
                INSERT INTO endereco
                  (rua, numero, complemento, bairro, cep, cidade, estado)
                VALUES
                  (:rua, :numero, :complemento, :bairro, :cep, :cidade, :estado)
                RETURNING id
            ";
            $stmtEnd = $this->conn->prepare($sqlEnd);
            $stmtEnd->bindValue(':rua',         $endereco->getRua());
            $stmtEnd->bindValue(':numero',      $endereco->getNumero());
            $stmtEnd->bindValue(':complemento', $endereco->getComplemento());
            $stmtEnd->bindValue(':bairro',      $endereco->getBairro());
            $stmtEnd->bindValue(':cep',         $endereco->getCep());
            $stmtEnd->bindValue(':cidade',      $endereco->getCidade());
            $stmtEnd->bindValue(':estado',      $endereco->getEstado());
            if (! $stmtEnd->execute()) {
                $this->conn->rollBack();
                return -1;
            }
            $enderecoId = $stmtEnd->fetchColumn();

            // 2) Inserir usuário
            $sqlUser = "
                INSERT INTO {$this->table_name}
                  (login, senha, nome, tipo, endereco_id)
                VALUES
                  (:login, :senha, :nome, :tipo, :endereco_id)
            ";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->bindValue(':login',      $usuario->getLogin());
            $stmtUser->bindValue(':senha',      $usuario->getSenha());
            $stmtUser->bindValue(':nome',       $usuario->getNome());
            $stmtUser->bindValue(':tipo',       $usuario->getTipo());
            $stmtUser->bindValue(':endereco_id',$enderecoId, PDO::PARAM_INT);
            if (! $stmtUser->execute()) {
                $this->conn->rollBack();
                return -1;
            }

            $usuarioId = $this->conn->lastInsertId();
            $this->conn->commit();
            return $usuarioId;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Remove um usuário pelo ID.
     */
    public function remove(Usuario $usuario)
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $usuario->getId(), PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Altera um usuário (e seu endereço) e retorna true em caso de sucesso.
     */
    public function altera(Usuario $usuario)
    {
        $endereco = $usuario->getEndereco();

        try {
            $this->conn->beginTransaction();

            // 1) Atualizar usuário
            $sqlUser = "
                UPDATE {$this->table_name}
                   SET login = :login,
                       senha = :senha,
                       nome  = :nome,
                       tipo  = :tipo
                 WHERE id = :id
            ";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->bindValue(':login', $usuario->getLogin());
            $stmtUser->bindValue(':senha', $usuario->getSenha());
            $stmtUser->bindValue(':nome',  $usuario->getNome());
            $stmtUser->bindValue(':tipo',  $usuario->getTipo());
            $stmtUser->bindValue(':id',    $usuario->getId(), PDO::PARAM_INT);
            if (! $stmtUser->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // 2) Atualizar endereço
            $sqlEnd = "
                UPDATE endereco
                   SET rua         = :rua,
                       numero      = :numero,
                       complemento = :complemento,
                       bairro      = :bairro,
                       cep         = :cep,
                       cidade      = :cidade,
                       estado      = :estado
                 WHERE id = (
                   SELECT endereco_id FROM {$this->table_name} WHERE id = :id
                 )
            ";
            $stmtEnd = $this->conn->prepare($sqlEnd);
            $stmtEnd->bindValue(':rua',         $endereco->getRua());
            $stmtEnd->bindValue(':numero',      $endereco->getNumero());
            $stmtEnd->bindValue(':complemento', $endereco->getComplemento());
            $stmtEnd->bindValue(':bairro',      $endereco->getBairro());
            $stmtEnd->bindValue(':cep',         $endereco->getCep());
            $stmtEnd->bindValue(':cidade',      $endereco->getCidade());
            $stmtEnd->bindValue(':estado',      $endereco->getEstado());
            $stmtEnd->bindValue(':id',          $usuario->getId(), PDO::PARAM_INT);
            if (! $stmtEnd->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Busca usuário por ID; retorna null se não encontrar.
     */
    public function buscaPorId(int $id): ?Usuario
    {
        $sql = "
            SELECT u.id, u.login, u.senha, u.nome, u.tipo,
                   e.id   AS endereco_id,
                   e.rua, e.numero, e.complemento,
                   e.bairro, e.cep, e.cidade, e.estado
              FROM {$this->table_name} u
         LEFT JOIN endereco e ON u.endereco_id = e.id
             WHERE u.id = :id
             LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $row) {
            return null;
        }

        $end = new Endereco(
            (int)$row['endereco_id'],
            $row['rua'],
            $row['numero'],
            $row['complemento'],
            $row['bairro'],
            $row['cep'],
            $row['cidade'],
            $row['estado']
        );
        return new Usuario(
            (int)$row['id'],
            $row['login'],
            $row['senha'],
            $row['nome'],
            $row['tipo'],
            $end
        );
    }

    /**
     * Busca usuário por login; retorna null se não encontrar.
     */
    public function buscaPorLogin(string $login): ?Usuario
    {
        $sql = "
            SELECT id, login, senha, nome, tipo
              FROM {$this->table_name}
             WHERE login = :login
             LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        if (! $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }
        return new Usuario(
            (int)$row['id'],
            $row['login'],
            $row['senha'],
            $row['nome'],
            $row['tipo'],
            null
        );
    }

    /**
     * Retorna todos os usuários.
     * @return Usuario[]
     */
    public function buscaTodos(): array
    {
        $sql = "
            SELECT u.id, u.login, u.senha, u.nome, u.tipo,
                   e.id   AS endereco_id,
                   e.rua, e.numero, e.complemento,
                   e.bairro, e.cep, e.cidade, e.estado
              FROM {$this->table_name} u
         LEFT JOIN endereco e ON u.endereco_id = e.id
          ORDER BY u.id ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $end = new Endereco(
                (int)$row['endereco_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
            $out[] = new Usuario(
                (int)$row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo'],
                $end
            );
        }
        return $out;
    }

    /**
     * Retorna uma “página” de usuários.
     * @param int $limit
     * @param int $offset
     * @return Usuario[]
     */
    public function buscaPagina(int $limit, int $offset): array
    {
        $sql = "
            SELECT u.id, u.login, u.senha, u.nome, u.tipo,
                   e.id   AS endereco_id,
                   e.rua, e.numero, e.complemento,
                   e.bairro, e.cep, e.cidade, e.estado
              FROM {$this->table_name} u
         LEFT JOIN endereco e ON u.endereco_id = e.id
          ORDER BY u.id ASC
             LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $end = new Endereco(
                (int)$row['endereco_id'],
                $row['rua'],
                $row['numero'],
                $row['complemento'],
                $row['bairro'],
                $row['cep'],
                $row['cidade'],
                $row['estado']
            );
            $out[] = new Usuario(
                (int)$row['id'],
                $row['login'],
                $row['senha'],
                $row['nome'],
                $row['tipo'],
                $end
            );
        }
        return $out;
    }

    /**
     * Conta o total de usuários.
     * @return int
     */
    public function contaTodos(): int
    {
        return (int)$this->conn
            ->query("SELECT COUNT(*) FROM {$this->table_name}")
            ->fetchColumn();
    }
}
