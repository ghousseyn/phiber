<?php
namespace models;
use Codup;
class employees extends model  
{
    public $emp_no;
    public $birth_date;
    public $first_name;
    public $last_name;
    public $gender;
    public $hire_date;
    public function getPrimary() 
    {
        return "emp_no";
    }
    public function getPrimaryValue() 
    {
        return $this->emp_no;
    }
    public function save() 
    {
        parent::save($this);
    }
}
