<?php
namespace models;
use Codup;
class departments extends model  
{
    public $dept_no;
    public $dept_name;
    public function getPrimary() 
    {
        return "dept_no";
    }
    public function getPrimaryValue() 
    {
        return $this->dept_no;
    }
    public function save() 
    {
        parent::save($this);
    }
}
