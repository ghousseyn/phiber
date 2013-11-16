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
        return $this->id;
    }
}
?>