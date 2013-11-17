<?php
namespace models;
use Codup;
class customer_api extends model  
{
    public $id;
    public $uid;
    public $api_key;
    public $locked;
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
