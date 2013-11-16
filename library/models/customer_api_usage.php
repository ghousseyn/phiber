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
        return $this->id;
    }
}
?>