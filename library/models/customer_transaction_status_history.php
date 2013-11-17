<?php
namespace models;
use Codup;
class customer_transaction_status_history extends model  
{
    public $id;
    public $tid;
    public $old;
    public $new;
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
        return array('tid'=>'customer_transactions.id',);
    }
}
