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
        return $this->id;
    }
}
?>