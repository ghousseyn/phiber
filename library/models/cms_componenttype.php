<?php
namespace models;
use Codup;
class cms_componenttype extends model  
{
    public $id;
    public $name;
    public $class;
    public function getPrimary() 
    {
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
}
