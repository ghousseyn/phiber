<?php
namespace models;
use Phiber;
use entity;

class departments extends Phiber\model
{
  function getDepartments($num){
    $deps = entity\departments::getInstance();
    return $deps->findAll()->fetch($num);
  }
}