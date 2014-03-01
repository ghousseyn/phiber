<?php
include 'cogen.php';

$gen = new oosql\cogen('localhost', 'codup', 'root', 'password');

$gen->generate();

?>