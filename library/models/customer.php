<?php
namespace models;
use Codup;
class customer extends model  
{
    public $id;
    public $uid;
    public $email;
    public $password;
    public $name;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>