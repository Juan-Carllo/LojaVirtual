<?php

error_reporting(E_ERROR | E_PARSE);

include_once('model/Usuario.php');
include_once('model/Produto.php');
include_once('model/Fornecedor.php');
include_once('model/Endereco.php');
include_once('model/ItemPedido.php');
include_once('model/Pedido.php');
include_once('model/ItemPedido.php');
include_once('dao/UsuarioDao.php');
include_once('dao/ProdutoDao.php');
include_once('dao/PedidoFactory.php');
include_once('dao/FornecedorDao.php');
include_once('dao/EnderecoDao.php');
include_once('dao/DaoFactory.php');
include_once('dao/ItemPedidoDao.php');
include_once('dao/postgres/PostgresDaoFactory.php');
include_once('dao/postgres/PostgresEnderecoDao.php');
include_once('dao/postgres/PostgresProdutoDao.php');
include_once('dao/postgres/PostgresUsuarioDao');
include_once('dao/postgres/PostgresUsuarioDao');
include_once('dao/postgres/PostgresUsuarioDao');
include_once('dao/postgres/ItemPedidoDao');

$factory = new PostgresDaoFactory();


?>
