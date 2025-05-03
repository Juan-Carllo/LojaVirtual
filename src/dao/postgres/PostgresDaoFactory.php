<?php

include_once('../DaoFactory.php');
include_once('../UsuarioDao.php');
include_once('../FornecedorDAO.php');
include_once('../ProdutoDAO.php');
include_once('PostgresUsuarioDao.php');

class PostgresDaoFactory extends DaoFactory {

    private $host = "postgres-db";         // Nome do serviço no docker-compose
    private $db_name = "lojavirtual";      // Nome do banco de dados
    private $port = "5432";                // Porta padrão do PostgreSQL
    private $username = "amigosdocasa";         // Usuário definido no docker-compose
    private $password = "senhasuperdificil";        // Senha definida no docker-compose
    private $conn;

    // Estabelece conexão com o banco de dados
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function getUsuarioDao() {
        return new PostgresUsuarioDao($this->getConnection());
    }

    public function getFornecedorDao() {
        return new PostgresFornecedorDao($this->getConnection());
    }

    public function getProdutoDao() {
        return new PostgresProdutoDao($this->getConnection());
    }
}