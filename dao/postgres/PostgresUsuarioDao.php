<?php

include_once('C:\wamp64\www\projeto_loja\dao\UsuarioDao.php.php');
include_once('dao\DAO.php');

class PostgresUsuarioDao extends DAO implements UsuarioDao {

    private $table_name = 'usuario';
    
    public function insere($usuario) {

      
    
        $query = "INSERT INTO " . $this->table_name . 
        " (login, senha, nome) VALUES" .
        " (:login, :senha, :nome)";
    
        $stmt = $this->conn->prepare($query);
    
        // bind values 
        $stmt->bindParam(":login", $usuario->getLogin());
        $stmt->bindParam(":senha", $usuario->getSenha()); // Armazena a senha hash
        $stmt->bindParam(":nome", $usuario->getNome());
    
        if($stmt->execute()){
            return $this->conn->lastInsertId();
        } else {
            return -1;
        }
    }
    

    public function remove($usuario) {
        $query = "DELETE FROM " . $this->table_name . 
        " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // bind parameters
        $stmt->bindParam(':id', $usuario->getId());

        // execute the query
        if($stmt->execute()){
            return true;
        }    

        return false;
    }

    public function altera($usuario) {

       
    
        $query = "UPDATE " . $this->table_name . 
        " SET login = :login, senha = :senha, nome = :nome" .
        " WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        // bind parameters
        $stmt->bindParam(":login", $usuario->getLogin());
        $stmt->bindParam(":senha", $usuario->getSenha()); // Armazena a senha hash
        $stmt->bindParam(":nome", $usuario->getNome());
        $stmt->bindParam(':id', $usuario->getId());
    
        // execute the query
        if($stmt->execute()){
            return true;
        }    
    
        return false;
    }
    

    public function buscaPorId($id) {
        
        $usuario = null;

        $query = "SELECT
                    id, login, nome, senha
                FROM
                    " . $this->table_name . "
                WHERE
                    id = ?
                LIMIT
                    1 OFFSET 0";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $id);
        $stmt->execute();
     
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $usuario = new Usuario($row['id'],$row['login'], $row['senha'], $row['nome']);
        } 
     
        return $usuario;
    }

    public function buscaPorLogin($login) {

        $usuario = null;

        $query = "SELECT
                    id, login, nome, senha
                FROM
                    " . $this->table_name . "
                WHERE
                    login = ?
                LIMIT
                    1 OFFSET 0";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $login);
        $stmt->execute();
     
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $usuario = new Usuario($row['id'],$row['login'], $row['senha'], $row['nome']);
        } 
     
        return $usuario;
    }

    public function buscaTodos() {

        $query = "SELECT
                    id, login, senha, nome
                FROM
                    " . $this->table_name . 
                    " ORDER BY id ASC";
     
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            extract($row);
            $usuario = new Usuario($id,$login,$senha,$nome); 
            $usuarios[] = $usuario;
        }
        return $usuarios;
    }
}
?>