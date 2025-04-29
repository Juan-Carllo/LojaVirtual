<?php

error_reporting(E_ERROR | E_PARSE);

include_once('model/Usuario.php');
include_once('model/Produto.php');
include_once('model/Fornecedor.php');
include_once('dao/UsuarioDao.php');
include_once('dao/FornecedorDao.php');
include_once('dao/ProdutoDao.php');
/*include_once('dao/DaoFactory.php');*/
include_once('dao/postgres/PostgresDaoFactory.php');

/*$factory = new PostgresDaoFactory();*/


?>
