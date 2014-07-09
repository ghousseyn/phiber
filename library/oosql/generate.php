<?php
/**
 * Example entities generation script
 */
include 'oogen.php';
try{
   $gen = new Phiber\oosql\oogen('mysql:host=127.0.0.1;dbname=phiber', 'root', 'password');
   }catch(\PDOException $e){
     var_dump($e);
   }

$gen->generate();

?>