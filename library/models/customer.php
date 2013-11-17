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
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
    public function getRelations() 
    {
        return array('uid'=>'customer_profile.id',);
    }
}
