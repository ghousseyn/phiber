<?php
include 'cogen.php';

$gen = new oosql\cogen('localhost', 'codup', 'root', 'hggiHmfv');


$gen->generate();
//var_dump($gen->getErrors());
?>