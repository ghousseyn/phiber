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
        return $this->id;
    }
}
?>