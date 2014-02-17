<?php
namespace models;
use Codup;
class salaries extends model  
{
    public $emp_no;
    public $salary;
    public $from_date;
    public $to_date;
    public function getPrimary() 
    {
        return "from_date";
    }
    public function getPrimaryValue() 
    {
        return $this->from_date;
    }
    public function getRelations() 
    {
        return array('emp_no'=>'employees.emp_no',);
    }
    public function save() 
    {
        parent::save($this);
    }
}
