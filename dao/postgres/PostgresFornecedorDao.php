<?php
require_once(__DIR__ . '/../model/Fornecedor.php');


class PostgresFornecedorDao {
    private $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    public function inserir(Fornecedor $fornecedor) {
        $sql = "INSERT INTO fornecedores (nome, cnpj) VALUES (?, ?)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            $fornecedor->getNome(),
            $fornecedor->getCnpj()
        ]);
    }

    public function atualizar(Fornecedor $fornecedor) {
        $sql = "UPDATE fornecedores SET nome = ?, cnpj = ? WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            $fornecedor->getNome(),
            $fornecedor->getCnpj(),
            $fornecedor->getId()
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM fornecedores WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$id]);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM fornecedores WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            return new Fornecedor($row['nome'], $row['cnpj'], $row['id']);
        }
        return null;
    }

    public function buscarPorNome($nome) {
        $sql = "SELECT * FROM fornecedores WHERE nome LIKE ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(["%$nome%"]);
        return $stmt->fetchAll();
    }

    public function listarTodos() {
        $sql = "SELECT * FROM fornecedores";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }
}
?>
