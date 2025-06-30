<?php
// src/dao/postgres/PostgresDaoFactory.php

// preciso da definição de DaoFactory
require_once __DIR__ . '/../DaoFactory.php';

require_once __DIR__ . '/PostgresUsuarioDao.php';
require_once __DIR__ . '/PostgresFornecedorDao.php';
require_once __DIR__ . '/PostgresProdutoDao.php';
require_once __DIR__ . '/PostgresPedidoDao.php';
require_once __DIR__ . '/PostgresItemPedidoDao.php';

class PostgresDaoFactory extends DaoFactory
{
    private string $host     = 'postgres-db';
    private string $db_name  = 'lojavirtual';
    private string $port     = '5432';
    private string $username = 'amigosdocasa';
    private string $password = 'senhasuperdificil';
    private ?PDO   $conn     = null;

    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->conn;
    }

    public function getUsuarioDao(): UsuarioDao
    {
        return new PostgresUsuarioDao($this->getConnection());
    }

    public function getFornecedorDao(): FornecedorDao
    {
        return new PostgresFornecedorDao($this->getConnection());
    }

    public function getProdutoDao(): ProdutoDao
    {
        return new PostgresProdutoDao($this->getConnection());
    }

    public function getPedidoDao(): PedidoDao
    {
        return new PostgresPedidoDao($this->getConnection());
    }

    public function getItemPedidoDao(): ItemPedidoDao
    {
        return new PostgresItemPedidoDao($this->getConnection());
    }
}
