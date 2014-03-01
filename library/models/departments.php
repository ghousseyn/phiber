<?php
namespace models;
use Codup;
use entity;

class departments extends Codup\model
{
  function getDepartments($num){
    $deps = entity\departments::getInstance();
    return $deps->findAll()->fetch($num);
  }
}
