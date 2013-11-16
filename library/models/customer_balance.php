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
        return $this->id;
    }
}
?>