<?php
namespace models;
use Codup;
class customer_api_usage extends model  
{
    public $id;
    public $uid;
    public $count;
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
