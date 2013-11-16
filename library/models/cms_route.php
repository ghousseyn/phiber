<?php
namespace models;
use Codup;
class cms_route extends model  
{
    public $id;
    public $rule;
    public $target;
    public $params;
    public $ord;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>