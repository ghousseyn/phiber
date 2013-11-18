<?php
namespace models;
use Codup;
class customer_balance extends model  
{
    public $id;
    public $uid;
    public $balance;
    public $currency;
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
