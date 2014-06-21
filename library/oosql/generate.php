<?php
include 'cogen.php';
try{
   $gen = new oosql\cogen('localhost', 'naftal', 'root', 'password');
   }catch(\PDOException $e){
     var_dump($e);
   }

$gen->generate();

?>