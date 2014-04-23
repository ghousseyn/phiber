<?php
namespace model;
use Phiber;
use entity;

class departments extends Phiber\model
{
  function getDepartments($num){
    $deps = entity\departments::getInstance();
    $this->view->model = __class__;
    return $deps->findAll()->fetch($num);
  }
}