<?php
/**
 * Example entities generation script
 * Run this in CLI
 */

//include 'pgsql.php';
include 'mysql.php';

try{
   //$gen = new Phiber\oosql\pgsql('pgsql:host=127.0.0.1;dbname=phiber', 'user', 'password');
   $gen = new Phiber\oosql\mysql('mysql:host=127.0.0.1;dbname=phiber', 'user', 'password');
   }catch(\PDOException $e){
     print $e->getMessage();
   }

$gen->generate();

?>