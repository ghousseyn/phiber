<?php
namespace models;
use Codup;
class customer_transactions extends model  
{
    public $id;
    public $ruid;
    public $suid;
    public $date;
    public $value;
    public $currency;
    public $item;
    public $item_quantity;
    public $item_description;
    public $tid;
    public $type;
    public $user_3;
    public $status;
    public $user_1;
    public $user_2;
    public $user_4;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>