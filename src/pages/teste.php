<?php
require 'fachada.php';
$p = $factory->getProdutoDao()->buscaTodos();
var_dump($p);