<?php
include 'cogen.php';
try{
   $gen = new oosql\cogen('localhost', 'codup', 'root', 'hggiHmfv');
   }catch(\PDOException $e){
     var_dump($e);
   }

$gen->generate();

?>