<?php
include 'cogen.php';

$gen = new oosql\cogen('localhost', 'edinar', 'root', 'hggiHmfv');

$gen->prefix = "model_";
$gen->suffix = "_mapper";
$gen->generate();
//var_dump($gen->getErrors());
?>