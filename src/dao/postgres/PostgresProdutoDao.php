<?php
require_once(__DIR__ . '/../model/Produto.php');


class PostgresProdutoDao {
    private $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    public function inserir(Produto $produto) {
        $sql = "INSERT INTO produtos (nome, preco, quantidade, fornecedor_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            $produto->getNome(),
            $produto->getPreco(),
            $produto->getQuantidade(),
            $produto->getFornecedorId()
        ]);
    }

    public function atualizar(Produto $produto) {
        $sql = "UPDATE produtos SET nome = ?, preco = ?, quantidade = ?, fornecedor_id = ? WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([
            $produto->getNome(),
            $produto->getPreco(),
            $produto->getQuantidade(),
            $produto->getFornecedorId(),
            $produto->getId()
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$id]);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM produtos WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            return new Produto($row['nome'], $row['preco'], $row['quantidade'], $row['fornecedor_id'], $row['id']);
        }
        return null;
    }

    public function buscarPorNome($nome) {
        $sql = "SELECT * FROM produtos WHERE nome LIKE ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute(["%$nome%"]);
        return $stmt->fetchAll();
    }

    public function listarTodos() {
        $sql = "SELECT * FROM produtos";
        $stmt = $this->conexao->query($sql);
        return $stmt->fetchAll();
    }

    public function atualizarQuantidade($id, $quantidade) {
        $sql = "UPDATE produtos SET quantidade = ? WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute([$quantidade, $id]);
    }
}
?>
