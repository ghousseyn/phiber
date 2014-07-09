<?php
/**
 * Example entities generation script
 */
include 'oogen.php';
try{
   $gen = new Phiber\oosql\oogen('localhost', 'phiber', 'root', 'password');
   }catch(\PDOException $e){
     var_dump($e);
   }

$gen->generate();

?>