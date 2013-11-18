<?php
namespace models;
use Codup;
class customer_limit extends model  
{
    public $id;
    public $gid;
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
    public function getRelations() 
    {
        return array('gid'=>'customer_group.id',);
    }
}
