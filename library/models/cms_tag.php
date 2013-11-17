<?php
namespace models;
use Codup;
class cms_tag extends model  
{
    public $id;
    public $name;
    public $value;
    public function getPrimary() 
    {
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
}
