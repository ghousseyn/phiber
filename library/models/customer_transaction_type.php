<?php
namespace models;
use Codup;
class customer_transaction_type extends model  
{
    public $id;
    public $type;
    public function getPrimary() 
    {
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
}
