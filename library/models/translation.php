<?php
namespace models;
use Codup;
class translation extends model  
{
    public $id;
    public $tr_en;
    public $tr_fr;
    public $tr_ar;
    public $key;
    public function getPrimary() 
    {
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
}
