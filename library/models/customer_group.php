<?php
namespace models;
use Codup;
class customer_group extends model  
{
    public $id;
    public $name;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>